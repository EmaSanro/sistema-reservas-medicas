<?php
namespace App\Model;

use App\Model\DTOs\RespuestaNotaDTO;

class Nota {
    private int $id;
    private string $motivo_visita;
    private string $texto_nota;
    private int $reserva_id;
    private array $adjuntos = [];

    public function __construct(int $id, string $motivo_visita, string $texto_nota, int $reserva_id) {
        $this->id = $id;
        $this->motivo_visita = $motivo_visita;
        $this->texto_nota = $texto_nota;
        $this->reserva_id = $reserva_id;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getMotivoVisita(): string {
        return $this->motivo_visita;
    }

    public function getTextoNota(): string {
        return $this->texto_nota;
    }

    public function getReservaId(): int {
        return $this->reserva_id;
    }

    public function getAdjuntos(): array {
        return $this->adjuntos;
    }

    public function setAdjuntos(array $array): void {
        $this->adjuntos = $array;
    }

    public function agregarAdjuntos(ArchivoNota $archivo): void {
        $this->adjuntos[] = $archivo;
    }

    public function tieneAdjuntos(): bool {
        return !empty($this->adjuntos);
    }

    public function cantidadAdjuntos(): int {
        return count($this->adjuntos);
    }

    public function toDTO(): RespuestaNotaDTO {
        return new RespuestaNotaDTO(
            $this->id,
            $this->motivo_visita,
            $this->texto_nota,
            $this->reserva_id
        );
    }
}