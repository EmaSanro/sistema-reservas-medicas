<?php
namespace App\Pacientes\DTOs\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: "ActualizarPacienteRequest")]
class ActualizarPacienteRequest {
    #[OA\Property(example: "Juan", nullable: true)]
    private string|null $nombre;
    #[OA\Property(example: "Perez", nullable: true)]
    private string|null $apellido;
    #[OA\Property(example: "jPerez@gmail.com", nullable: true)]
    private string|null $email;
    #[OA\Property(example: "", nullable: true)]
    private string|null $telefono;

    public function __construct(string|null $nombre = null, string|null $apellido = null, string|null $email = null, string|null $telefono = null) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->email = $email;
        $this->telefono = $telefono;
    }

    public function getNombre(): string|null {
        return $this->nombre;
    }

    public function getApellido(): string|null {
        return $this->apellido;
    }

    public function getEmail(): string|null {
        return $this->email;
    }

    public function getTelefono(): string|null {
        return $this->telefono;
    }

    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }

    public function setApellido(string $apellido): void {
        $this->apellido = $apellido;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setTelefono(string $telefono): void {
        $this->telefono = $telefono;
    }
}