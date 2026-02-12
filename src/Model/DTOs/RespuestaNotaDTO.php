<?php
namespace App\Model\DTOs;
class RespuestaNotaDTO {
    public readonly int $id;
    public readonly string $motivo_visita;
    public readonly string $texto_nota;
    public readonly int $reserva_id;

    public function __construct(int $id, string $motivo_visita, string $texto_nota, int $reserva_id) {
        $this->id = $id;
        $this->motivo_visita = $motivo_visita;
        $this->texto_nota = $texto_nota;
        $this->reserva_id = $reserva_id;
    }
}