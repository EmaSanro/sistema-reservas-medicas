<?php
namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Model\DTOs\ProfesionalDTO;
use App\Model\Profesional;
use App\Model\Roles;
use AppConfig\Database;
use PDO;

class ProfesionalesRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function obtenerTodos() {
        $profs = $this->db->prepare("SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE rol = ?");
        $profs->execute([Roles::PROFESIONAL]);
        $data = $profs->fetchAll(PDO::FETCH_ASSOC);
        $profesionales = [];
        foreach($data as $profesional) {
            $profesionales[] = new Profesional(
                $profesional["id"],
                $profesional["nombre"],
                $profesional["apellido"],
                $profesional["profesion"],
                $profesional["email"],
                $profesional["telefono"],
                $profesional["password"],
            );
        }
        if(!$profesionales) return null;
        return $profesionales;
    }

    public function obtenerPorId(int $id) {
        $prof = $this->db->prepare("SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE id = ?");
        $prof->execute([$id]);
        $data = $prof->fetch();
        if($data) {
            return new Profesional(
                $data["id"],
                $data["nombre"],
                $data["apellido"],
                $data["profesion"],
                $data["email"],
                $data["telefono"],
                $data["password"]
            );
        }
        return null;
    }

    public function buscarPor(string $filtro, string $valor): array|null {
        $profs = $this->db->prepare("SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE $filtro LIKE ? AND u.rol = ?");
        $profs->execute(["%$valor%", Roles::PROFESIONAL]);
        $data = $profs->fetchAll(PDO::FETCH_ASSOC);
        $profesionales = [];
        foreach($data as $profesional) {
            $profesionales[] = new Profesional(
                $profesional["id"],
                $profesional["nombre"],
                $profesional["apellido"],
                $profesional["profesion"],
                $profesional["email"],
                $profesional["telefono"],
                $profesional["password"],
            );
        }
        return $profesionales;
    }

    public function obtenerPorProfesion($profesion): array|null {
        $profs = $this->db->prepare("SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE p.profesion LIKE ?");
        $profs->execute([ucwords("%$profesion%")]);
        $data = $profs->fetchAll(PDO::FETCH_ASSOC);
        $profesionales = [];
        foreach($data as $profesional) {
            $profesionales[] = new Profesional(
                $profesional["id"],
                $profesional["nombre"],
                $profesional["apellido"],
                $profesional["profesion"],
                $profesional["email"],
                $profesional["telefono"],
                $profesional["password"],
            );
        }
        if(!$profesionales) return null;
        return $profesionales;
    }

    public function obtenerPorTelefono(string $telefono): Profesional|null {
        $prof = $this->db->prepare("SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE telefono = ? AND rol = ?");
        $prof->execute([$telefono, Roles::PROFESIONAL]);
        $data = $prof->fetch(PDO::FETCH_ASSOC);
        if($data) {
            return new Profesional(
                $data["id"],
                $data["nombre"],
                $data["apellido"],
                $data["profesion"],
                $data["email"],
                $data["telefono"],
                $data["password"]
            );
        }
        return null;
    }

    public function obtenerPorEmail(string $email): Profesional|null {
        $prof = $this->db->prepare("SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE email = ? AND rol = ?");
        $prof->execute([$email, Roles::PROFESIONAL]);
        $data = $prof->fetch();
        if($data) {
            return new Profesional(
                $data["id"],
                $data["nombre"],
                $data["apellido"],
                $data["profesion"],
                $data["email"],
                $data["telefono"],
                $data["password"]
            );
        }
        return null;
    }
    
    public function obtenerProfesionalPorUbicacion($valor): array|null { 
        $query = $this->db->prepare("
            SELECT u.*, p.profesion FROM usuario u 
            JOIN profesional p ON u.id = p.idprofesional 
            JOIN consultorio c ON p.idprofesional = c.idprofesional 
            WHERE c.direccion LIKE ? OR c.ciudad LIKE ?
        ");
        $query->execute(["%$valor%", "%$valor%"]);
        $profs = $query->fetchAll(PDO::FETCH_ASSOC);
        $profesionales = [];
        foreach($profs as $profesional) {
            $profesionales[] = new Profesional(
                $profesional["id"],
                $profesional["nombre"],
                $profesional["apellido"],
                $profesional["profesion"],
                $profesional["email"],
                $profesional["telefono"],
                $profesional["password"],
            );
        }
        if(!$profesionales) return null;
        return $profesionales;
    }

    public function buscarCoincidencia(ProfesionalDTO $dto): mixed {
        $prof = $this->db->prepare("SELECT id FROM usuario WHERE telefono = ? OR email = ?");
        $prof->execute([$dto->getTelefono(), $dto->getEmail()]);
        $data = $prof->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    public function registrarProfesional(ProfesionalDTO $profesional, string $passwordHash): Profesional {
        try {
            $this->db->beginTransaction();
            $stmtUsuario = $this->db->prepare("INSERT INTO usuario(nombre, apellido, rol, email, telefono, password) VALUES(?,?,?,?,?,?)");
            $created = $stmtUsuario->execute([
                $profesional->getNombre(), 
                $profesional->getApellido(), 
                Roles::PROFESIONAL, 
                $profesional->getEmail(), 
                $profesional->getTelefono(),
                $passwordHash
            ]);
            
            if(!$created) {
                throw new DatabaseException("Error al crear el profesional");
            }
            $id = $this->db->lastInsertId();

            $stmtProfesional = $this->db->prepare("INSERT INTO profesional(idprofesional, profesion) VALUES(?,?)");
            $profesionalCreated = $stmtProfesional->execute([$id, $profesional->getProfesion()]);
            if(!$profesionalCreated) {
                throw new DatabaseException("Error al crear el profesional");
            }

            $this->db->commit();

            return new Profesional(
                $id,
                $profesional->getNombre(),
                $profesional->getApellido(),
                $profesional->getProfesion(),
                $profesional->getEmail(),
                $profesional->getTelefono(),
                $passwordHash
            );
        } catch (\Throwable $th) {
            $this->db->rollBack();
            throw new DatabaseException("Error en la base de datos");
        }
    }

    public function actualizarProfesional(int $id, ProfesionalDTO $dto, ?string $passwordHash = null): Profesional {
        try {
            $this->db->beginTransaction();
            $query = "UPDATE usuario SET nombre = ?, apellido = ?, email = ?, telefono = ?";
            $params = [$dto->getNombre(), $dto->getApellido(), $dto->getEmail(), $dto->getTelefono()];
            if($passwordHash != null) {
                $query .= ", password = ?";
                $params[] = $passwordHash;
            }
            $query .= " WHERE id = ? AND rol = ?";
            $params[] = $id;
            $params[] = Roles::PROFESIONAL;
            $stmtUsuario = $this->db->prepare($query);
            $stmtUsuario->execute($params);

            $stmtProfesional = $this->db->prepare("UPDATE profesional SET profesion = ? WHERE idprofesional = ?");
            $stmtProfesional->execute([$dto->getProfesion(), $id]);

            $this->db->commit();

            return new Profesional(
                $id,
                $dto->getNombre(), 
                $dto->getApellido(), 
                $dto->getProfesion(),
                $dto->getEmail(),
                $dto->getTelefono(),
                $passwordHash
                );
        } catch (\Throwable $th) {
            $this->db->rollBack();
            throw new DatabaseException("Error en la base de datos");
        }
    }

    public function eliminarProfesional($id): bool {
        $stmtUsuario = $this->db->prepare("DELETE FROM usuario WHERE id = ? and rol = ?");
        $stmtUsuario->execute([$id, Roles::PROFESIONAL]);
        if($stmtUsuario->rowCount() === 0) {
            throw new DatabaseException("No se pudo eliminar el profesional");
        }
        return $stmtUsuario->rowCount() > 0;
    }
}