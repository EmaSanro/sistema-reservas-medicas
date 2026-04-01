<?php

namespace App\Profesionales\Repository;

use App\Auth\Exceptions\UserAlreadyInactiveException;
use App\Auth\Model\Roles;
use App\Profesionales\Exceptions\ProfesionalNotFoundException;
use App\Profesionales\Model\Profesional;
use App\Shared\Repository;
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

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE rol = :rol";
        $data = $this->findByQuery($sql, ["rol" => Roles::PROFESIONAL]);
        return $data;
    }

    public function obtenerPorId(int $id): Profesional|null
    {
        $sql = "SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE id = :id";
        $data = $this->findOneByQuery($sql, ["id" => $id]);
        return $data;
    }

    public function buscarPor(string $filtro, string $valor): array
    {
        $sql = "SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE $filtro LIKE :valor AND u.rol = :rol";
        $data = $this->findByQuery($sql, ["valor" => "%$valor%", "rol" => Roles::PROFESIONAL]);
        return $data;
    }

    public function obtenerPorProfesion(string $profesion): array
    {
        $sql = "SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE p.profesion LIKE :profesion";
        $data = $this->findByQuery($sql, ["profesion" => ucwords("%$profesion%")]);
        return $data;
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

    public function obtenerProfesionalPorUbicacion(string $valor): array
    {
        $sql = "SELECT u.*, p.profesion FROM usuario u 
                JOIN profesional p ON u.id = p.idprofesional 
                JOIN consultorio c ON p.idprofesional = c.idprofesional 
                WHERE c.direccion LIKE :direccion OR c.ciudad LIKE :ciudad";
        $data = $this->findByQuery($sql, ["direccion" => "%$valor%", "ciudad" => "%$valor%"]);
        return $data;
    }

    public function buscarCoincidencia(Profesional $prof): Profesional|null //REFACTOR
    {
        $sql = "SELECT * FROM usuario WHERE telefono = ? OR email = ?";
        $data = $this->findOneByQuery($sql, [$prof->getTelefono(), $prof->getEmail()]);
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

            $id = $this->db->lastInsertId();
            $this->db->commit();

            $profesional->setId((int) $id);

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
