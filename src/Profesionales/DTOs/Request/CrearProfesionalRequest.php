<?php
namespace App\Profesionales\DTOs\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: "CrearProfesionalRequest", required: ["nombre", "apellido", "profesion", "email", "telefono", "password"])]
class CrearProfesionalRequest {
    #[Oa\Property(example: "Roberto")]
    private string $nombre;
    #[Oa\Property(example: "Falcao")]
    private string $apellido;
    #[Oa\Property(example: "Odontologo")]
    private string $profesion;
    #[Oa\Property(example: "roberFalcao@outlook.com")]
    private string|null $email;
    #[Oa\Property(example: "0984728910")]
    private string|null $telefono;
    #[Oa\Property(example: "Rober13#")]
    private string $password;

    public function __construct(string $nombre, string $apellido, string $profesion, string|null $email, string|null $telefono, string $password) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->profesion = $profesion;
        $this->email = $email ?? null;
        $this->telefono = $telefono ?? null;
        $this->password = $password;
    }

    public function getNombre(): string {
        return $this->nombre;
    }
    public function getApellido(): string {
        return $this->apellido;
    }

    public function getProfesion(): string {
        return $this->profesion;
    }

    public function getEmail(): string|null {
        return $this->email;
    }

    public function getTelefono(): string|null {
        return $this->telefono;
    }

    public function getPassword(): string {
        return $this->password;
    }
}