<?php
namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Model\DTOs\ActualizarNotaDTO;
use App\Model\DTOs\CrearNotaDTO;
use App\Model\Nota;
use AppConfig\Database;
use PDO;

class NotaRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();    
    }

    public function guardarNota(CrearNotaDTO $nota) {
        try {
            $this->db->beginTransaction();
            $stmtNota = $this->db->prepare("
                INSERT INTO nota(motivo_visita, texto_nota, reserva_id) VALUES(?,?,?)
            ");
            $stmtNota->execute([$nota->getMotivoVisita(), $nota->getTextoNota(), $nota->getReservaId()]);
            
            $id = $this->db->lastInsertId();
            
            $this->db->commit();

            
            return new Nota(
                $id,
                $nota->getMotivoVisita(),
                $nota->getTextoNota(),
                $nota->getReservaId()
            );
        } catch (\Throwable $th) {
            $this->db->rollBack();
            throw new DatabaseException("Error en la base de datos");
        }
    }

    public function obtenerNotaPorId(int $id) {
        $stmtNota = $this->db->prepare("
            SELECT * FROM nota WHERE id = ?
        ");
        $stmtNota->execute([$id]);
        $data = $stmtNota->fetch(PDO::FETCH_ASSOC);

        if(!$data) return null;

        return new Nota(
            $data["id"],
            $data["motivo_visita"],
            $data["texto_nota"],
            $data["reserva_id"]
        );
    }

    public function actualizarNota(int $id, ActualizarNotaDTO $nota) {
        try {
            $this->db->beginTransaction();
            $stmtNota = $this->db->prepare("
                UPDATE nota SET motivo_visita = ?, 
                                texto_nota = ?
                WHERE id = ?
            ");
            $stmtNota->execute([
                $nota->getMotivoVisita(),
                $nota->getTextoNota(),
                $id
            ]);
            $this->db->commit();

            return $this->obtenerNotaPorId($id);
        } catch (\Throwable $th) {
            $this->db->rollBack();
            throw new DatabaseException("Hubo un error en la base de datos");
        }
    }
}