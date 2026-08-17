<?php
namespace App\Nota\Repository;

use App\Nota\Model\Nota;
use App\Shared\Repository;

class NotaRepository extends Repository {

    protected function getTableName(): string
    {
        return "nota";
    }

    protected function getEntityClass(): string
    {
        return Nota::class;
    }

    public function guardarNota(Nota $nota) {
        try {
            $this->db->beginTransaction();
            $stmtNota = $this->db->prepare("
                INSERT INTO nota(motivo_visita, texto_nota, reserva_id) 
                VALUES(:motivo_visita, :texto_nota, :reserva_id)
            ");
            $stmtNota->execute([
                "motivo_visita" => $nota->getMotivoVisita(), 
                "texto_nota" => $nota->getTextoNota(), 
                "reserva_id" => $nota->getReservaId()
            ]);
            
            $id = $this->db->lastInsertId();
            
            $this->db->commit();

            $nota->setId((int)$id);
            
            return $nota;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $this->translateException($e);
        }
    }

    public function actualizarNota(int $id, Nota $nota) {
        try {
            $this->db->beginTransaction();
            $stmtNota = $this->db->prepare("
                UPDATE nota SET motivo_visita = :motivo_visita, 
                                texto_nota = :texto_nota
                WHERE id = :id
            ");
            $stmtNota->execute([
                "motivo_visita" => $nota->getMotivoVisita(),
                "texto_nota" => $nota->getTextoNota(),
                "id" => $id
            ]);

            $nota->setId($id);

            $this->db->commit();

            return $nota;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $this->translateException($e);
        }
    }
}