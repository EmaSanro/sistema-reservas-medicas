<?php
namespace App\Nota\Mapper;

use App\Nota\DTOs\Request\CrearArchivoNotaRequest;
use App\Nota\DTOs\Response\RespuestaArchivoNota;
use App\Nota\Model\ArchivoNota;

class ArchivoNotaMapper {

    public static function toRequestCrear(array $data): CrearArchivoNotaRequest {
        return new CrearArchivoNotaRequest(
            $data["nombreOriginal"],
            $data["nombreSistema"],
            $data["ruta"],
            $data["tipo"],
            $data["tamanio"],
            $data["fechaSubida"],
            $data["idNota"]
        );
    }

    public static function toArchivoNota(array $data): ArchivoNota {
        return ArchivoNota::create(
            $data["nombreOriginal"],
            $data["nombreSistema"],
            $data["ruta"],
            $data["tipo"],
            $data["tamanio"],
            $data["fechaSubida"],
            $data["idNota"]
        );
    }

    public static function toResponse(ArchivoNota $archivo): RespuestaArchivoNota {
        return new RespuestaArchivoNota(
            $archivo->getId(),
            $archivo->getNombreOriginal(),
            $archivo->getPeso(),
            $archivo->getFechaSubida(),
            $archivo->getNotaId()
        );
    }
}