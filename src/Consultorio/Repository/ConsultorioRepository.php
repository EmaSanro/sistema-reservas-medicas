<?php
namespace App\Repository;

use App\Model\Consultorio;
use App\Shared\Repository;
use PDO;

class ConsultorioRepository extends Repository {

    protected function getTableName(): string {
        return "consultorio";
    }

    protected function getEntityClass(): string {
        return Consultorio::class;
    }
    
    public function crearConsultorio(Consultorio $consultorio, int $idprofesional): Consultorio {
        try {
            $this->db->beginTransaction();
            $query = $this->db->prepare("
                INSERT INTO consultorio(direccion, ciudad, horario_apertura, horario_cierre, idprofesional) 
                VALUES(:direccion,:ciudad,:horario_apertura,:horario_cierre,:idprofesional)
            ");
            $query->execute([
                "direccion" => $consultorio["direccion"],
                "ciudad" => $consultorio["ciudad"],
                "horario_apertura" => $consultorio["horario_apertura"],
                "horario_cierre" => $consultorio["horario_cierre"],
                "idprofesional" => $idprofesional
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

    public function actualizarConsultorio(Consultorio $consultorio, int $id): Consultorio|null {
        try {
            $this->db->beginTransaction();
            $updateQuery = $this->db->prepare("
                UPDATE consultorio set ciudad = :ciudad, direccion = :ciudad, horario_apertura = :ciudad, horario_cierre = :horario_cierre
                WHERE id = :id
            ");
            $updateQuery->execute([
                "ciudad" => $consultorio->getCiudad(),
                "direccion" => $consultorio->getDireccion(),
                "horario_apertura" => $consultorio->getHorarioApertura(),
                "horario_cierre" => $consultorio->getHorarioCierre()
            ]);

            $this->db->commit();

            $consultorio->setId($id);
            
            return $consultorio;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function borrarConsultorio(int $id): void {
        $deleteQuery = $this->db->prepare("
            DELETE FROM consultorio WHERE id = ?
        ");
        $deleteQuery->execute(["id" => $id]);
    }

    public function buscarPorCiudadDireccion(string $ciudad, string $direccion): array {
        $sql = sprintf("SELECT * FROM %s WHERE ciudad = :ciudad AND direccion = :direccion", $this->getTableName());
        $consultorios = $this->findByQuery($sql, ["ciudad" => $ciudad, "direccion" => $direccion]);
        return $consultorios;
    }

    public function esAtendidoPor($idConsultorio): mixed {
        $query = $this->db->prepare("
            SELECT idprofesional FROM consultorio WHERE id = :id
        ");
        $query->execute(["id" => $idConsultorio]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}