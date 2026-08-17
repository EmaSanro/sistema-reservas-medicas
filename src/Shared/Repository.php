<?php

namespace App\Shared;

use AppConfig\Database;
use PDO;
use PDOStatement;
use Throwable;

abstract class Repository {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    abstract protected function getTableName(): string;

    abstract protected function getEntityClass(): string;

    public function findAll(): array {
        $sql = sprintf("SELECT * FROM %s", $this->getTableName());
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $class = $this->getEntityClass();
        $entities = [];
        foreach($data as $row) {
            $entities[] = $class::fromDatabase($row);
        }

        return $entities;
    }

    public function findPaginated(int $page = 1, int $limit = 10): array {
        $sql = sprintf("SELECT * FROM %s", $this->getTableName());
        return $this->findPaginatedByQuery($sql, [], $page, $limit);
    }

    public function findById(int $id): ?Entity {
        $sql = sprintf("SELECT * FROM %s WHERE id = ?", $this->getTableName());
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$data) return null;

        $class = $this->getEntityClass();
        return $class::fromDatabase($data);
    }

    protected function findByQuery(string $sql, array $params = []): array {
        $stmt = $this->prepareAndExecute($sql, $params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $entities = [];
        foreach($data as &$row) {
            $entities[] = $this->getEntityClass()::fromDatabase($row);
        }

        return $entities;
    }

    protected function findPaginatedByQuery(string $sql, array $params = [], int $page = 1, int $limit = 10): array {
        return [
            'data' => $this->findByQuery($this->withLimit($sql, $page, $limit), $params),
            'total' => $this->countByQuery($sql, $params),
        ];
    }

    /**
     * Paginado sobre una proyeccion que no corresponde a getEntityClass()
     * (tipicamente un JOIN). $mapper recibe cada fila cruda y devuelve el
     * read model correspondiente.
     *
     * @template T
     * @param callable(array<string, mixed>): T $mapper
     * @return array{data: list<T>, total: int}
     */
    protected function findPaginatedByQueryAs(string $sql, callable $mapper, array $params = [], int $page = 1, int $limit = 10): array {
        return [
            'data' => array_map($mapper, $this->fetchRows($this->withLimit($sql, $page, $limit), $params)),
            'total' => $this->countByQuery($sql, $params),
        ];
    }

    /**
     * El COUNT corre sobre el SQL sin LIMIT: es el total de la busqueda, no
     * el de la pagina.
     */
    private function countByQuery(string $sql, array $params = []): int {
        return (int) $this->prepareAndExecute("SELECT COUNT(*) FROM ($sql) as sub", $params)->fetchColumn();
    }

    private function withLimit(string $sql, int $page, int $limit): string {
        $offset = ($page - 1) * $limit;
        return $sql . " LIMIT $limit OFFSET $offset";
    }

    protected function findOneByQuery(string $sql, array $params = []): ?Entity {
        $stmt = $this->prepareAndExecute($sql, $params);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if($data === false) return null;

        $entityClass = $this->getEntityClass();
        return $entityClass::fromDatabase($data);
    }

    protected function executeQuery(string $sql, array $params = []): bool {
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->rowCount() > 0;
    }

    protected function rowCountByQuery(string $sql, array $params = []): int {
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->rowCount();
    }

    protected function insertAndGetId(string $sql, array $params = []): int {
        $this->prepareAndExecute($sql, $params);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Para chequeos de existencia. La query debe proyectar una sola columna
     * (tipicamente "SELECT 1 ... LIMIT 1"): no hidrata entidades.
     */
    protected function existsByQuery(string $sql, array $params = []): bool {
        return (bool) $this->prepareAndExecute($sql, $params)->fetchColumn();
    }

    /**
     * Devuelve la fila cruda sin pasar por getEntityClass(). Pensado para
     * proyecciones que no corresponden a una entidad completa.
     */
    protected function fetchOneRow(string $sql, array $params = []): ?array {
        $row = $this->prepareAndExecute($sql, $params)->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /**
     * Contraparte plural de fetchOneRow(): filas crudas, sin hidratar entidades.
     *
     * @return list<array<string, mixed>>
     */
    protected function fetchRows(string $sql, array $params = []): array {
        return $this->prepareAndExecute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function prepareAndExecute(string $sql, array $params = []): PDOStatement {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected function runInTransaction(callable $callback): mixed {
        try {
            $this->db->beginTransaction();
            $result = $callback($this->db);
            $this->db->commit();
            return $result;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new \Exception("Error en la base de datos");
        }
    }
}