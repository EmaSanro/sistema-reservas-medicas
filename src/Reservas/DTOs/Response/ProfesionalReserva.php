<?php
namespace App\Reservas\DTOs\Response;

use OpenApi\Attributes as OA;

/**
 * Datos del profesional embebidos en una reserva. Incluye la profesion porque
 * es lo que la UI muestra junto al nombre en el listado del paciente.
 */
#[OA\Schema(schema: "ProfesionalReserva")]
final class ProfesionalReserva {
    public function __construct(
        #[OA\Property(example: 12)]
        public readonly int $id,
        #[OA\Property(example: "Lucero")]
        public readonly string $nombre,
        #[OA\Property(example: "Gimenez")]
        public readonly string $apellido,
        #[OA\Property(example: "Pediatra")]
        public readonly string $profesion,
    ) {}
}
