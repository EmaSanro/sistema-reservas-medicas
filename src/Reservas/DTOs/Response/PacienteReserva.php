<?php
namespace App\Reservas\DTOs\Response;

use OpenApi\Attributes as OA;

/**
 * Datos del paciente embebidos en una reserva. Lo minimo para renderizar una
 * lista sin pedirle al cliente una consulta extra por cada fila.
 */
#[OA\Schema(schema: "PacienteReserva")]
final class PacienteReserva {
    public function __construct(
        #[OA\Property(example: 24)]
        public readonly int $id,
        #[OA\Property(example: "Ana")]
        public readonly string $nombre,
        #[OA\Property(example: "Torres")]
        public readonly string $apellido,
    ) {}
}
