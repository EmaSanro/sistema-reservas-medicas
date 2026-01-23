<?php
namespace App\Repository;

use App\Model\DTOs\ProfesionalDTO;
use App\Model\Roles;
use AppConfig\Database;

class ProfesionalesRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
        // $this->crearAdmin();
    }

    public function obtenerTodos() {
        $profs = $this->db->prepare("SELECT * FROM usuario JOIN profesional ON usuario.id = profesional.idprofesional WHERE rol = ?");
        $profs->execute([Roles::PROFESIONAL]);
        $data = $profs->fetchAll();
        return $data;
    }

    public function obtenerPorId(int $id) {
        $prof = $this->db->prepare("SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE id = ?");
        $prof->execute([$id]);
        $data = $prof->fetch();
        if($data) {
            return [
                "id" => $data["id"],
                "nombre" => $data["nombre"],
                "apellido" => $data["apellido"],
                "profesion" => $data["profesion"],
                "email" => $data["email"],
                "telefono" =>$data["telefono"]
            ];
        }
    }

    public function buscarPor(string $filtro, string $valor) {
        $profs = $this->db->prepare("SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE $filtro LIKE ? AND u.rol = ?");
        $profs->execute(["%$valor%", Roles::PROFESIONAL]);
        $data = $profs->fetchAll();
        return $data;
    }

    public function obtenerPorProfesion($profesion) {
        $profs = $this->db->prepare("SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE p.profesion LIKE ?");
        $profs->execute([ucwords("%$profesion%")]);
        $data = $profs->fetchAll();
        return $data;
    }

    public function obtenerPorTelefono(string $telefono) {
        $prof = $this->db->prepare("SELECT * FROM usuario u JOIN profesional p ON u.id = p.idprofesional WHERE telefono = ? AND rol = ?");
        $prof->execute([$telefono, Roles::PROFESIONAL]);
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
    // TODO Modificar la siguiente funcion para evitar cualquier error de traer un usuario con email o telefono null;
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
        if($created) {
            $id = $this->db->lastInsertId();

            $prof = $this->db->prepare("INSERT INTO profesional(idprofesional, profesion) VALUES(?,?)");
            $prof->execute([$id, $profesional->getProfesion()]);

            $this->db->commit();

            return [
                "id" => $id,
                "nombre" => $profesional->getNombre(),
                "apellido" => $profesional->getApellido(),
                "profesion" => $profesional->getProfesion(),
                "email" => $profesional->getEmail(),
                "telefono" => $profesional->getTelefono()
            ];
        }
        return null;
    }

    public function actualizarProfesional(int $id, ProfesionalDTO $dto) {
        $profActualizado = $this->db->prepare("UPDATE usuario SET nombre = ?, apellido = ?, email = ?, telefono = ? WHERE id = ? AND rol = ?");
        $profActualizado->execute([
            $dto->getNombre(), 
            $dto->getApellido(), 
            $dto->getEmail(),
            $dto->getTelefono(),
            $id,
            Roles::PROFESIONAL
        ]);

        $profActualizado2 = $this->db->prepare("UPDATE profesional SET profesion = ? WHERE idprofesional = ?");
        $profActualizado2->execute([$dto->getProfesion(), $id]);
        // TODO Corregir metodo para retornar el profesional actualizado y no un boolean
        return $profActualizado->rowCount() > 0 || $profActualizado2->rowCount() > 0;
    }

    public function eliminarProfesional($id) {
        $prof = $this->db->prepare("DELETE FROM usuario WHERE id = ? and rol = ?");
        $prof->execute([$id, Roles::PROFESIONAL]);

        return $prof->rowCount() > 0;
    }

    // private function crearAdmin() {
    //     $query = $this->db->prepare("
    //         INSERT INTO usuario(nombre, apellido, rol, email, telefono, password) VALUES(?,?,?,?,?,?)
    //     ");
    //     $passwordHash = password_hash("emasanro", PASSWORD_BCRYPT);
    //     $query->execute(["Emanuel", "San Roman", Roles::ADMIN, "esanroman@gmail.com", "", $passwordHash]);
    // }
}