<?php
namespace App\Model\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: "RespuestaProfesional")]
class RespuestaProfesionalDTO {
    #[OA\Property(example: 12)]
    public readonly int $id;
    #[OA\Property(example: "Lucero Gimenez")]
    public readonly string $nombre;
    #[OA\Property(example: "Pediatra")]
    public readonly string $profesion;
    #[OA\Property(example: "luceroG@outlook.com")]
    public readonly string|null $email;
    #[OA\Property(example: "347586901")]
    public readonly string|null $telefono;
    #[OA\Property(example: "true")]
    public readonly bool $activo;
    #[OA\Property(example: "Cambio de hospital")]
    public readonly string|null $motivo_baja;
    #[OA\Property(example: "2026-03-1 08:00:13")]
    public readonly string|null $fecha_baja;

    public function __construct(int $id, string $nombreCompleto, string $profesion, string|null $email, string|null $telefono, bool $activo, string|null $motivo_baja, string|null $fecha_baja) {
        $this->id = $id;
        $this->nombre = $nombreCompleto;
        $this->profesion = $profesion;
        $this->email = $email ?? null;
        $this->telefono = $telefono ?? null;
        $this->motivo_baja = $motivo_baja;
        $this->fecha_baja = $fecha_baja;
    }
}