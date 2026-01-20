<?php
namespace App\Repository;

use App\Model\DTOs\PacienteDTO;
use App\Model\Roles;
use AppConfig\Database;

class PacientesRepository {

    private $db;
    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function obtenerTodos() {
        $pacs = $this->db->prepare("SELECT * FROM usuario WHERE rol = ?");
        $pacs->execute([Roles::PACIENTE]);
        $data = $pacs->fetchAll();

        $pacientes = [];
        foreach($data as $pac) {
            $pacientes[] = [
                "id" => $pac["id"],
                "nombre" => $pac["nombre"],
                "apellido" => $pac["apellido"],
                "email" => $pac["email"],
                "telefono" => $pac["telefono"]
            ];
        }
        return $pacientes;
    }

    public function obtenerPorId($id) {
        $pac = $this->db->prepare("SELECT * FROM usuario WHERE id = ? AND rol = ?");
        $pac->execute([$id, Roles::PACIENTE]);
        $data = $pac->fetch();

        if(!$data) {
            return null;
        } else {
            return [
                "id" => $data["id"],
                "nombre" => $data["nombre"],
                "apellido" => $data["apellido"],
                "email" => $data["email"],
                "telefono" => $data["telefono"]
            ];
        }
    }

    public function buscarPor($filtro, $valor) {
        $pac = $this->db->prepare("SELECT * FROM usuario WHERE $filtro LIKE ? AND rol = ?");
        $pac->execute(["%$valor%", Roles::PACIENTE]);
        return $pac->fetchAll();
    }

    public function buscarCoincidencia(PacienteDTO $dto) {
        $pac = $this->db->prepare("SELECT * FROM usuario WHERE telefono = ? OR email = ?");
        $pac->execute([$dto->getTelefono(), $dto->getEmail()]);
        $data = $pac->fetch();
        if($data) {
            return [
                "id" => $data["id"],
                "nombre" => $data["nombre"],
                "apellido" => $data["apellido"],
                "email" => $data["email"],
                "telefono" => $data["telefono"]
            ];
        }
        return null;
    }

    public function crearPaciente(PacienteDTO $dto) {
        $pac = $this->db->prepare("INSERT INTO usuario(nombre, apellido, rol, email, telefono) VALUES(?,?,?,?,?)");
        $created = $pac->execute([
            $dto->getNombre(),
            $dto->getApellido(),
            Roles::PACIENTE,
            $dto->getEmail(),
            $dto->getTelefono()
        ]);
        if($created) {
            $id = $this->db->lastInsertId();

            return [
                "id" => $id,
                "nombre" => $dto->getNombre(),
                "apellido" => $dto->getApellido(),
                "email" => $dto->getEmail(),
                "telefono" => $dto->getTelefono()
            ];
        }
    }

    public function actualizarPaciente(int $id, PacienteDTO $dto) {
        $act = $this->db->prepare("UPDATE usuario SET nombre = ?, apellido = ?, email = ?, telefono = ? WHERE id = ? AND rol = ?");
        $act->execute([
            $dto->getNombre(),
            $dto->getApellido(),
            $dto->getEmail(),
            $dto->getTelefono(),
            $id,
            Roles::PACIENTE
        ]);
        if($act->rowCount() > 0) {
            return [
                "id" => $id,
                "nombre" => $dto->getNombre(),
                "apellido" => $dto->getApellido(),
                "email" => $dto->getEmail(),
                "telefono" => $dto->getTelefono()
            ];
        }
        return null;
    }

    public function eliminarPaciente(int $id) {
        $pac = $this->db->prepare("DELETE FROM usuario WHERE id = ? AND rol = ?");
        $pac->execute([$id, Roles::PACIENTE]);
        return $pac->rowCount() > 0;
    }
}