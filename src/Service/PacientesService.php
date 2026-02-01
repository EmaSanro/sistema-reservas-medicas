<?php
namespace App\Service;

use App\Model\DTOs\RespuestaPacienteDTO;
use App\Model\Roles;
use App\Repository\PacientesRepository;

class PacientesService {

    public function __construct(private PacientesRepository $repo){ }

    public function obtenerTodos() {
        $pacientes = $this->repo->obtenerTodos();
        if($pacientes) {
            $pacientesDTO = array_map(
                fn($paciente) => $paciente->toDTO(),
                $pacientes
            );
            return $pacientesDTO;
        }
        return null;
    }

    public function obtenerPorId($id) {
        $paciente = $this->repo->obtenerPorId($id);
        if($paciente) {
            return $paciente->toDTO();
        }
        return null;
    }

    public function buscarPor($filtro, $valor) {
        $filtrosPermitidos = ["nombre", "apellido", "email", "telefono"];
        if(!in_array($filtro, $filtrosPermitidos)) {
            throw new \Exception("El filtro no esta entre los permitidos(nombre, apellido, email, telefono)");
        }

        $pacientesFiltrados = $this->repo->buscarPor($filtro, $valor);
        if($pacientesFiltrados) {
            $pacientesDTO = array_map(
                fn($paciente) => $paciente->toDTO(),
                $pacientesFiltrados 
            );
            return $pacientesDTO;
        }
        return null;
    }

    public function registrarPaciente($dto) {
        $coincidencia = $this->repo->buscarCoincidencia($dto);
        if($coincidencia) {
            throw new \Exception("Ya hay un usuario con ese telefono/email");
        }
        $passwordHash = password_hash($dto->getPassword(), PASSWORD_BCRYPT);
        $pac = $this->repo->registrarPaciente($dto, $passwordHash);
        return $pac->toDTO();
    }

    public function actualizarPaciente($id, $dto, $usuario) {
        if($id != $usuario->id && $usuario->rol != Roles::ADMIN) {
            throw new \Exception("No tienes permiso para actualizar datos de otra persona!");
        }
        
        $paciente = $this->repo->buscarCoincidencia($dto);
        if($paciente && $paciente->getId() != $id) {
            throw new \Exception("Ya hay un usuario con ese email/telefono");
        }
        $passwordHash = password_hash($dto->getPassword(), PASSWORD_BCRYPT);
        $pac = $this->repo->actualizarPaciente($id, $dto, $passwordHash);
        if($pac) {
            return $pac->toDTO();
        }
        return null;
    }

    public function eliminarPaciente($id) {
        return $this->repo->eliminarPaciente($id);
    }
}