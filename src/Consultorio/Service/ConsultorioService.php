<?php
namespace App\Consultorio\Service;

use App\Auth\Exceptions\ForbiddenException;
use App\Auth\Model\Roles;
use App\Consultorio\DTOs\Request\ActualizarConsultorioRequest;
use App\Consultorio\DTOs\Request\CrearConsultorioRequest;
use App\Consultorio\DTOs\Response\RespuestaConsultorio;
use App\Consultorio\Exceptions\ConsultorioAlreadyExistsException;
use App\Consultorio\Exceptions\ConsultorioNotFoundException;
use App\Consultorio\Mapper\ConsultorioMapper;
use App\Consultorio\Model\Consultorio;
use App\Consultorio\Repository\ConsultorioRepository;

class ConsultorioService {

    public function __construct(private ConsultorioRepository $repo) { }

    public function obtenerConsultorios(int $page = 1, int $limit = 10): array {
        $paginated = $this->repo->findPaginated($page, $limit);
        $paginated['data'] = array_map(fn($consultorio) => ConsultorioMapper::toResponse($consultorio), $paginated['data']);

        return $paginated;
    }

    public function obtenerConsultorio(int $id): RespuestaConsultorio {
        $consultorio = $this->repo->findById($id);
        if(!$consultorio) {
            throw new ConsultorioNotFoundException($id);
        }
        return ConsultorioMapper::toResponse($consultorio);
    }

    public function crearConsultorio(CrearConsultorioRequest $request, mixed $usuario): RespuestaConsultorio {
        $consultorio = ConsultorioMapper::fromRequest($request);

        if($this->repo->buscarPorCiudadDireccion($consultorio->getCiudad(), $consultorio->getDireccion())) {
            throw new ConsultorioAlreadyExistsException("direccion", $consultorio->getDireccion());
        }
        
        $idProfesional = $usuario->rol == Roles::PROFESIONAL ? $usuario->id : null;

        $consultorio->setIdprofesional($idProfesional);

        $consultorioCreado = $this->repo->crearConsultorio($consultorio);

        return ConsultorioMapper::toResponse($consultorioCreado);
    }

    public function actualizarConsultorio(ActualizarConsultorioRequest $request, int $id, mixed $usuario): RespuestaConsultorio {
        /** @var Consultorio $consultorioExistente */
        $consultorioExistente = $this->repo->findById($id);
        if(!$consultorioExistente) {
            throw new ConsultorioNotFoundException($id);
        }

        $consultorio = $this->repo->esAtendidoPor($id);
        if($usuario->rol != Roles::ADMIN && $consultorio["idprofesional"] != $usuario->id) {
            throw new ForbiddenException("No tienes permisos para actualizar un consultorio que no es tuyo!");
        }

        if($request->getCiudad() === null) {
            $request->setCiudad($consultorioExistente->getCiudad());
        }
        if($request->getDireccion() === null) {
            $request->setDireccion($consultorioExistente->getDireccion());
        }
        if($request->getHorarioApertura() === null) {
            $request->setHorarioApertura($consultorioExistente->getHorarioApertura());
        }
        if($request->getHorarioCierre() === null) {
            $request->setHorarioCierre($consultorioExistente->getHorarioCierre());
        }

        $consultorio = ConsultorioMapper::fromRequest($request);
        
        /** @var Consultorio $coincidencia */
        $coincidencia = $this->repo->buscarPorCiudadDireccion($request->getCiudad(), $request->getDireccion());
        if($coincidencia && $coincidencia->getId() != $id) {
            throw new ConsultorioAlreadyExistsException("direccion", $request->getDireccion());
        }

        $consultorio = $this->repo->actualizarConsultorio($consultorio, $id);

        return ConsultorioMapper::toResponse($consultorio);
    }

    public function borrarConsultorio(int $id, mixed $usuario): void {
        $consultorio = $this->repo->esAtendidoPor($id);
        if($usuario->rol == Roles::PROFESIONAL && $consultorio["idprofesional"] != $usuario->id) {
            throw new ForbiddenException("No puedes eliminar un consultorio ajeno!");
        }
        $this->repo->borrarConsultorio($id);
    }
}