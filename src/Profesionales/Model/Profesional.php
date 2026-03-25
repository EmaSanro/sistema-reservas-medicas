<?php
namespace App\Model;

use App\Model\DTOs\RespuestaProfesionalDTO;

class Profesional extends Usuario
{
    private string $profesion;

    private function __construct()
    {

    }

    public static function create(string $nombre, string $apellido, string $profesion, string|null $email, string|null $telefono, bool $activo = true, string|null $motivo_baja = null, string|null $fecha_baja = null, string $password): self
    {
        $profesional = new self();
        $profesional->setNombre($nombre);
        $profesional->setApellido($apellido);
        $profesional->setRol(Roles::PROFESIONAL);
        $profesional->setProfesion($profesion);
        $profesional->setEmail($email);
        $profesional->setTelefono($telefono);
        $profesional->setActivo($activo);
        $profesional->setMotivoBaja($motivo_baja);
        $profesional->setFechaBaja($fecha_baja);
        $profesional->setPassword($password);

        return $profesional;
    }

    public function setProfesion(string $profesion): void
    {
        $this->maxLength($profesion, 60, 'profesion');
        $this->profesion = $profesion;
    }

    public function getProfesion(): string
    {
        return $this->profesion;
    }

    public static function fromDatabase(array $data): self
    {
        $profesional = new self();
        $profesional->id = (int) $data["id"];
        $profesional->nombre = $data["nombre"];
        $profesional->apellido = $data["apellido"];
        $profesional->rol = Roles::PROFESIONAL;
        $profesional->profesion = $data["profesion"];
        $profesional->email = $data["email"];
        $profesional->telefono = $data["telefono"];
        $profesional->activo = (bool) $data["activo"];
        $profesional->motivo_baja = $data["motivo_baja"];
        $profesional->fecha_baja = $data["fecha_baja"];
        $profesional->password = $data["password"];

        return $profesional;
    }
}
