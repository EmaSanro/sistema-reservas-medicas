<?php

namespace App\Pacientes\Repository;

use App\Auth\Exceptions\UserAlreadyInactiveException;
use App\Auth\Model\Roles;
use App\Auth\Model\Usuario;
use App\Pacientes\Exceptions\PacienteNotFoundException;
use App\Pacientes\Validators\PacienteSearchValidator;
use App\Shared\Repository;
use App\Shared\Search\SearchQueryBuilder;
use PDO;

class PacientesRepository extends Repository
{

    protected function getTableName(): string
    {
        return "usuario";
    }

    protected function getEntityClass(): string
    {
        return Usuario::class;
    }

    public function listar(array $filtros = [], int $page = 1, int $limit = 10): array
    {
        $built = SearchQueryBuilder::build(PacienteSearchValidator::definitions(), $filtros);

        $where = array_merge(["rol = :rol"], $built['where']);
        $params = array_merge(["rol" => Roles::PACIENTE], $built['params']);

        $sql = "SELECT " . Usuario::columnasSelect() . " FROM usuario WHERE " . implode(" AND ", $where);
        return $this->findPaginatedByQuery($sql, $params, $page, $limit);
    }

    public function obtenerPorId(int $id): Usuario|null
    {
        $sql = "SELECT " . Usuario::columnasSelect() . " FROM usuario WHERE id = :id AND rol = :rol";
        $paciente = $this->findOneByQuery($sql, ["id" => $id, "rol" => Roles::PACIENTE]);
        return $paciente;
    }
    
    public function registrarPaciente(Usuario $usuario, string $passwordHash): Usuario
    {
        try {
            $this->db->beginTransaction();
            $stmtUsuario = $this->db->prepare(
                        "INSERT INTO usuario(nombre, apellido, rol, email, telefono, activo, password) 
                        VALUES(:nombre,:apellido,:rol,:email,:telefono,:activo,:password)");
            $stmtUsuario->execute([
                "nombre" => $usuario->getNombre(),
                "apellido" => $usuario->getApellido(),
                "rol" => Roles::PACIENTE,
                "email" => $usuario->getEmail(),
                "telefono" => $usuario->getTelefono(),
                "activo" => $usuario->isActivo(),
                "password" => $passwordHash
            ]);

            $id = $this->db->lastInsertId();

            $this->db->commit();

            $usuario->setId((int) $id);

            return $usuario;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $this->translateException($e);
        }
    }

    public function actualizarPaciente(int $id, Usuario $usuario): Usuario
    {
        try {
            $this->db->beginTransaction();

            $query = "UPDATE usuario SET nombre = :nombre, apellido = :apellido, email = :email, telefono = :telefono";
            $params = [
                "nombre" => $usuario->getNombre(), 
                "apellido" => $usuario->getApellido(), 
                "email" => $usuario->getEmail(), 
                "telefono" => $usuario->getTelefono()
            ];

            $query .= " WHERE id = :id AND rol = :rol";
            $params["id"] = $id;
            $params["rol"] = Roles::PACIENTE;
            $stmtUsuario = $this->db->prepare($query);
            $stmtUsuario->execute($params);

            $this->db->commit();
            $usuario->setId($id);

            return $usuario;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $this->translateException($e);
        }
    }

    public function darDeBajaPaciente(int $id, string $motivo): void
    {
        $pac = $this->db->prepare("
            UPDATE usuario SET activo = false, motivo_baja = :motivo, fecha_baja = NOW()
            WHERE id = :id AND rol = :rol AND activo = true
        ");
        $pac->execute(["motivo" => $motivo, "id" => $id, "rol" => Roles::PACIENTE]);
        if ($pac->rowCount() === 0) {
            $stmtCheck = $this->db->prepare("
                SELECT activo FROM usuario 
                WHERE id = :id AND rol = :rol
            ");
            $stmtCheck->execute(["id" => $id, "rol" => Roles::PACIENTE]);
            $usuario = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                throw new PacienteNotFoundException($id);
            }
            if (!$usuario["activo"]) {
                throw new UserAlreadyInactiveException("El paciente ya se encuentra inactivo!");
            }

            throw new \Exception("No se pudo dar de baja el paciente");
        }
    }
}
