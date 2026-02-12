<?php
namespace App\Service;

use App\Exceptions\Auth\ForbiddenException;
use App\Exceptions\Nota\NotaNotFoundException;
use App\Exceptions\Reservas\ReservaNotFoundException;
use App\Model\DTOs\CrearNotaDTO;
use App\Model\Roles;
use App\Repository\ArchivoNotaRepository;
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
        if($usuario->rol != Roles::PROFESIONAL) {
            throw new ForbiddenException("No tienes permisos para ver notas ajenas");
        }

        $nota = $this->repo->obtenerNotaPorId($id);

        if(!$nota) {
            throw new NotaNotFoundException("No se encontro la nota");
        }
        return $nota->toDTO();
    }
}