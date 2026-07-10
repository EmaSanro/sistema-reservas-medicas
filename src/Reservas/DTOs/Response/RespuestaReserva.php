<?php
namespace App\Reservas\DTOs\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: "RespuestaReserva")]
class RespuestaReserva {
    #[OA\Property(example: 15)]
    public readonly int $idReserva;
    #[OA\Property(example: "24")]
    public readonly int $idPaciente;
    #[OA\Property(example: "12")]
    public readonly int $idProfesional;
    #[OA\Property(example: "2026-04-18 15:00:00")]
    public readonly string $fecha_reserva;
    #[OA\Property(example:"Confirmada")]
    public readonly string $estado;

    public function __construct(int $id, int $idPaciente, int $idProfesional, string $fecha_reserva, string $estado) {
        $this->idReserva = $id;
        $this->idPaciente = $idPaciente;
        $this->idProfesional = $idProfesional;
        $this->fecha_reserva = $fecha_reserva;
        $this->estado = $estado;
    }
}