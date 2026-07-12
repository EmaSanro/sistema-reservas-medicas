<?php
namespace App\Nota\Mapper;

use App\Nota\DTOs\Request\ActualizarNotaRequest;
use App\Nota\DTOs\Request\CrearNotaRequest;
use App\Nota\DTOs\Response\RespuestaNota;
use App\Nota\Model\Nota;

class NotaMapper {

    public static function fromRequestCrear(CrearNotaRequest $request): Nota {
        return Nota::create(
            $request->getMotivoVisita(),
            $request->getTextoNota(),
            $request->getReservaId(),
        );
    }

    /**
     * Aplica los cambios del request sobre la entidad ya cargada (patch).
     * No reemplaza la instancia — se preservan id y estado persistido.
     * Solo se pisan los campos que vinieron no-null en el request.
     */
    public static function aplicarActualizacion(Nota $nota, ActualizarNotaRequest $request): void {
        if ($request->getMotivoVisita() !== null) {
            $nota->setMotivoVisita($request->getMotivoVisita());
        }
        if ($request->getTextoNota() !== null) {
            $nota->setTextoNota($request->getTextoNota());
        }
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

    /**
     * @param list<\App\Nota\DTOs\Response\RespuestaArchivoNota> $adjuntos
     */
    public static function toResponse(Nota $nota, array $adjuntos = []): RespuestaNota {
        return new RespuestaNota(
            $nota->getId(),
            $nota->getMotivoVisita(),
            $nota->getTextoNota(),
            $nota->getReservaId(),
            $adjuntos,
        );
    }
}
