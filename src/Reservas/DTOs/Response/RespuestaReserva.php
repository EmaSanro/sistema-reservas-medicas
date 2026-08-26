<?php
namespace App\Reservas\DTOs\Response;

use OpenApi\Attributes as OA;

/**
 * Forma unica de una reserva en la API: los participantes vienen anidados y ya
 * resueltos por JOIN, para que el cliente no tenga que pedirlos aparte.
 */
#[OA\Schema(schema: "RespuestaReserva")]
final class RespuestaReserva {
    public function __construct(
        #[OA\Property(example: 15)]
        public readonly int $idReserva,
        #[OA\Property(ref: "#/components/schemas/PacienteReserva")]
        public readonly PacienteReserva $paciente,
        #[OA\Property(ref: "#/components/schemas/ProfesionalReserva")]
        public readonly ProfesionalReserva $profesional,
        #[OA\Property(example: "2026-04-18 15:00:00")]
        public readonly string $fecha_reserva,
        #[OA\Property(example: "Confirmada")]
        public readonly string $estado,
    ) {}
}
