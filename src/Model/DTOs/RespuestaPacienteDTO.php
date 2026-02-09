<?php
namespace App\Model\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: "RespuestaPaciente")]
class RespuestaPacienteDTO {
    #[OA\Property(example: 10)]
    public readonly int $id;
    #[OA\Property(example: "Alonso Martinez")]
    public readonly string $nombre;
    #[OA\Property(example: "aMartinez@gmail.com")]
    public readonly string|null $email;
    #[OA\Property(example: "1234567890")]
    public readonly string|null $telefono;
    #[OA\Property(example: "true")]
    public readonly bool $activo;
    #[OA\Property(example: "Cambio de pais")]
    public readonly string $motivo_baja;
    #[OA\Property(example: "2026-06-15 14:32:34")]
    public readonly string $fecha_baja;

    public function __construct(int $id, string $nombre, string|null $email, string|null $telefono, bool $activo, string|null $motivo_baja, string|null $fecha_baja) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->email = $email ?? null;
        $this->telefono = $telefono ?? null;
        $this->motivo_baja = $motivo_baja;
        $this->fecha_baja = $fecha_baja;
    }
}