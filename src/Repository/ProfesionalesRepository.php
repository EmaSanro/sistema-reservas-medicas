<?php
namespace App\Repository;

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

    public function buscarPor(string $filtro, string $valor) {
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
        if(!$profesionales) return null;
        return $profesionales;
    }

    public function obtenerPorProfesion($profesion) {
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

    public function obtenerPorTelefono(string $telefono) {
        $prof = $this->db->prepare("SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE telefono = ? AND rol = ?");
        $prof->execute([$telefono, Roles::PROFESIONAL]);
        $data = $prof->fetch(PDO::FETCH_ASSOC);
        if($data) {
            return [
                "id" => $data["id"],
                "nombre" => $data["nombre"],
                "apellido" => $data["apellido"],
                "profesion" => $data["profesion"],
                "email" => $data["email"],
                "telefono" => $data["telefono"]
            ];
        }
    }

    public function obtenerPorEmail(string $email) {
        $prof = $this->db->prepare("SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE email = ? AND rol = ?");
        $prof->execute([$email, Roles::PROFESIONAL]);
        $data = $prof->fetch();
        if($data) {
            return [
                "id" => $data["id"],
                "nombre" => $data["nombre"],
                "apellido" => $data["apellido"],
                "profesion" => $data["profesion"],
                "email" => $data["email"],
                "telefono" => $data["telefono"]
            ];
        }
    }
    
    public function obtenerProfesionalPorUbicacion($valor) { 
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

    public function buscarCoincidencia(ProfesionalDTO $dto) {
        $prof = $this->db->prepare("SELECT * FROM usuario WHERE telefono = ? OR email = ?");
        $prof->execute([$dto->getTelefono(), $dto->getEmail()]);
        $data = $prof->fetch();
        return $data;
    }

    public function registrarProfesional(ProfesionalDTO $profesional, string $passwordHash) {
        $this->db->beginTransaction();
        $prof = $this->db->prepare("INSERT INTO usuario(nombre, apellido, rol, email, telefono, password) VALUES(?,?,?,?,?,?)");
        $created = $prof->execute([
            $profesional->getNombre(), 
            $profesional->getApellido(), 
            Roles::PROFESIONAL, 
            $profesional->getEmail(), 
            $profesional->getTelefono(),
            $passwordHash
        ]);
        
        if(!$created) {
            throw new \Exception("Error al crear el profesional");
        }
        $id = $this->db->lastInsertId();

        $prof = $this->db->prepare("INSERT INTO profesional(idprofesional, profesion) VALUES(?,?)");
        $prof->execute([$id, $profesional->getProfesion()]);

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
    }

    public function actualizarProfesional(int $id, ProfesionalDTO $dto, $passwordHash) {
        $profActualizado = $this->db->prepare("UPDATE usuario SET nombre = ?, apellido = ?, email = ?, telefono = ?, password = ? WHERE id = ? AND rol = ?");
        $profActualizado->execute([
            $dto->getNombre(), 
            $dto->getApellido(), 
            $dto->getEmail(),
            $dto->getTelefono(),
            $passwordHash,
            $id,
            Roles::PROFESIONAL
        ]);

        $profActualizado2 = $this->db->prepare("UPDATE profesional SET profesion = ? WHERE idprofesional = ?");
        $profActualizado2->execute([$dto->getProfesion(), $id]);
        if($profActualizado->rowCount() > 0 && $profActualizado2->rowCount() > 0) {
            return new Profesional(
                $id,
                $dto->getNombre(), 
                $dto->getApellido(), 
                $dto->getProfesion(),
                $dto->getEmail(),
                $dto->getTelefono(),
                $passwordHash
            );
        }
        return null;
    }

    public function eliminarProfesional($id) {
        $prof = $this->db->prepare("DELETE FROM usuario WHERE id = ? and rol = ?");
        $prof->execute([$id, Roles::PROFESIONAL]);

        return $prof->rowCount() > 0;
    }
}