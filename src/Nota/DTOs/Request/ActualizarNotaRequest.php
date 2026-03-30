<?php
namespace App\Nota\DTOs\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: "ActualizarNotaRequest")]
class ActualizarNotaRequest {

    public function __construct(
        #[OA\Property(example: "Consulta general", nullable: true)]
        private ?string $motivo_visita = null,
        #[OA\Property(example: "Un cuerpo de ejemplo", nullable: true)]
        private ?string $texto_nota = null
    ) {}

    public function getMotivoVisita(): string {
        return $this->motivo_visita;
    }

    public function getTextoNota(): string {
        return $this->texto_nota;
    }

    public function setMotivoVisita(string $motivo_visita): void {
        $this->motivo_visita = $motivo_visita;
    }

    public function setTextoNota(string $texto_nota): void {
        $this->texto_nota = $texto_nota;
    }
}