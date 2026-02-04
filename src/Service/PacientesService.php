<?php
namespace App\Service;

use App\Exceptions\Auth\ForbiddenException;
use App\Exceptions\InvalidFilterException;
use App\Exceptions\Pacientes\PacienteNotFoundException;
use App\Exceptions\UserAlreadyExistsException;
use App\Model\DTOs\RespuestaPacienteDTO;
use App\Model\Roles;
use App\Repository\PacientesRepository;

class PacientesService {

    public function __construct(private PacientesRepository $repo){ }

    public function obtenerTodos(): array {
        $pacientes = $this->repo->obtenerTodos();

        return array_map(fn($paciente) => $paciente->toDTO(), $pacientes ?? []);
    }

    public function obtenerPorId($id): RespuestaPacienteDTO {
        $paciente = $this->repo->obtenerPorId($id);
        if(!$paciente) {
            throw new PacienteNotFoundException("No se ha encontrado un paciente con ese id");
        }
        return $paciente->toDTO();
    }

    public function buscarPor($filtro, $valor): array {
        $filtrosPermitidos = ["nombre", "apellido", "email", "telefono"];
        if(!in_array($filtro, $filtrosPermitidos)) {
            throw new InvalidFilterException("El filtro no esta entre los permitidos(nombre, apellido, email, telefono)");
        }

        $pacientesFiltrados = $this->repo->buscarPor($filtro, $valor);

        return array_map(fn($paciente) => $paciente->toDTO(), $pacientesFiltrados ?? []);
    }

    public function registrarPaciente($dto): RespuestaPacienteDTO {
        $coincidencia = $this->repo->buscarCoincidencia($dto);
        if($coincidencia) {
            throw new UserAlreadyExistsException("Ya hay un usuario con ese telefono/email");
        }

        $passwordHash = password_hash($dto->getPassword(), PASSWORD_BCRYPT);
        
        $pac = $this->repo->registrarPaciente($dto, $passwordHash);

        return $pac->toDTO();
    }

    public function actualizarPaciente($id, $dto, $usuario): RespuestaPacienteDTO|null {
        if($id != $usuario->id && $usuario->rol != Roles::ADMIN) {
            throw new ForbiddenException("No tienes permiso para actualizar datos de otra persona!");
        }
        
        $paciente = $this->repo->buscarCoincidencia($dto);
        if($paciente && $paciente->getId() != $id) {
            throw new UserAlreadyExistsException("Ya hay un usuario con ese email/telefono");
        }
        $passwordHash = null;
        if($dto->getPassword()) {
            $passwordHash = password_hash($dto->getPassword(), PASSWORD_BCRYPT);
        }

        $pac = $this->repo->actualizarPaciente($id, $dto, $passwordHash);

        return $pac?->toDTO() ?: null;
    }

    public function eliminarPaciente($id): void {
        $eliminado = $this->repo->eliminarPaciente($id);
        if(!$eliminado) {
            throw new PacienteNotFoundException("No se encontro un paciente para eliminar");
        }
    }
}