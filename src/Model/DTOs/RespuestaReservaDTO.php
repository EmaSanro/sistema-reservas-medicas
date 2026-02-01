<?php
namespace App\Model\DTOs;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: "RespuestaReserva")]
class RespuestaReservaDTO {
    #[OA\Property(example: 15)]
    public readonly int $idReserva;
    #[OA\Property(example: "24")]
    public readonly int $idPaciente;
    #[OA\Property(example: "12")]
    public readonly int $idProfesional;
    #[OA\Property(example: "2026-04-18 15:00:00")]
    public readonly string $fecha;

    public function __construct($id, $idPaciente, $idProfesional, $fecha) {
        $this->idReserva = $id;
        $this->idPaciente = $idPaciente;
        $this->idProfesional = $idProfesional;
        $this->fecha = $fecha;
    }
}