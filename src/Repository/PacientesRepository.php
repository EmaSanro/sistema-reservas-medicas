<?php
namespace App\Repository;

use App\Exceptions\DatabaseException;
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

    public function obtenerTodos(): array {
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

    public function obtenerPorId($id): Usuario|null {
        $pac = $this->db->prepare("SELECT * FROM usuario WHERE id = ? AND rol = ?");
        $pac->execute([$id, Roles::PACIENTE]);
        $data = $pac->fetch(PDO::FETCH_ASSOC);

        if(!$data) {
            return null;
        }
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

    public function buscarPor($filtro, $valor): array {
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

    public function buscarCoincidencia(PacienteDTO $dto): Usuario|null {
        $pac = $this->db->prepare("SELECT * FROM usuario WHERE telefono = ? OR email = ?");
        $pac->execute([$dto->getTelefono(), $dto->getEmail()]);
        $data = $pac->fetch();
        if(!$data) {
            return null;
        }
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

    public function registrarPaciente(PacienteDTO $dto, string $passwordHash): Usuario {
        try {
            $this->db->beginTransaction();
            $stmtUsuario = $this->db->prepare("INSERT INTO usuario(nombre, apellido, rol, email, telefono, password) VALUES(?,?,?,?,?,?)");
            $stmtUsuario->execute([
                $dto->getNombre(),
                $dto->getApellido(),
                Roles::PACIENTE,
                $dto->getEmail(),
                $dto->getTelefono(),
                $passwordHash
            ]);
            $this->db->commit();
            
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
        } catch (\Throwable $th) {
            $this->db->rollBack();
            throw new DatabaseException("Error en la base de datos");
        }
        
    }

    public function actualizarPaciente(int $id, PacienteDTO $dto, ?string $passwordHash = null): Usuario {
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
            $params[] = Roles::PACIENTE;
            $stmtUsuario = $this->db->prepare($query);
            $stmtUsuario->execute($params);

            $this->db->commit();

            return new Usuario(
                $id,
                $dto->getNombre(), 
                $dto->getApellido(), 
                Roles::PACIENTE,
                $dto->getEmail(),
                $dto->getTelefono(),
                $passwordHash
                );
        } catch (\Throwable $th) {
            $this->db->rollBack();
            throw new DatabaseException("Error en la base de datos");
        }
    }

    public function eliminarPaciente(int $id): bool {
        $pac = $this->db->prepare("DELETE FROM usuario WHERE id = ? AND rol = ?");
        $pac->execute([$id, Roles::PACIENTE]);
        if($pac->rowCount() === 0) {
            throw new DatabaseException("No se pudo eliminar el paciente");
        }
        return $pac->rowCount() > 0;
    }
}