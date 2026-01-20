<?php
namespace App\Model\DTOs;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: "RespuestaReserva")]
class RespuestaReservaDTO {
    #[OA\Property(example: 15)]
    public readonly int $idReserva;
    #[OA\Property(example: "Juan perez")]
    public readonly string $paciente;
    #[OA\Property(example: "Roberto Falcao")]
    public readonly string $profesional;
    #[OA\Property(example: "2026-04-18 15:00:00")]
    public readonly string $fecha;

    public function __construct($id, $paciente, $profesional, $fecha) {
        $this->idReserva = $id;
        $this->paciente = $paciente;
        $this->profesional = $profesional;
        $this->fecha = $fecha;
    }

    public static function fromArray(array $array) {
        return new self(
            $array["id"],
            $array["paciente"],
            $array["profesional"],
            $array["fecha_reserva"],
        );
    }
}