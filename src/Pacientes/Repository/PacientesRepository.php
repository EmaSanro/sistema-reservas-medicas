<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Exceptions\Pacientes\PacienteNotFoundException;
use App\Exceptions\UserAlreadyInactiveException;
use App\Model\Roles;
use App\Shared\Repository;
use App\Model\Usuario;
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

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM usuario WHERE rol = :rol";
        $pacientes = $this->findByQuery($sql, ["rol" => Roles::PACIENTE]);
        return $pacientes;
    }

    public function obtenerPorId(int $id): Usuario|null
    {
        $sql = "SELECT * FROM usuario WHERE id = :id AND rol = :rol";
        $paciente = $this->findOneByQuery($sql, ["id" => $id, "rol" => Roles::PACIENTE]);
        return $paciente;
    }

    public function buscarPor(string $filtro, string $valor): array
    {
        $sql = "SELECT * FROM usuario WHERE $filtro LIKE :valor AND rol = :rol";
        $pacientes = $this->findByQuery($sql, ["valor" => "%$valor%", "rol" => Roles::PACIENTE]);
        return $pacientes;
    }

    public function buscarCoincidencia(Usuario $paciente): array
    {
        $sql = "SELECT * FROM usuario WHERE (telefono = :telefono OR email = :email) AND rol = :rol";
        $pacientes = $this->findByQuery($sql, [
            "telefono" => $paciente->getTelefono(),
            "email" => $paciente->getEmail(),
            "rol" => Roles::PACIENTE
        ]);
        return $pacientes;
    }

    public function registrarPaciente(Usuario $usuario, string $passwordHash): Usuario
    {
        try {
            $this->db->beginTransaction();
            $stmtUsuario = $this->db->prepare("INSERT INTO usuario(nombre, apellido, rol, email, telefono, activo, password) VALUES(:nombre,:apellido,:rol,:email,:telefono,:activo,:password)");
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
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizarPaciente(int $id, Usuario $usuario, ?string $passwordHash = null): Usuario
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

            if ($passwordHash != null) {
                $query .= ", password = :password";
                $params["password"] = $passwordHash;
            }

            $query .= " WHERE id = :id AND rol = :rol";
            $params["id"] = $id;
            $params["rol"] = Roles::PACIENTE;
            $stmtUsuario = $this->db->prepare($query);
            $stmtUsuario->execute($params);

            $this->db->commit();
            $usuario->setId($id);

            return $usuario;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function darDeBajaPaciente(int $id, string $motivo): bool
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
                throw new PacienteNotFoundException("Paciente no encontrado");
            }
            if (!$usuario["activo"]) {
                throw new UserAlreadyInactiveException("El paciente ya se encuentra inactivo!");
            }

            throw new DatabaseException("No se pudo dar de baja el paciente");
        }
        return true;
    }
}
