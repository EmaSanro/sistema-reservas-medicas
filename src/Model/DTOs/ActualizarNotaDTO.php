<?php

class ActualizarNotaDTO {
    private string|null $motivo_visita;
    private string|null $texto_nota;
    private int|null $reserva_id;

    public function __construct(string|null $motivo_visita = null, string|null $texto_nota = null, int|null $reserva_id = null) {
        $this->motivo_visita = $motivo_visita;
        $this->texto_nota = $texto_nota;
        $this->reserva_id = $reserva_id;
    }

    public function getMotivoVisita(): ?string {
        return $this->motivo_visita;
    }

    public function getTextoNota(): ?string {
        return $this->texto_nota;
    }

    public function getReservaId(): ?int {
        return $this->reserva_id;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['motivo_visita'] ?? null,
            $data['texto_nota'] ?? null,
            $data['reserva_id'] ?? null
        );
    }
}