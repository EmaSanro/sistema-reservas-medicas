<?php
namespace App\Pacientes\Mapper;

use App\Auth\Model\Usuario;
use App\Model\Roles;
use App\Pacientes\DTOs\Request\ActualizarPacienteRequest;
use App\Pacientes\DTOs\Request\CrearPacienteRequest;
use App\Pacientes\DTOs\Response\RespuestaPaciente;

class PacienteMapper {

    public static function toRequestCrear(array $input): CrearPacienteRequest {
        return new CrearPacienteRequest(
            trim($input["nombre"]),
            trim($input["apellido"]),
            isset($input["email"]) ? trim($input["email"]) : null,
            isset($input["telefono"]) ? trim($input["telefono"]) : null,
            $input["password"]
        );
    }

    public static function toRequestActualizar(array $input): ActualizarPacienteRequest {
        return new ActualizarPacienteRequest(
            isset($input["nombre"]) ? trim($input["nombre"]) : null,
            isset($input["apellido"]) ? trim($input["apellido"]) : null,
            isset($input["email"]) ? trim($input["email"]) : null,
            isset($input["telefono"]) ? trim($input["telefono"]) : null
        );
    }

    public static function fromRequestCrear(CrearPacienteRequest $request): Usuario {
        return Usuario::create(
            $request->getNombre(),
            $request->getApellido(),
            Roles::PACIENTE,
            $request->getEmail(),
            $request->getTelefono(),
            password: $request->getPassword()
        );
    }

    public static function fromRequestActualizar(Usuario $paciente, ActualizarPacienteRequest $request): void {
        if ($request->getNombre() !== null) {
            $paciente->setNombre($request->getNombre());
        }
        if ($request->getApellido() !== null) {
            $paciente->setApellido($request->getApellido());
        }
        if ($request->getEmail() !== null) {
            $paciente->setEmail($request->getEmail());
        }
        if ($request->getTelefono() !== null) {
            $paciente->setTelefono($request->getTelefono());
        }
    }

    public static function toResponse(Usuario $paciente): RespuestaPaciente {
        return new RespuestaPaciente(
            $paciente->getId(),
            "{$paciente->getNombre()} {$paciente->getApellido()}",
            $paciente->getEmail(),
            $paciente->getTelefono(),
            $paciente->isActivo(),
            $paciente->getMotivoBaja(),
            $paciente->getFechaBaja()
        );
    }
}