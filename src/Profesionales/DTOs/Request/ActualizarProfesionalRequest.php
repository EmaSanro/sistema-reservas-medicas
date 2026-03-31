<?php
namespace App\Profesionales\DTOs\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: "ActualizarProfesionalRequest")]
class ActualizarProfesionalRequest {
    #[OA\Property(example: "Juan", nullable: true)]
    private string|null $nombre;
    #[OA\Property(example: "Perez", nullable: true)]
    private string|null $apellido;
    #[OA\Property(example: "Cardiologo", nullable: true)]
    private string|null $profesion;
    #[OA\Property(example: "juanPerez@gmail.com", nullable: true)]
    private string|null $email;
    #[OA\Property(example: "123456789", nullable: true)]
    private string|null $telefono;
    public function __construct(string|null $nombre, string|null $apellido, string|null $profesion, string|null $email, string|null $telefono) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->profesion = $profesion;
        $this->email = $email;
        $this->telefono = $telefono;
    }

    public function getNombre(): string|null {
        return $this->nombre;
    }

    public function getApellido(): string|null {
        return $this->apellido;
    }

    public function getProfesion(): string|null {
        return $this->profesion;
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

    public function setProfesion(string $profesion): void {
        $this->profesion = $profesion;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setTelefono(?string $telefono): void {
        $this->telefono = $telefono;
    }
}