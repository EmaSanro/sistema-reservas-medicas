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
    private bool $activo;
    private string|null $motivo_baja;
    private string|null $fecha_baja;
    private string $password;

    public function __construct(int $id, string $nombre, string $apellido, string $rol, string|null $email, string|null $telefono, bool $activo, string|null $motivo_baja, string|null $fecha_baja, string $password) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->rol = $rol;
        $this->email = $email ?? null;
        $this->telefono = $telefono ?? null;
        $this->activo = $activo;
        $this->motivo_baja = $motivo_baja ?? null;
        $this->fecha_baja = $fecha_baja ?? null;
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

    public function getActivo(): bool {
        return $this->activo;
    }

    public function getMotivoBaja(): string|null {
        return $this->motivo_baja ?? null;
    }

    public function getFechaBaja(): string|null {
        return $this->fecha_baja ?? null;
    }

    public function getPassword() : string {
        return $this->password;
    }

    public function toDTO() {
        return new RespuestaPacienteDTO(
            $this->id,
            "$this->nombre $this->apellido",
            $this->email,
            $this->telefono,
            $this->activo,
            $this->motivo_baja,
            $this->fecha_baja
        ); 
    }
}