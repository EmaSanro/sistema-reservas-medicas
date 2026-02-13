<?php
namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Model\ArchivoNota;
use App\Model\DTOs\CrearArchivoNotaDTO;
use AppConfig\Database;
use PDO;

class ArchivoNotaRepository {
    private $db;
    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function guardarArchivo(CrearArchivoNotaDTO $archivo): ArchivoNota {
        try {
            $this->db->beginTransaction();
            $stmtGuardar = $this->db->prepare("
                INSERT INTO archivo_nota(nombre_original, nombre_sistema, ruta, tipo_archivo, peso, fecha_subida, nota_id) VALUES(?,?,?,?,?,?,?)   
            ");
            $stmtGuardar->execute([
                $archivo->getNombreOriginal(),
                $archivo->getNombreSistema(),
                $archivo->getRuta(),
                $archivo->getTipoArchivo(),
                $archivo->getPeso(),
                $archivo->getFechaSubida(),
                $archivo->getNotaId()
            ]);

            $id = $this->db->lastInsertId();
            
            $this->db->commit();

            return new ArchivoNota(
                $id,
                $archivo->getNombreOriginal(),
                $archivo->getNombreSistema(),
                $archivo->getRuta(),
                $archivo->getTipoArchivo(),
                $archivo->getPeso(),
                $archivo->getFechaSubida(),
                $archivo->getNotaId()
            );
        } catch (\Throwable $th) {
            $this->db->rollBack();
            throw new DatabaseException("Error en la base de datos" . $th->getMessage());
        }
    }

    public function obtenerPorId(int $id) {
        $stmtArchivo = $this->db->prepare("
            SELECT * FROM archivo_nota WHERE id = ?
        ");
        $stmtArchivo->execute([$id]);
        $data = $stmtArchivo->fetch(PDO::FETCH_ASSOC);

        if(!$data) return null;
        
        return new ArchivoNota(
            $data["id"],
            $data["nombre_original"],
            $data["nombre_sistema"],
            $data["ruta"],
            $data["tipo_archivo"],
            $data["peso"],
            $data["fecha_subida"],
            $data["nota_id"]
        );
    }

    public function obtenerPorNotaId($idNota) {
        $stmtArchivo = $this->db->prepare("
            SELECT * FROM archivo_nota WHERE nota_id = ?
        ");
        $stmtArchivo->execute([$idNota]);
        $obtenidos = $stmtArchivo->fetchAll(PDO::FETCH_ASSOC);
        $archivos = [];
        foreach($obtenidos as $archivo) {
            $archivos[] = new ArchivoNota(
                $archivo["id"],
                $archivo["nombre_original"],
                $archivo["nombre_sistema"],
                $archivo["ruta"],
                $archivo["tipo_archivo"],
                $archivo["peso"],
                $archivo["fecha_subida"],
                $idNota
                );
        }

        return $archivos;
    }

    public function eliminarArchivo(int $id) {
        $stmtBorrar = $this->db->prepare("
            DELETE FROM archivo_nota WHERE id = ? 
        ");
        $stmtBorrar->execute([$id]);

        return $stmtBorrar->rowCount() > 0;
    }
}