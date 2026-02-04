<?php
namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Model\Consultorio;
use App\Model\DTOs\ConsultorioDTO;
use AppConfig\Database;
use PDO;

class ConsultorioRepository {
    private $db;

    public function __construct() { 
        $this->db = Database::getConnection();
    }

    public function obtenerConsultorios(): array {
        $consultorio = $this->db->prepare("
            SELECT * FROM consultorio 
        ");
        $consultorio->execute();
        $consultorios = [];
        foreach($consultorios as $consultorio) {
            $consultorios[] = new Consultorio(
                $consultorio["id"],
                $consultorio["ciudad"],
                $consultorio["direccion"],
                $consultorio["horario_apertura"],
                $consultorio["horario_cierre"],
                $consultorio["idprofesional"]
            );
        }
        return $consultorios;
    }

    public function obtenerConsultorio($id): Consultorio|null {
        $consultorio = $this->db->prepare("
            SELECT * FROM consultorio WHERE id = ?
        ");
        $consultorio->execute([$id]);
        $data = $consultorio->fetch(PDO::FETCH_ASSOC);

        if(!$data) return null;
        
        return new Consultorio(
            $data["id"],
            $data["ciudad"],
            $data["direccion"],
            $data["horario_apertura"],
            $data["horario_cierre"],
            $data["idprofesional"]
        );
    }
    
    public function crearConsultorio($data, $idprofesional): Consultorio {
        try {
            $this->db->beginTransaction();
            $query = $this->db->prepare("
                INSERT INTO consultorio(direccion, ciudad, horario_apertura, horario_cierre, idprofesional) VALUES(?,?,?,?,?)
            ");
            $query->execute([
                $data["direccion"],
                $data["ciudad"],
                $data["horario_apertura"],
                $data["horario_cierre"],
                $idprofesional
            ]);
            $this->db->commit();
            return new Consultorio(
                $data["id"],
                $data["direccion"],
                $data["ciudad"],
                $data["horario_apertura"],
                $data["horario_cierre"],
                $idprofesional
            );
        } catch (\Throwable $th) {
            $this->db->rollBack();
            throw new DatabaseException("Error en la base de datos");
        }
    }

    public function actualizarConsultorio(ConsultorioDTO $data, int $id, int $idProfesional): Consultorio|null {
        try {
            $this->db->beginTransaction();
            $updateQuery = $this->db->prepare("
                UPDATE consultorio set ciudad = ?, direccion = ?, horario_apertura = ?, horario_cierre = ?
                WHERE id = ?
            ");
            $updateQuery->execute([
                $data->getCiudad(),
                $data->getDireccion(),
                $data->getHorarioApertura(),
                $data->getHorarioCierre()
            ]);
            $this->db->commit();
            if($updateQuery->rowCount() > 0) {
                return new Consultorio(
                    $id,
                    $data->getCiudad(),
                    $data->getDireccion(),
                    $data->getHorarioApertura(),
                    $data->getHorarioCierre(),
                    $idProfesional
                );
            }
            return null;
        } catch (\Throwable $th) {
            $this->db->rollBack();
            throw new DatabaseException("Error en la base de datos");
        }
    }

    public function borrarConsultorio($id): bool {
        $deleteQuery = $this->db->prepare("
            DELETE FROM consultorio WHERE id = ?
        ");
        $deleteQuery->execute([$id]);
        if($deleteQuery->rowCount() === 0) {
            throw new DatabaseException("No se pudo eliminar el consultorio");
        }
        return $deleteQuery->rowCount() > 0;
    }

    public function buscarPorCiudadDireccion(string $ciudad, string $direccion): mixed {
        $consultorio = $this->db->prepare("
            SELECT id FROM consultorio WHERE ciudad = ? AND direccion = ?
        ");
        $consultorio->execute([$ciudad, $direccion]);
        return $consultorio->fetch(PDO::FETCH_ASSOC);
    }

    public function esAtendidoPor($idConsultorio): mixed {
        $query = $this->db->prepare("
            SELECT idprofesional FROM consultorio WHERE id = ?
        ");
        $query->execute([$idConsultorio]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}