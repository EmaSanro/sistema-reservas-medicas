<?php
namespace App\Pacientes\DTOs\Request;

use OpenApi\Attributes as OA;

#[Oa\Schema(schema: "CrearPacienteRequest", required: ["nombre", "apellido", "email", "telefono", "password"])]
class CrearPacienteRequest {
    #[OA\Property(example: "Juan")]
    private string $nombre;
    #[OA\Property(example: "Perez")]
    private string $apellido;
    #[OA\Property(example: "juanPerez@gmail.com")]
    private string|null $email;
    #[OA\Property(example: "0118574892")]
    private string|null $telefono;
    #[OA\Property(example: "JuanP34$")]
    private string $password;

    public function __construct(string $nombre, string $apellido, string|null $email, string|null $telefono, string $password) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
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