<?php
namespace App\Model\DTOs;

use App\Exceptions\ValidationException;

class ActualizarNotaDTO {
    private string $motivo_visita;
    private string $texto_nota;

    public function __construct(string $motivo_visita, string $texto_nota) {
        $this->motivo_visita = $motivo_visita;
        $this->texto_nota = $texto_nota;
    }

    public function getMotivoVisita(): string {
        return $this->motivo_visita;
    }

    public function getTextoNota(): string {
        return $this->texto_nota;
    }

    public static function fromArray(array $data): self {
        if(empty($data["motivo_visita"]) || empty($data["texto_nota"])) {
            throw new ValidationException("Todos los campos deben estar completados!");
        }
        
        return new self(
            $data['motivo_visita'],
            $data['texto_nota']
        );
    }
}