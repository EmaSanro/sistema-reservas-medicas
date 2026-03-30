<?php
namespace App\Nota\DTOs\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: "CrearNotaRequest", required: ["motivo_visita", "texto_nota", "reserva_id"])]
class CrearNotaRequest {
    #[OA\Property(example: "Consulta general")]
    private string $motivo_visita;
    #[OA\Property(example: "El paciente refiere dolor de cabeza desde hace 3 días...")]
    private string $texto_nota;
    #[OA\Property(example: 123)]
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
}