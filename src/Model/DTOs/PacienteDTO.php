<?php
namespace App\Model\DTOs;
use OpenApi\Attributes as OA;

#[Oa\Schema(schema: "Paciente", required: ["nombre", "apellido", "email", "telefono"])]
class PacienteDTO {
    #[OA\Property(example: "Juan")]
    private string $nombre;
    #[OA\Property(example: "Perez")]
    private string $apellido;
    #[OA\Property(example: "juanPerez@gmail.com")]
    private string $email;
    #[OA\Property(example: "0118574892")]
    private string $telefono;

    public function __construct(string $nombre, string $apellido, string $email, string $telefono) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->email = $email;
        $this->telefono = $telefono;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    public function getApellido(): string {
        return $this->apellido;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getTelefono(): string {
        return $this->telefono;
    }

    public static function fromArray($input) {
        if(!isset($input["nombre"], $input["apellido"], $input["email"], $input["telefono"])) {
            throw new \InvalidArgumentException("ERROR: Todos los campos son requeridos(nombre, apellido, email, telefono)");
        }
        if(!filter_var($input["email"], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("ERROR : Email invalido");
        }

        return new self(
            ucwords(strtolower($input["nombre"])), 
            ucwords(strtolower($input["apellido"])), 
            $input["email"],
            $input["telefono"]);
    }
}