<?php
namespace App\Model;

use App\Model\DTOs\RespuestaPacienteDTO;
use App\Shared\Entity;

class Usuario extends Entity
{
    private const PATRON_UPPER_CASE_PASSWORD = '/[A-Z]/';
    private const PATRON_LOWER_CASE_PASSWORD = '/[a-z]/';
    private const PATRON_DIGITO_PASSWORD = '/[0-9]/';
    protected string $nombre;
    protected string $apellido;
    protected string $rol;
    protected string|null $email;
    protected string|null $telefono;
    protected bool $activo;
    protected string|null $motivo_baja;
    protected string|null $fecha_baja;
    protected string $password;

    protected function __construct()
    {

    }

    public static function create(string $nombre, string $apellido, string $rol, string|null $email, string|null $telefono, bool $activo = true, string|null $motivo_baja = null, string|null $fecha_baja = null, string $password): self
    {
        $usuario = new self();
        $usuario->setNombre($nombre);
        $usuario->setApellido($apellido);
        $usuario->setRol($rol);
        $usuario->setEmail($email);
        $usuario->setTelefono($telefono);
        $usuario->setActivo($activo);
        $usuario->setMotivoBaja($motivo_baja);
        $usuario->setFechaBaja($fecha_baja);
        $usuario->setPassword($password);

        return $usuario;
    }

    public function setNombre(string $nombre): void
    {
        $this->maxLength($nombre, 50, 'nombre');
        $this->nombre = $nombre;
    }

    public function setApellido(string $apellido): void
    {
        $this->maxLength($apellido, 70, 'apellido');
        $this->apellido = $apellido;
    }

    public function setRol(string $rol): void
    {
        $this->rol = $rol;
    }

    public function setEmail(string|null $email): void
    {
        $this->email = $email;
    }

    public function setTelefono(string|null $telefono): void
    {
        $this->telefono = $telefono;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }

    public function setMotivoBaja(string|null $motivo_baja): void
    {
        $this->motivo_baja = $motivo_baja;
    }

    public function setFechaBaja(string|null $fecha_baja): void
    {
        $this->fecha_baja = $fecha_baja;
    }

    public function setPassword(string $password): void
    {
        $this->minLength($password, 8, 'password');
        $this->matchPattern($password, self::PATRON_LOWER_CASE_PASSWORD, 'password', 'La contraseña debe tener al menos una minuscula');
        $this->matchPattern($password, self::PATRON_UPPER_CASE_PASSWORD, 'password', 'La contraseña debe tener al menos una mayuscula');
        $this->matchPattern($password, self::PATRON_DIGITO_PASSWORD, 'password', 'La contraseña debe tener al menos un digito');
        $this->password = $password;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getApellido(): string
    {
        return $this->apellido;
    }

    public function getRol(): string
    {
        return $this->rol;
    }

    public function getEmail(): string|null
    {
        return $this->email ?? null;
    }

    public function getTelefono(): string|null
    {
        return $this->telefono ?? null;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function getMotivoBaja(): string|null
    {
        return $this->motivo_baja ?? null;
    }

    public function getFechaBaja(): string|null
    {
        return $this->fecha_baja ?? null;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public static function fromDatabase(array $data): self
    {
        $usuario = new self();
        $usuario->id = (int) $data["id"];
        $usuario->nombre = $data["nombre"];
        $usuario->apellido = $data["apellido"];
        $usuario->rol = $data["rol"];
        $usuario->email = $data["email"];
        $usuario->telefono = $data["telefono"];
        $usuario->activo = (bool) $data["activo"];
        $usuario->motivo_baja = $data["motivo_baja"];
        $usuario->fecha_baja = $data["fecha_baja"];
        $usuario->password = $data["password"];

        return $usuario;
    }
}
