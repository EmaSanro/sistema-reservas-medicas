<?php

namespace App\Profesionales\Repository;

use App\Auth\Exceptions\UserAlreadyInactiveException;
use App\Auth\Model\Roles;
use App\Auth\Model\Usuario;
use App\Profesionales\Exceptions\ProfesionalNotFoundException;
use App\Profesionales\Model\Profesional;
use App\Profesionales\Validators\ProfesionalSearchValidator;
use App\Shared\Repository;
use App\Shared\Search\SearchQueryBuilder;
use PDO;

class ProfesionalesRepository extends Repository
{

    protected function getTableName(): string
    {
        return "profesional";
    }

    protected function getEntityClass(): string
    {
        return Profesional::class;
    }

    public function listar(array $filtros = [], int $page = 1, int $limit = 10): array
    {
        $built = SearchQueryBuilder::build(ProfesionalSearchValidator::definitions(), $filtros);

        $joins = array_merge(["JOIN profesional p ON u.id = p.idprofesional"], $built['joins']);
        $where = array_merge(["u.rol = :rol"], $built['where']);
        $params = array_merge(["rol" => Roles::PROFESIONAL], $built['params']);

        $sql = "SELECT u.*, p.profesion FROM usuario u "
             . implode(" ", $joins)
             . " WHERE " . implode(" AND ", $where);

        return $this->findPaginatedByQuery($sql, $params, $page, $limit);
    }

    public function obtenerPorId(int $id): Profesional|null
    {
        $sql = "SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE id = :id";
        $data = $this->findOneByQuery($sql, ["id" => $id]);
        return $data;
    }

    /**
     * Verifica si existe un profesional activo con el id dado.
     * Sin hidratar la entidad — pensado para chequeos previos a una operacion
     * (ej. antes de crear una reserva).
     */
    public function existePorId(int $id): bool
    {
        $sql = "SELECT 1 FROM profesional p
                INNER JOIN usuario u ON u.id = p.idprofesional
                WHERE p.idprofesional = :id AND u.activo = 1
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["id" => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function obtenerPorTelefono(string $telefono): Profesional|null
    {
        $sql = "SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE telefono = :telefono AND rol = :rol";
        $data = $this->findOneByQuery($sql, ["telefono" => $telefono, "rol" => Roles::PROFESIONAL]);
        return $data;
    }

    public function obtenerPorEmail(string $email): Profesional|null
    {
        $sql = "SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE email = :email AND rol = :rol";
        $data = $this->findOneByQuery($sql, ["email" => $email, "rol" => Roles::PROFESIONAL]);
        return $data;
    }

    public function registrarProfesional(Profesional $profesional, string $passwordHash): Profesional
    {
        try {
            $this->db->beginTransaction();
            $stmtUsuario = $this->db->prepare(
                "INSERT INTO usuario(nombre, apellido, rol, email, telefono, activo, password) 
                VALUES(:nombre, :apellido, :rol, :email, :telefono, :activo, :password)
            ");
            $stmtUsuario->execute([
                "nombre" => $profesional->getNombre(),
                "apellido" =>$profesional->getApellido(),
                "rol" => Roles::PROFESIONAL,
                "email" => $profesional->getEmail(),
                "telefono" => $profesional->getTelefono(),
                "activo" => true,
                "password" => $passwordHash
            ]);

            $id = $this->db->lastInsertId();

            $stmtProfesional = $this->db->prepare(
                "INSERT INTO profesional(idprofesional, profesion) 
                VALUES(:idprofesional, :profesion)
            ");
            $stmtProfesional->execute([
                "idprofesional" => $id, 
                "profesion" => $profesional->getProfesion()
            ]);

            $this->db->commit();

            $profesional->setId((int)$id);

            return $profesional;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizarProfesional(int $id, Profesional $profesional): Profesional
    {
        try {
            $this->db->beginTransaction();
            $query = "UPDATE usuario SET nombre = :nombre, apellido = :apellido, email = :email, telefono = :telefono";
            $params = [
                "nombre" => $profesional->getNombre(), 
                "apellido" => $profesional->getApellido(), 
                "email" => $profesional->getEmail(), 
                "telefono" => $profesional->getTelefono()
            ];

            $query .= " WHERE id = :id AND rol = :rol";
            $params["id"] = $id;
            $params["rol"] = Roles::PROFESIONAL;
            $stmtUsuario = $this->db->prepare($query);
            $stmtUsuario->execute($params);

            $stmtProfesional = $this->db->prepare("UPDATE profesional SET profesion = :profesion WHERE idprofesional = :idprofesional");
            $stmtProfesional->execute([
                "profesion" => $profesional->getProfesion(), 
                "idprofesional" => $id
            ]);

            if($stmtProfesional->rowCount() === 0) {
                $profesional->setId((int) $id);
            } else {
                $id = $this->db->lastInsertId();
                $profesional->setId((int) $id);
            }

            $this->db->commit();


            return $profesional;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function darDeBajaProfesional(int $id, string $motivo): void
    {
        $stmtUsuario = $this->db->prepare("
            UPDATE usuario SET activo = false, fecha_baja = NOW(), motivo_baja = :motivo_baja
            WHERE id = :id AND rol = :rol AND activo = true
        ");
        $stmtUsuario->execute([
            "motivo_baja" => $motivo, 
            "id" => $id, 
            "rol" => Roles::PROFESIONAL
        ]);
        if ($stmtUsuario->rowCount() === 0) {
            $stmtCheck = $this->db->prepare("
                SELECT activo FROM usuario
                WHERE id = :id AND rol = :rol
            ");
            $stmtCheck->execute([
                "id" => $id, 
                "rol" => Roles::PROFESIONAL
            ]);
            $usuario = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                throw new ProfesionalNotFoundException($id);
            }
            if (!$usuario["activo"]) {
                throw new UserAlreadyInactiveException("El profesional ya se encuentra inactivo");
            }

            throw new \Exception("No se pudo dar de baja el profesional");
        }
    }
}
