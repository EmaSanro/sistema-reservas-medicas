<?php
namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Exceptions\Pacientes\PacienteNotFoundException;
use App\Exceptions\UserAlreadyInactiveException;
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
                $pac["activo"],
                $pac["motivo_baja"],
                $pac["fecha_baja"],
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
            $data["activo"],
            $data["motivo_baja"],
            $data["fecha_baja"],
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
                $pac["activo"],
                $pac["motivo_baja"],
                $pac["fecha_baja"],
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
            $data["activo"],
            $data["motivo_baja"],
            $data["fecha_baja"],
            $data["password"]
        );
    }

    public function registrarPaciente(PacienteDTO $dto, string $passwordHash): Usuario {
        try {
            $this->db->beginTransaction();
            $stmtUsuario = $this->db->prepare("INSERT INTO usuario(nombre, apellido, rol, email, telefono, activo, password) VALUES(?,?,?,?,?,?,?)");
            $stmtUsuario->execute([
                $dto->getNombre(),
                $dto->getApellido(),
                Roles::PACIENTE,
                $dto->getEmail(),
                $dto->getTelefono(),
                true,
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
                true,
                null,
                null,
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
                true,
                null,
                null,
                $passwordHash
                );
        } catch (\Throwable $th) {
            $this->db->rollBack();
            throw new DatabaseException("Error en la base de datos");
        }
    }

    public function darDeBajaPaciente(int $id, $motivo): bool {
        $pac = $this->db->prepare("
            UPDATE usuario SET activo = false, motivo_baja = ?, fecha_baja = NOW()
            WHERE id = ? AND rol = ? AND activo = true
        ");
        $pac->execute([$motivo, $id, Roles::PACIENTE]);
        if($pac->rowCount() === 0) {
            $stmtCheck = $this->db->prepare("
                SELECT activo FROM usuario 
                WHERE id = ? AND rol = ?
            ");
            $stmtCheck->execute([$id, Roles::PACIENTE]);
            $usuario = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if(!$usuario) {
                throw new PacienteNotFoundException("Paciente no encontrado");
            }
            if(!$usuario["activo"]) {
                throw new UserAlreadyInactiveException("El paciente ya se encuentra inactivo!");
            }

            throw new DatabaseException("No se pudo dar de baja el paciente");
        }
        return true;
    }
}