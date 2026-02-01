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

    public function __construct(int $id, string $nombre, string|null $email, string|null $telefono) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->email = $email ?? null;
        $this->telefono = $telefono ?? null;
    }
}