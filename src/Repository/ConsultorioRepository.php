<?php
namespace App\Repository;

use AppConfig\Database;
use PDO;

class ConsultorioRepository {
    private $db;

    public function __construct() { 
        $this->db = Database::getConnection();
    }

    public function obtenerConsultorios() {
        $consultorio = $this->db->prepare("
            SELECT * FROM consultorio 
        ");
        $consultorio->execute();
        $consultorios = [];
        foreach($consultorios as $consultorio) {
            $consultorios[] = [
                "id" => $consultorio["id"],
                "ciudad" => $consultorio["ciudad"],
                "direccion" => $consultorio["direccion"],
                "horario_apertura" => $consultorio["horario_apertura"],
                "horario_cierre" => $consultorio["horario_cierre"],
            ];
        }
        if(!$consultorios) return null;
        return $consultorios;
    }

    public function obtenerConsultorio($id) {
        $consultorio = $this->db->prepare("
            SELECT * FROM consultorio WHERE id = ?
        ");
        $consultorio->execute([$id]);
        return $consultorio->fetch(PDO::FETCH_ASSOC);
    }
    
    public function crearConsultorio($data, $idprofesional) {
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
        if($query->rowCount() > 0) {
            return [
                "direccion" => $data["direccion"],
                "ciudad" => $data["ciudad"],
                "horario_apertura" => $data["horario_apertura"],
                "horario_cierre" => $data["horario_cierre"]
            ];
        }
        return null;
    }

    public function actualizarConsultorio($data, $id) {
        $updateQuery = $this->db->prepare("
            UPDATE consultorio set ciudad = ?, direccion = ?, horario_apertura = ?, horario_cierre = ?
            WHERE id = ?
        ");
        $updateQuery->execute([
            $data["ciudad"],
            $data["direccion"],
            $data["horario_apertura"],
            $data["horario_cierre"]
        ]);
        if($updateQuery->rowCount() > 0) {
            return [
                "id" => $id,
                "direccion" => $data["direccion"],
                "ciudad" => $data["ciudad"],
                "horario_apertura" => $data["horario_apertura"],
                "horario_cierre" => $data["horario_cierre"]
            ];
        }
        return null;
    }

    public function borrarConsultorio($id) {
        $deleteQuery = $this->db->prepare("
            DELETE FROM consultorio WHERE id = ?
        ");
        $deleteQuery->execute([$id]);
        return $deleteQuery->rowCount() > 0;
    }

    public function buscarPorCiudadDireccion(string $ciudad, string $direccion) {
        $consultorio = $this->db->prepare("
            SELECT id FROM consultorio WHERE ciudad = ? AND direccion = ?
        ");
        $consultorio->execute([$ciudad, $direccion]);
        return $consultorio->fetch(PDO::FETCH_ASSOC);
    }

    public function esAtendidoPor($idConsultorio) {
        $query = $this->db->prepare("
            SELECT idprofesional FROM consultorio WHERE id = ?
        ");
        $query->execute([$idConsultorio]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}