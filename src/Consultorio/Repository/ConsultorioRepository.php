<?php
namespace App\Consultorio\Repository;

use App\Consultorio\Model\Consultorio;
use App\Shared\Repository;
use PDO;

class ConsultorioRepository extends Repository {

    protected function getTableName(): string {
        return "consultorio";
    }

    protected function getEntityClass(): string {
        return Consultorio::class;
    }
    
    public function crearConsultorio(Consultorio $consultorio): Consultorio {
        try {
            $this->db->beginTransaction();
            $query = $this->db->prepare("
                INSERT INTO consultorio(direccion, ciudad, horario_apertura, horario_cierre, idprofesional) 
                VALUES(:direccion, :ciudad, :horario_apertura, :horario_cierre, :idprofesional)
            ");
            $query->execute([
                "direccion" => $consultorio->getDireccion(),
                "ciudad" => $consultorio->getCiudad(),
                "horario_apertura" => $consultorio->getHorarioApertura(),
                "horario_cierre" => $consultorio->getHorarioCierre(),
                "idprofesional" => $consultorio->getIdProfesional()
            ]);

            $id = $this->db->lastInsertId();
            
            $this->db->commit();

            $consultorio->setId((int) $id);
            return $consultorio;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizarConsultorio(Consultorio $consultorio, int $id): Consultorio {
        try {
            $this->db->beginTransaction();
            $updateQuery = $this->db->prepare("
                UPDATE consultorio set ciudad = :ciudad, direccion = :direccion, horario_apertura = :horario_apertura, horario_cierre = :horario_cierre
                WHERE id = :id
            ");
            $updateQuery->execute([
                "ciudad" => $consultorio->getCiudad(),
                "direccion" => $consultorio->getDireccion(),
                "horario_apertura" => $consultorio->getHorarioApertura(),
                "horario_cierre" => $consultorio->getHorarioCierre(),
                "id" => $id
            ]);

            $this->db->commit();

            return $this->findById($id);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function borrarConsultorio(int $id): void {
        $deleteQuery = $this->db->prepare("
            DELETE FROM consultorio WHERE id = :id
        ");
        $deleteQuery->execute(["id" => $id]);
        if ($deleteQuery->rowCount() === 0) {
            throw new \Exception("Error en la base de datos");
        } 
    }

    public function buscarPorCiudadDireccion(string $ciudad, string $direccion): array {
        $sql = sprintf("SELECT * FROM %s WHERE ciudad = :ciudad AND direccion = :direccion", $this->getTableName());
        $consultorios = $this->findOneByQuery($sql, ["ciudad" => $ciudad, "direccion" => $direccion]);
        return $consultorios;
    }

    public function esAtendidoPor(int $idConsultorio): mixed {
        $query = $this->db->prepare("
            SELECT idprofesional FROM consultorio WHERE id = :id
        ");
        $query->execute(["id" => $idConsultorio]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}