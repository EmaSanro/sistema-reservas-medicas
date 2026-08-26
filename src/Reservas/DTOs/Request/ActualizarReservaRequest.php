<?php
namespace App\Reservas\DTOs\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: "ActualizarReservaRequest")]
class ActualizarReservaRequest {
    #[OA\Property(example: "2026-03-15 18:45:00")]
    private string|null $fecha_reserva;
    #[OA\Property(example: "Cancelada")]
    private string|null $estado_reserva;

    public function __construct(string|null $fecha_reserva = null, string|null $estado_reserva = null) {
        $this->fecha_reserva = $fecha_reserva;
        $this->estado_reserva = $estado_reserva;
    }

    public function getFechaReserva(): string|null {
        return $this->fecha_reserva;
    }

    public function getEstadoReserva(): string|null {
        return $this->estado_reserva;
    }

    public function setFechaReserva(string $fecha_reserva): void {
        $this->fecha_reserva = $fecha_reserva;
    }

    public function setEstadoReserva(string $estado_reserva): void {
        $this->estado_reserva = $estado_reserva;
    }
}