<?php
namespace App\Consultorio\Mapper;

use App\Consultorio\DTOs\Request\ActualizarConsultorioRequest;
use App\Consultorio\DTOs\Request\CrearConsultorioRequest;
use App\Consultorio\DTOs\Response\RespuestaConsultorio;
use App\Consultorio\Model\Consultorio;

class ConsultorioMapper {

    public static function toRequestCrear(array $input): CrearConsultorioRequest {
        return new CrearConsultorioRequest(
            trim($input['ciudad']),
            trim($input['direccion']),
            $input['horario_apertura'],
            $input['horario_cierre'],
            $input['idProfesional'] ?? null
        );
    }

    public static function toRequestActualizar(array $input): ActualizarConsultorioRequest {
        return new ActualizarConsultorioRequest(
            isset($input['ciudad']) ? trim($input["ciudad"]) : null,
            isset($input["direccion"]) ? trim($input['direccion']) : null,
            $input['horario_apertura'] ?? null,
            $input['horario_cierre'] ?? null
        );
    }

    public static function fromRequestCrear(CrearConsultorioRequest $request): Consultorio {
        return Consultorio::create(
            $request->getCiudad(),
            $request->getDireccion(),
            $request->getHorarioApertura(),
            $request->getHorarioCierre(),
            $request->getIdProfesional()
        );
    }

    /**
     * Aplica los cambios del request sobre la entidad ya cargada (patch).
     * No reemplaza la instancia — se preservan id e idprofesional.
     * Solo se pisan los campos que vinieron no-null en el request.
     */
    public static function aplicarActualizacion(Consultorio $consultorio, ActualizarConsultorioRequest $request): void {
        if ($request->getCiudad() !== null) {
            $consultorio->setCiudad($request->getCiudad());
        }
        if ($request->getDireccion() !== null) {
            $consultorio->setDireccion($request->getDireccion());
        }
        if ($request->getHorarioApertura() !== null) {
            $consultorio->setHorarioApertura($request->getHorarioApertura());
        }
        if ($request->getHorarioCierre() !== null) {
            $consultorio->setHorarioCierre($request->getHorarioCierre());
        }
    }

    public static function toResponse(Consultorio $consultorio): RespuestaConsultorio {
        return new RespuestaConsultorio(
            $consultorio->getId(),
            $consultorio->getDireccion(),
            $consultorio->getCiudad(),
            "{$consultorio->getHorarioApertura()} - {$consultorio->getHorarioCierre()}",
            $consultorio->getIdProfesional()
        );
    }
}