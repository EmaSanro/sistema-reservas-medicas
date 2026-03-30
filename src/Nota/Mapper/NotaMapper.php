<?php 
namespace App\Nota\Mapper;

use App\Nota\DTOs\Request\ActualizarNotaRequest;
use App\Nota\DTOs\Request\CrearNotaRequest;
use App\Nota\DTOs\Response\RespuestaNota;
use App\Nota\Model\Nota;

class NotaMapper {

    public static function fromRequest(CrearNotaRequest|ActualizarNotaRequest $nota): Nota {
        return Nota::create(
            $nota->getMotivoVisita(),
            $nota->getTextoNota(),
            $nota->getReservaId()
        );
    }

    public static function toRequestCrear(array $data): CrearNotaRequest {
        return new CrearNotaRequest(
            trim($data['motivo_visita']),
            trim($data['texto_nota']),
            $data['reserva_id']
        );
    }

    public static function toRequestActualizar(array $data): ActualizarNotaRequest {
        return new ActualizarNotaRequest(
            isset($data['motivo_visita']) ? trim($data['motivo_visita']) : null,
            isset($data['texto_nota']) ? trim($data['texto_nota']) : null
        );
    }
    public static function toResponse(Nota $nota): RespuestaNota {
        return new RespuestaNota(
            $nota->getId(),
            $nota->getMotivoVisita(),
            $nota->getTextoNota(),
            $nota->getReservaId()
        );
    }
}