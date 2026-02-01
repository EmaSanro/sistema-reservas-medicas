<?php
namespace App\Model;

use App\Model\DTOs\RespuestaPacienteDTO;
class Usuario {
    private int $id;
    private string $nombre;
    private string $apellido;
    private string $rol;
    private string|null $email;
    private string|null $telefono;
    private string $password;

    public function __construct(int $id, string $nombre, string $apellido, string $rol, string|null $email, string|null $telefono, string $password) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->rol = $rol;
        $this->email = $email ?? null;
        $this->telefono = $telefono ?? null;
        $this->password = $password;
    }

    public function getId() {
        return $this->id;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    public function getApellido(): string {
        return $this->apellido;
    }

    public function getRol(): string {
        return $this->rol;
    }

    public function getEmail(): string|null {
        return $this->email ?? null;
    }

    public function getTelefono(): string|null {
        return $this->telefono ?? null;
    }

    public function getPassword() : string {
        return $this->password;
    }

    public function toDTO() {
        return new RespuestaPacienteDTO(
            $this->id,
            "$this->nombre $this->apellido",
            $this->email,
            $this->telefono
        ); 
    }
}