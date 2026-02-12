<?php
namespace App\Model\DTOs;
use App\Exceptions\ValidationException;

class CrearNotaDTO {
    private string $motivo_visita;
    private string $texto_nota;
    private int $reserva_id;
    
    public function __construct(string $motivo_visita, string $texto_nota, int $reserva_id) {
        $this->motivo_visita = $motivo_visita;
        $this->texto_nota = $texto_nota;
        $this->reserva_id = $reserva_id;
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

    public static function fromArray($input) {
        if(!isset($input["motivo_visita"], $input["texto_nota"], $input["reserva_id"])) {
            throw new ValidationException("ERROR: Completa los campos requeridos(motivo_visita, texto_nota, reserva_id)");
        }
        if(empty(trim($input["motivo_visita"])) || empty(trim($input["texto_nota"])) || empty(trim($input["reserva_id"]))) {
            throw new ValidationException("ERROR: Ninguno de los campos puede estar vacio!");
        }

        return new self(
            $input["motivo_visita"],
            $input["texto_nota"],
            (int)$input["reserva_id"]
        );
    }
}