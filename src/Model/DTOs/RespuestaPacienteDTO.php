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
    public readonly string $email;
    #[OA\Property(example: "1234567890")]
    public readonly string $telefono;

    public function __construct(int $id, string $nombre, string $email, string $telefono) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->telefono = $telefono;
    }

    public static function fromArray(array $array) {
        return new self(
            (int)$array["id"],
            $array["nombre"] . " " . $array["apellido"],
            $array["email"],
            $array["telefono"]
        );
    }
}