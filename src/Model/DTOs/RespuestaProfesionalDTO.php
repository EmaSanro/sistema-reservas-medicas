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

    public function __construct(int $id, string $nombreCompleto, string $profesion, string|null $email, string|null $telefono) {
        $this->id = $id;
        $this->nombre = $nombreCompleto;
        $this->profesion = $profesion;
        $this->email = $email ?? null;
        $this->telefono = $telefono ?? null;
    }

    public static function fromArray(array $array) {
        return new self(
            (int)$array["id"],
            $array["nombre"] . " " . $array["apellido"],
            $array["profesion"],
            $array["email"] ?? null,
            $array["telefono"] ?? null
        );
    }
}