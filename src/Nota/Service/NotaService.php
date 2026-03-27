<?php
namespace App\Service;

use App\Exceptions\Auth\ForbiddenException;
use App\Exceptions\Nota\NotaNotFoundException;
use App\Exceptions\Reservas\ReservaNotFoundException;
use App\Model\DTOs\ActualizarNotaDTO;
use App\Model\DTOs\CrearNotaDTO;
use App\Repository\NotaRepository;
use App\Repository\ReservasRepository;

class NotaService {
    
    public function __construct(
        private NotaRepository $repo, 
        private ReservasRepository $reservaRepo,
        private ArchivoNotaService $archivoService
        ) {}

    public function crearNota(CrearNotaDTO $nota, $archivos, $usuario) {
        $reserva = $this->reservaRepo->obtenerReserva($nota->getReservaId());
        if(!$reserva) {
            throw new ReservaNotFoundException("No existe la reserva!");
        }
        if($usuario->id != $reserva->getIdProfesional()) {
            throw new ForbiddenException("No tienes permisos de crear notas en una reserva ajena!");
        }

        $nota = $this->repo->guardarNota($nota);
        $adjuntosGuardados = [];
        if(!empty($archivos)) {
            foreach($archivos as $archivo) {
                if($archivo["error"] === UPLOAD_ERR_OK) {
                    $adjunto = $this->archivoService->guardarArchivo($nota->getId(), $archivo);
                    $adjuntosGuardados[] = $adjunto;
                }
            }
        }
        $nota->setAdjuntos($adjuntosGuardados);

        return $nota->toDTO();
    }

    public function obtenerNotaPorId(int $id, $usuario) {
        $nota = $this->validarPermisoNota($id, $usuario);
        $archivos = $this->archivoService->obtenerPorNotaId($id);
        $nota->setAdjuntos($archivos);
        return $nota->toDTO();
    }

    public function actualizarNota(int $id, ActualizarNotaDTO $input, $usuario, $archivos) {
        $this->validarPermisoNota($id, $usuario);

        $notaActualizada = $this->repo->actualizarNota($id, $input);

        if(!empty($archivos)) {
            foreach($archivos as $archivo) {
                if($archivo["error"] == UPLOAD_ERR_OK) {
                    $this->archivoService->guardarArchivo($id, $archivo);
                }
            }
        }

        $archivosAdjuntos = $this->archivoService->obtenerPorNotaId($id);
        $notaActualizada->setAdjuntos($archivosAdjuntos);

        return $notaActualizada->toDTO();
    }

    private function validarPermisoNota(int $notaId, $usuario) {
        $nota = $this->repo->obtenerNotaPorId($notaId);
        if(!$nota) {
            throw new NotaNotFoundException("No existe la nota");
        }

        $reserva = $this->reservaRepo->obtenerReserva($nota->getReservaId());
        if(!$reserva) {
            throw new ReservaNotFoundException("La reserva no existe");
        }

        if($reserva->getIdProfesional() != $usuario->id) {
            throw new ForbiddenException("No tienes permisos sobre esta nota");
        }

        return $nota;
    }
}