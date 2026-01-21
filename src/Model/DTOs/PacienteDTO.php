<?php
namespace App\Model\DTOs;

use App\Controller\Validaciones;
use OpenApi\Attributes as OA;

#[Oa\Schema(schema: "Paciente", required: ["nombre", "apellido", "email", "telefono"])]
class PacienteDTO {
    #[OA\Property(example: "Juan")]
    private string $nombre;
    #[OA\Property(example: "Perez")]
    private string $apellido;
    #[OA\Property(example: "juanPerez@gmail.com")]
    private string|null $email;
    #[OA\Property(example: "0118574892")]
    private string|null $telefono;
    private string $password;

    public function __construct(string $nombre, string $apellido, string|null $email, string|null $telefono, string $password) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->email = $email ?? "";
        $this->telefono = $telefono ?? "";
        $this->password = $password;
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

    public function getPassword(): string {
        return $this->password;
    }

    public static function fromArray($input) {
        if(!isset($input["nombre"], $input["apellido"], $input["password"]) || !(isset($input["email"]) || isset($input["telefono"]))) {
            throw new \InvalidArgumentException("ERROR: Completa los campos requeridos(nombre, apellido, contraseña, email y/o telefono)");
        }
        if(isset($input["email"]) && !filter_var($input["email"], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("ERROR : Email invalido");
        }
        
        return new self(
            ucwords(strtolower($input["nombre"])), 
            ucwords(strtolower($input["apellido"])), 
            $input["email"] ?? null,
            $input["telefono"] ?? null,
            $input["password"]
        );
    }
}