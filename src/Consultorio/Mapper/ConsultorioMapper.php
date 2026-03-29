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
            $input['horarioApertura'],
            $input['horarioCierre'],
            $input['idProfesional'] ?? null
        );
    }

    public static function toRequestActualizar(array $input): ActualizarConsultorioRequest {
        return new ActualizarConsultorioRequest(
            isset($input['ciudad']) ? trim($input["ciudad"]) : null,
            isset($input["direccion"]) ? trim($input['direccion']) : null,
            isset($input['horarioApertura']) ?? null,
            isset($input['horarioCierre']) ?? null
        );
    }

    public static function fromRequest(CrearConsultorioRequest|ActualizarConsultorioRequest $request): Consultorio {
        return Consultorio::create(
            $request->getCiudad(),
            $request->getDireccion(),
            $request->getHorarioApertura(),
            $request->getHorarioCierre(),
            $request->getIdProfesional()
        );
    }

    public static function toResponse(Consultorio $consultorio): RespuestaConsultorio {
        return new RespuestaConsultorio(
            $consultorio->getId(),
            $consultorio->getCiudad(),
            $consultorio->getDireccion(),
            "{$consultorio->getHorarioApertura()} - {$consultorio->getHorarioCierre()}",
            $consultorio->getIdProfesional()
        );
    }
}