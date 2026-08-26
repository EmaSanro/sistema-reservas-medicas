<?php
namespace App\Reservas\DTOs\Request;

use App\Reservas\Model\EstadoReserva;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: "CrearReservaRequest", required: ["idProfesional", "idPaciente", "fecha_reserva"])]
class CrearReservaRequest {

    #[OA\Property(example: 100)]
    private int $idProfesional;

    #[OA\Property(example: 123)]
    private int $idPaciente;
    #[OA\Property(example: "2026-03-15 18:45:00")]
    private string $fecha_reserva;
    #[OA\Property(example: "Confirmada")]
    private string $estado;

    public function __construct(int $idProfesional, int $idPaciente, string $fecha_reserva) {
        $this->idProfesional = $idProfesional;
        $this->idPaciente = $idPaciente;
        $this->fecha_reserva = $fecha_reserva;
        $this->estado = EstadoReserva::CONFIRMADA;
    }

    public function getIdProfesional(): int {
        return $this->idProfesional;
    }

    public function getIdPaciente(): int {
        return $this->idPaciente;
    }

    public function getFechaReserva(): string {
        return $this->fecha_reserva;
    }

    public function getEstadoReserva(): string {
        return $this->estado;
    }
}