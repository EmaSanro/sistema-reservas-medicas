<?php
namespace App\Repository;

use App\Model\DTOs\PacienteDTO;
use App\Model\Roles;
use App\Model\Usuario;
use AppConfig\Database;
use PDO;

class PacientesRepository {

    private $db;
    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function obtenerTodos() {
        $pacs = $this->db->prepare("SELECT * FROM usuario WHERE rol = ?");
        $pacs->execute([Roles::PACIENTE]);
        $data = $pacs->fetchAll(PDO::FETCH_ASSOC);

        $pacientes = [];
        foreach($data as $pac) {
            $pacientes[] = new Usuario(
                $pac["id"],
                $pac["nombre"],
                $pac["apellido"],
                $pac["rol"],
                $pac["email"],
                $pac["telefono"],
                $pac["password"]
            );
        }
        return $pacientes;
    }

    public function obtenerPorId($id) {
        $pac = $this->db->prepare("SELECT * FROM usuario WHERE id = ? AND rol = ?");
        $pac->execute([$id, Roles::PACIENTE]);
        $data = $pac->fetch(PDO::FETCH_ASSOC);

        if(!$data) {
            return null;
        } else {
            return new Usuario(
                $data["id"],
                $data["nombre"],
                $data["apellido"],
                $data["rol"],
                $data["email"],
                $data["telefono"],
                $data["password"]
            );
        }
    }

    public function buscarPor($filtro, $valor) {
        $pac = $this->db->prepare("SELECT * FROM usuario WHERE $filtro LIKE ? AND rol = ?");
        $pac->execute(["%$valor%", Roles::PACIENTE]);
        $data = $pac->fetchAll();
        $pacientes = [];
        foreach($data as $pac) {
            $pacientes[] = new Usuario(
                $pac["id"],
                $pac["nombre"],
                $pac["apellido"],
                $pac["rol"],
                $pac["email"],
                $pac["telefono"],
                $pac["password"]
            );
        }
        return $pacientes;
    }

    public function buscarCoincidencia(PacienteDTO $dto) {
        $pac = $this->db->prepare("SELECT * FROM usuario WHERE telefono = ? OR email = ?");
        $pac->execute([$dto->getTelefono(), $dto->getEmail()]);
        $data = $pac->fetch();
        if($data) {
            return new Usuario(
                $data["id"],
                $data["nombre"],
                $data["apellido"],
                $data["rol"],
                $data["email"],
                $data["telefono"],
                $data["password"]
            );
        }
        return null;
    }

    public function registrarPaciente(PacienteDTO $dto, string $passwordHash) {
        $pac = $this->db->prepare("INSERT INTO usuario(nombre, apellido, rol, email, telefono, password) VALUES(?,?,?,?,?,?)");
        $created = $pac->execute([
            $dto->getNombre(),
            $dto->getApellido(),
            Roles::PACIENTE,
            $dto->getEmail(),
            $dto->getTelefono(),
            $passwordHash
        ]);
        if($created) {
            $id = $this->db->lastInsertId();

            return new Usuario(
                $id,
                $dto->getNombre(),
                $dto->getApellido(),
                Roles::PACIENTE,
                $dto->getEmail(),
                $dto->getTelefono(),
                $passwordHash
            );
        }
    }

    public function actualizarPaciente(int $id, PacienteDTO $dto, $password) {
        $act = $this->db->prepare("UPDATE usuario SET nombre = ?, apellido = ?, email = ?, telefono = ?, password = ? WHERE id = ? AND rol = ?");
        $act->execute([
            $dto->getNombre(),
            $dto->getApellido(),
            $dto->getEmail(),
            $dto->getTelefono(),
            $password,
            $id,
            Roles::PACIENTE
        ]);
        if($act->rowCount() > 0) {
            return new Usuario(
                $id,
                $dto->getNombre(),
                $dto->getApellido(),
                Roles::PACIENTE,
                $dto->getEmail(),
                $dto->getTelefono(),
                $dto->getPassword()
            );
        }
        return null;
    }

    public function eliminarPaciente(int $id) {
        $pac = $this->db->prepare("DELETE FROM usuario WHERE id = ? AND rol = ?");
        $pac->execute([$id, Roles::PACIENTE]);
        return $pac->rowCount() > 0;
    }
}