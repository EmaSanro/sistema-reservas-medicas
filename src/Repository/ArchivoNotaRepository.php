<?php
namespace App\Repository;

use App\Model\ArchivoNota;
use App\Shared\Repository;

class ArchivoNotaRepository extends Repository {

    protected function getTableName(): string {
        return "archivo_nota";
    }

    protected function getEntityClass(): string {
        return ArchivoNota::class;
    }

    public function guardarArchivo(ArchivoNota $archivo): ArchivoNota {
        try {
            $this->db->beginTransaction();
            $stmtGuardar = $this->db->prepare("
                INSERT INTO archivo_nota(nombre_original, nombre_sistema, ruta, tipo_archivo, peso, fecha_subida, nota_id) 
                VALUES(:nombre_original,:nombre_sistema,:ruta,:tipo_archivo,:peso,:fecha_subida,:nota_id)   
            ");
            $stmtGuardar->execute([
                "nombre_original" => $archivo->getNombreOriginal(),
                "nombre_sistema" => $archivo->getNombreSistema(),
                "ruta" => $archivo->getRuta(),
                "tipo_archivo" => $archivo->getTipoArchivo(),
                "peso" => $archivo->getPeso(),
                "fecha_subida" => $archivo->getFechaSubida(),
                "nota_id" => $archivo->getNotaId()
            ]);

            $id = $this->db->lastInsertId();
            
            $this->db->commit();

            $archivo->setId((int)$id);

            return $archivo;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function obtenerPorNotaId(int $idNota): array {
        $sql = "SELECT * FROM archivo_nota WHERE nota_id = :idNota";
        $archivosNota = $this->findByQuery($sql, ["idNota" => $idNota]);
        return $archivosNota;
    }

    public function eliminarArchivo(int $id): void {
        $stmtBorrar = $this->db->prepare("
            DELETE FROM archivo_nota WHERE id = :id
        ");
        $stmtBorrar->execute(["id" => $id]);
    }
}