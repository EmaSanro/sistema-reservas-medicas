<?php
namespace App\Service;

use App\Model\DTOs\RespuestaConsultorioDTO;
use App\Repository\ConsultorioRepository;

class ConsultorioService {

    public function __construct(private ConsultorioRepository $repo) { }

    public function obtenerConsultorios() {
        $consultorios = $this->repo->obtenerConsultorios();
        if($consultorios) {
            $consultoriosDTO = array_map(
                fn($consultorio) => RespuestaConsultorioDTO::fromArray($consultorio),
                $consultorios
            );
            return $consultoriosDTO;
        }
        return null;
    }

    public function obtenerConsultorio($id) {
        $consultorio = $this->repo->obtenerConsultorio($id);
        if($consultorio) {
            $consultorioDTO = RespuestaConsultorioDTO::fromArray($consultorio);
            return $consultorioDTO;
        }
        return null;
    }

    public function crearConsultorio($dto) {
        if($this->repo->buscarPorCiudadDireccion($dto->getCiudad(), $dto->getDireccion())) {
            throw new \Exception("Ya existe un consultorio en esa ciudad y direccion");
        }

        $consultorio = $this->repo->crearConsultorio($dto);
        if($consultorio) {
            $consultorioDTO = RespuestaConsultorioDTO::fromArray($consultorio);
            return $consultorioDTO;
        }
        return null;
    }

    public function actualizarConsultorio($dto, $id) {
        if(!$this->repo->obtenerConsultorio($id)) {
            throw new \Exception("No existe un consultorio con ese id");
        }
        $coincidencia = $this->repo->buscarPorCiudadDireccion($dto->getCiudad(), $dto->getDireccion());
        if($coincidencia && $coincidencia["id"] = $id) {
            throw new \Exception("No puedes usar la misma direccion y ciudad que un consultorio que ya existe");
        }

        $consultorio = $this->repo->actualizarConsultorio($dto, $id); 
        if($consultorio) {
            return RespuestaConsultorioDTO::fromArray($consultorio);
        }
        return null;
    }

    public function borrarConsultorio($id) {
        if(!$this->repo->obtenerConsultorio($id)) {
            throw new \Exception("No existe un consultorio para eliminar con ese id");
        }

        return $this->repo->borrarConsultorio($id);
    }
}