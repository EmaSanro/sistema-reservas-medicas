<?php
namespace App\Service;

use App\Exceptions\Auth\ForbiddenException;
use App\Exceptions\Consultorios\ConsultorioAlreadyExistsException;
use App\Exceptions\Consultorios\ConsultorioNotFoundException;
use App\Model\DTOs\ConsultorioDTO;
use App\Model\DTOs\RespuestaConsultorioDTO;
use App\Model\Roles;
use App\Repository\ConsultorioRepository;

class ConsultorioService {

    public function __construct(private ConsultorioRepository $repo) { }

    public function obtenerConsultorios(): array {
        $consultorios = $this->repo->obtenerConsultorios();

        return array_map(fn($consultorio) => $consultorio->toDTO(), $consultorios ?? []);
    }

    public function obtenerConsultorio($id): RespuestaConsultorioDTO {
        $consultorio = $this->repo->obtenerConsultorio($id);
        if(!$consultorio) {
            throw new ConsultorioNotFoundException("No se ha encontrado un consultorio con ese id");
        }
        return $consultorio->toDTO();
    }

    public function crearConsultorio($dto, $usuario): RespuestaConsultorioDTO {

        if($this->repo->buscarPorCiudadDireccion($dto->getCiudad(), $dto->getDireccion())) {
            throw new ConsultorioAlreadyExistsException("Ya existe un consultorio en esa ciudad y direccion");
        }
        
        $idProfesional = $usuario->rol == Roles::PROFESIONAL ? $usuario->id : null;

        $consultorio = $this->repo->crearConsultorio($dto, $idProfesional);

        return $consultorio->toDTO();
    }

    public function actualizarConsultorio(ConsultorioDTO $dto, int $id, $usuario): RespuestaConsultorioDTO|null {
        if(!$this->repo->obtenerConsultorio($id)) {
            throw new ConsultorioNotFoundException("No existe un consultorio con ese id");
        }

        $consultorio = $this->repo->esAtendidoPor($id);
        if($usuario->rol != Roles::ADMIN && $consultorio["idprofesional"] != $usuario->id) {
            throw new ForbiddenException("No tienes permisos para actualizar un consultorio que no es tuyo!");
        }
        
        $coincidencia = $this->repo->buscarPorCiudadDireccion($dto->getCiudad(), $dto->getDireccion());
        if($coincidencia && $coincidencia["id"] != $id) {
            throw new ConsultorioAlreadyExistsException("No puedes usar la misma direccion y ciudad que un consultorio que ya existe");
        }

        $consultorio = $this->repo->actualizarConsultorio($dto, $id, $consultorio["idprofesional"]);

        return $consultorio?->toDTO() ?? null;
    }

    public function borrarConsultorio($id, $usuario): void {
        if($usuario->rol == Roles::PROFESIONAL && $this->repo->esAtendidoPor($id) != $usuario->id) {
            throw new ForbiddenException("No puedes eliminar un consultorio ajeno!");
        }
        $eliminado = $this->repo->borrarConsultorio($id);
        if(!$eliminado) {
            throw new ConsultorioNotFoundException("No se encontro un consultorio para eliminar");
        }
    }
}