<?php
namespace App\Pacientes\Service;

use App\Auth\Exceptions\ForbiddenException;
use App\Auth\Exceptions\UserAlreadyExistsException;
use App\Auth\Model\Roles;
use App\Auth\Repository\AuthRepository;
use App\Pacientes\DTOs\Request\ActualizarPacienteRequest;
use App\Pacientes\DTOs\Request\CrearPacienteRequest;
use App\Pacientes\DTOs\Response\RespuestaPaciente;
use App\Pacientes\Exceptions\PacienteNotFoundException;
use App\Pacientes\Exceptions\PacienteWithReserveException;
use App\Pacientes\Mapper\PacienteMapper;
use App\Pacientes\Repository\PacientesRepository;
use App\Reservas\Repository\ReservasRepository;

class PacientesService {

    public function __construct(
        private PacientesRepository $repo, 
        private ReservasRepository $reservasRepo,
        private AuthRepository $authRepo){ }

    public function obtenerTodos(int $page = 1, int $limit = 10): array {
        $paginated = $this->repo->obtenerTodos($page, $limit);
        $paginated['data'] = array_map(fn($paciente) => PacienteMapper::toResponse($paciente), $paginated['data']);
        return $paginated;
    }

    public function obtenerPorId(int $id): RespuestaPaciente {
        $paciente = $this->repo->obtenerPorId($id);
        if(!$paciente) {
            throw new PacienteNotFoundException($id);
        }
        return PacienteMapper::toResponse($paciente);
    }

    public function buscarPor(string $filtro, string $valor, int $page = 1, int $limit = 10): array {
        $paginated = $this->repo->buscarPor($filtro, $valor, $page, $limit);
        $paginated['data'] = array_map(fn($paciente) => PacienteMapper::toResponse($paciente), $paginated['data']);
        return $paginated;
    }

    public function registrarPaciente(CrearPacienteRequest $request): RespuestaPaciente {
        $paciente = PacienteMapper::fromRequestCrear($request);
        $coincidencia = $this->authRepo->buscarCoincidencia($paciente);
        if($coincidencia) {
            if($coincidencia->getEmail() === $paciente->getEmail()) {
                throw new UserAlreadyExistsException("email", $paciente->getEmail());
            }
            throw new UserAlreadyExistsException("telefono", $paciente->getTelefono());
        }

        $passwordHash = password_hash($request->getPassword(), PASSWORD_BCRYPT);
        
        $pacienteCreado = $this->repo->registrarPaciente($paciente, $passwordHash);

        return PacienteMapper::toResponse($pacienteCreado);
    }

    public function actualizarPaciente(int $id, ActualizarPacienteRequest $request, mixed $usuario): RespuestaPaciente {
        if($id != $usuario->id && $usuario->rol != Roles::ADMIN) {
            throw new ForbiddenException("No tienes permiso para actualizar datos de otra persona!");
        }

        $pacienteExistente = $this->repo->obtenerPorId($id);
        if(!$pacienteExistente) {
            throw new PacienteNotFoundException($id);
        }

        PacienteMapper::fromRequestActualizar($pacienteExistente, $request);

        $pacienteDuplicado = $this->authRepo->buscarCoincidencia($pacienteExistente);

        if($pacienteDuplicado && $pacienteDuplicado->getId() != $id) {
            if($pacienteDuplicado->getEmail() === $pacienteExistente->getEmail()) {
                throw new UserAlreadyExistsException("email", $pacienteExistente->getEmail());
            }
            throw new UserAlreadyExistsException("telefono", $pacienteExistente->getTelefono());
        }

        $pacienteActualizado = $this->repo->actualizarPaciente($id, $pacienteExistente);

        return PacienteMapper::toResponse($pacienteActualizado);
    }

    public function darDeBajaPaciente(int $id, string $motivo): void {
        if($this->reservasRepo->tieneFuturasReservasPaciente($id)) {
            throw new PacienteWithReserveException("No se puede dar de baja un paciente con futuras reservas!");
        }
        $this->repo->darDeBajaPaciente($id, $motivo);
    }
}