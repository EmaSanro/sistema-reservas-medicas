<?php
namespace App\Nota\Service;

use App\Auth\Exceptions\ForbiddenException;
use App\Model\Reserva;
use App\Nota\DTOs\Request\ActualizarNotaRequest;
use App\Nota\DTOs\Request\CrearNotaRequest;
use App\Nota\DTOs\Response\RespuestaNota;
use App\Nota\Exceptions\NotaNotFoundException;
use App\Nota\Mapper\NotaMapper;
use App\Nota\Model\Nota;
use App\Nota\Repository\NotaRepository;
use App\Repository\ReservasRepository;
use App\Reservas\Exceptions\ReservaNotFoundException;

class NotaService {
    
    public function __construct(
        private NotaRepository $repo, 
        private ReservasRepository $reservaRepo,
        private ArchivoNotaService $archivoService
        ) {}

    public function obtenerNotaPorId(int $id, mixed $usuario): RespuestaNota {
        $nota = $this->validarPermisoNota($id, $usuario);
        $archivos = $this->archivoService->obtenerPorNotaId($id);
        $nota->setAdjuntos($archivos);
        return NotaMapper::toResponse($nota);
    }

    public function crearNota(CrearNotaRequest $request, array $archivos, mixed $usuario): RespuestaNota {
        $nota = NotaMapper::fromRequest($request);
        
        /** @var Reserva $reserva */
        $reserva = $this->reservaRepo->findById($nota->getReservaId());
        if(!$reserva) {
            throw new ReservaNotFoundException($reserva->getId());
        }
        if($usuario->id != $reserva->getIdProfesional()) {
            throw new ForbiddenException("No tienes permisos de crear notas en una reserva ajena!");
        }

        $notaCreada = $this->repo->guardarNota($nota);
        $adjuntosGuardados = [];
        if(!empty($archivos)) {
            foreach($archivos as $archivo) {
                if($archivo["error"] === UPLOAD_ERR_OK) {
                    $adjunto = $this->archivoService->guardarArchivo($notaCreada->getId(), $archivo);
                    $adjuntosGuardados[] = $adjunto;
                }
            }
        }
        $nota->setAdjuntos($adjuntosGuardados);

        return NotaMapper::toResponse($notaCreada);
    }

    public function actualizarNota(int $id, ActualizarNotaRequest $request, mixed $usuario, array $archivos): RespuestaNota {
        $nota = $this->validarPermisoNota($id, $usuario);

        if($request->getMotivoVisita() === null) {
            $request->setMotivoVisita($nota->getMotivoVisita());
        }
        if($request->getTextoNota() === null) {
            $request->setTextoNota($nota->getTextoNota());
        }

        $nota = NotaMapper::fromRequest($request);

        $notaActualizada = $this->repo->actualizarNota($id, $nota);

        if(!empty($archivos)) {
            foreach($archivos as $archivo) {
                if($archivo["error"] == UPLOAD_ERR_OK) {
                    $this->archivoService->guardarArchivo($id, $archivo);
                }
            }
        }

        $archivosAdjuntos = $this->archivoService->obtenerPorNotaId($id);
        $notaActualizada->setAdjuntos($archivosAdjuntos);

        return NotaMapper::toResponse($notaActualizada);
    }

    private function validarPermisoNota(int $notaId, mixed $usuario): Nota {
        /** @var Nota $nota */
        $nota = $this->repo->findById($notaId);
        if(!$nota) {
            throw new NotaNotFoundException($notaId);
        }
        /** @var Reserva $reserva */
        $reserva = $this->reservaRepo->findById($nota->getReservaId());
        if(!$reserva) {
            throw new ReservaNotFoundException($reserva->getId());
        }

        if($reserva->getIdProfesional() != $usuario->id) {
            throw new ForbiddenException("No tienes permisos sobre esta nota");
        }

        return $nota;
    }
}