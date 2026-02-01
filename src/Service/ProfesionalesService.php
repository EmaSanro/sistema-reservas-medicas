<?php
namespace App\Service;

use App\Model\Roles;
use App\Repository\ProfesionalesRepository;

class ProfesionalesService {

    public function __construct(private ProfesionalesRepository $repo) { }

    public function obtenerTodos() {
        $profesionales = $this->repo->obtenerTodos();
        if($profesionales) {
            $profesionalesDTO = array_map(
                fn($prof) => $prof->toDTO(),
                $profesionales
            );
            return $profesionalesDTO;
        }
        return null;
    }

    public function obtenerPorId($id) {
        $profesional = $this->repo->obtenerPorId($id);
        if($profesional) {
            return $profesional->toDTO();
        }
        return null;
    }

    public function obtenerPor($filtro, $valor) {
        $columnasPermitidas = ["nombre", "apellido", "profesion", "email", "telefono", "consultorio"];
            
        if(!in_array($filtro, $columnasPermitidas)) {
            throw new \Exception("El filtro ingresado no es valido para la busqueda");
        }

        $profs = match($filtro) {
            'profesion' => $this->repo->obtenerPorProfesion($valor),
            'consultorio' => $this->repo->obtenerProfesionalPorUbicacion($valor),
            default => $this->repo->buscarPor($filtro, $valor)
        };
        if($profs) {
            $profsDTO = array_map(
                fn($prof) => $prof->toDTO(),
                $profs
            );
            return $profsDTO;
        }
        return null;
    }

    public function registrarProfesional($dto) {
        $coincidencia = $this->repo->buscarCoincidencia($dto);
        if($coincidencia) {
            throw new \Exception("Asegurate de que no haya ningun profesional con ese email y/o telefono ya registrado");
        }

        $passwordHash = password_hash($dto->getPassword(), PASSWORD_BCRYPT);

        $prof = $this->repo->registrarProfesional($dto, $passwordHash);
        if($prof) {
            return $prof->toDTO();
        }
        return null;
    }

    public function actualizarProfesional($id, $dto, $usuario) {
        if($id != $usuario->id && $usuario->rol != Roles::ADMIN) {
           throw new \Exception("No tienes permisos para actualizar un perfil que no sea el tuyo!");
        }

        $coincidencia = $this->repo->buscarCoincidencia($dto);
        if($coincidencia && $coincidencia["id"] != $id) {
            throw new \Exception("Ya hay un usuario con ese email/telefono");
        }
        $passwordHash = password_hash($dto->getPassword(), PASSWORD_BCRYPT);
        $profActualizado = $this->repo->actualizarProfesional($id, $dto, $passwordHash);
        if($profActualizado) {
            return $profActualizado->toDTO();
        }
        return null;
    }

    public function eliminarProfesional($id) {
        return $this->repo->eliminarProfesional($id);
    }
}