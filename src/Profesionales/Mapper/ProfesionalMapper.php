<?php
namespace App\Profesionales\Mapper;

use App\Profesionales\DTOs\Request\ActualizarProfesionalRequest;
use App\Profesionales\DTOs\Request\CrearProfesionalRequest;
use App\Profesionales\DTOs\Response\RespuestaProfesional;
use App\Profesionales\Model\Profesional;

class ProfesionalMapper {

    public static function toRequestCrear(array $input): CrearProfesionalRequest {
        return new CrearProfesionalRequest(
            trim($input["nombre"]),
            trim($input["apellido"]),
            trim($input["profesion"]),
            isset($input["email"]) ? trim($input["email"]) : null,
            isset($input["telefono"]) ? trim($input["telefono"]) : null,
            $input["password"]
        );
    }

    public static function toRequestActualizar(array $input): ActualizarProfesionalRequest {
        return new ActualizarProfesionalRequest(
            isset($input["nombre"]) ? trim($input["nombre"]) : null,
            isset($input["apellido"]) ? trim($input["apellido"]) : null,
            isset($input["profesion"]) ? trim($input["profesion"]) : null,
            isset($input["email"]) ? trim($input["email"]) : null,
            isset($input["telefono"]) ? trim($input["telefono"]) : null
        );
    }

    public static function fromRequestCrear(CrearProfesionalRequest $request): Profesional {
        return Profesional::create(
            $request->getNombre(),
            $request->getApellido(),
            $request->getProfesion(),
            $request->getEmail(),
            $request->getTelefono(),
            password: $request->getPassword()
        );
    }

    public static function fromRequestActualizar(Profesional $profesional, ActualizarProfesionalRequest $request): void {
        if ($request->getNombre() !== null) {
            $profesional->setNombre($request->getNombre());
        }
        if ($request->getApellido() !== null) {
            $profesional->setApellido($request->getApellido());
        }
        if ($request->getProfesion() !== null) {
            $profesional->setProfesion($request->getProfesion());
        }
        if ($request->getEmail() !== null) {
            $profesional->setEmail($request->getEmail());
        }
        if ($request->getTelefono() !== null) {
            $profesional->setTelefono($request->getTelefono());
        }
    }

    public static function toResponse(Profesional $profesional): RespuestaProfesional {
        return new RespuestaProfesional(
            $profesional->getId(),
            "{$profesional->getNombre()} {$profesional->getApellido()}",
            $profesional->getProfesion(),
            $profesional->getEmail(),
            $profesional->getTelefono(),
            $profesional->isActivo(),
            $profesional->getMotivoBaja(),
            $profesional->getFechaBaja()
        );
    }
}