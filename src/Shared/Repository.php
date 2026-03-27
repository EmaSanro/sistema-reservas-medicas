<?php

namespace App\Shared;

use App\Exceptions\DatabaseException;
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

    protected function findAll(): array {
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

    protected function findById(int $id): ?object {
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

    protected function findOneByQuery(string $sql, array $params = []): ?array {
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
        } catch (Throwable $th) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new DatabaseException("Error en la base de datos", 0, $th);
        }
    }
}