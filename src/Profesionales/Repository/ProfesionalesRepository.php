<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Exceptions\Profesionales\ProfesionalNotFoundException;
use App\Exceptions\UserAlreadyInactiveException;
use App\Model\Profesional;
use App\Model\Roles;
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

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE rol = :rol";
        $data = $this->findByQuery($sql, ["rol" => Roles::PROFESIONAL]);
        return $data;
    }

    public function obtenerPorId(int $id)
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

    public function buscarCoincidencia(Profesional $prof): int|null //REFACTOR
    {
        $sql = "SELECT id FROM usuario WHERE telefono = ? OR email = ?";
        $data = $this->findOneByQuery($sql, [$prof->getTelefono(), $prof->getEmail()]);
        return $data;
    }

    public function registrarProfesional(Profesional $profesional, string $passwordHash): Profesional
    {
        try {
            $this->db->beginTransaction();
            $stmtUsuario = $this->db->prepare("INSERT INTO usuario(nombre, apellido, rol, email, telefono, activo, password) VALUES(?,?,?,?,?,?,?)");
            $stmtUsuario->execute([
                $profesional->getNombre(),
                $profesional->getApellido(),
                Roles::PROFESIONAL,
                $profesional->getEmail(),
                $profesional->getTelefono(),
                true,
                $passwordHash
            ]);

            $id = $this->db->lastInsertId();

            $stmtProfesional = $this->db->prepare("INSERT INTO profesional(idprofesional, profesion) VALUES(?,?)");
            $stmtProfesional->execute([$id, $profesional->getProfesion()]);

            $this->db->commit();

            $profesional->setId((int)$id);

            return $profesional;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizarProfesional(int $id, Profesional $profesional, ?string $passwordHash = null): Profesional
    {
        try {
            $this->db->beginTransaction();
            $query = "UPDATE usuario SET nombre = ?, apellido = ?, email = ?, telefono = ?";
            $params = [$profesional->getNombre(), $profesional->getApellido(), $profesional->getEmail(), $profesional->getTelefono()];
            if ($passwordHash != null) {
                $query .= ", password = ?";
                $params[] = $passwordHash;
            }
            $query .= " WHERE id = ? AND rol = ?";
            $params[] = $id;
            $params[] = Roles::PROFESIONAL;
            $stmtUsuario = $this->db->prepare($query);
            $stmtUsuario->execute($params);

            $stmtProfesional = $this->db->prepare("UPDATE profesional SET profesion = ? WHERE idprofesional = ?");
            $stmtProfesional->execute([$profesional->getProfesion(), $id]);

            $id = $this->db->lastInsertId();
            $this->db->commit();

            $profesional->setId((int) $id);

            return $profesional;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function darDeBajaProfesional($id, $motivo): bool
    {
        $stmtUsuario = $this->db->prepare("
            UPDATE usuario SET activo = false, fecha_baja = NOW(), motivo_baja = ? 
            WHERE id = ? AND rol = ? AND activo = true
        ");
        $stmtUsuario->execute([$motivo, $id, Roles::PROFESIONAL]);
        if ($stmtUsuario->rowCount() === 0) {
            $stmtCheck = $this->db->prepare("
                SELECT activo FROM usuario
                WHERE id = ? AND rol = ?
            ");
            $stmtCheck->execute([$id, Roles::PROFESIONAL]);
            $usuario = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                throw new ProfesionalNotFoundException("Profesional no encontrado");
            }
            if (!$usuario["activo"]) {
                throw new UserAlreadyInactiveException("El profesional ya se encuentra inactivo");
            }

            throw new DatabaseException("No se pudo dar de baja el profesional");
        }
        return true;
    }
}
