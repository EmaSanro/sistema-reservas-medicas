<?php
namespace App\Pacientes\Service;

use App\Auth\Exceptions\ForbiddenException;
use App\Auth\Exceptions\UserAlreadyExistsException;
use App\Auth\Model\Roles;
use App\Auth\Repository\AuthRepository;
use App\Auth\Service\RateLimiter;
use App\Pacientes\DTOs\Request\ActualizarPacienteRequest;
use App\Pacientes\DTOs\Request\CrearPacienteRequest;
use App\Pacientes\DTOs\Response\RespuestaPaciente;
use App\Pacientes\Exceptions\PacienteNotFoundException;
use App\Pacientes\Exceptions\PacienteWithReserveException;
use App\Pacientes\Mapper\PacienteMapper;
use App\Pacientes\Repository\PacientesRepository;
use App\Reservas\Repository\ReservasRepository;
use App\Shared\Exceptions\DuplicatedEntryException;

class PacientesService {

    public function __construct(
        private PacientesRepository $repo,
        private ReservasRepository $reservasRepo,
        private AuthRepository $authRepo,
        private RateLimiter $rateLimiter){ }

    public function listar(array $filtros = [], int $page = 1, int $limit = 10): array {
        $paginated = $this->repo->listar($filtros, $page, $limit);
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

    public function registrarPaciente(CrearPacienteRequest $request, string $ip): RespuestaPaciente {
        $this->rateLimiter->checkRegistration($ip);

        // Se registra el intento antes de resolverlo: el abuso a frenar es el
        // sondeo de emails, que se hace justamente provocando el 409.
        $this->rateLimiter->recordRegistrationAttempt($ip);

        $paciente = PacienteMapper::fromRequestCrear($request);

        $this->verificarContactoDisponible($paciente->getEmail(), $paciente->getTelefono());

        $passwordHash = password_hash($request->getPassword(), PASSWORD_BCRYPT);

        try {
            $pacienteCreado = $this->repo->registrarPaciente($paciente, $passwordHash);
        } catch (DuplicatedEntryException $e) {
            // Perdimos una carrera: entre el chequeo de arriba y el INSERT otro
            // request tomo el mismo contacto. Ahora el SELECT si lo encuentra,
            // asi que se puede responder 409 con el campo exacto en vez de 500.
            $this->verificarContactoDisponible($paciente->getEmail(), $paciente->getTelefono());
            throw $e;
        }

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

        $this->verificarContactoDisponible(
            $pacienteExistente->getEmail(),
            $pacienteExistente->getTelefono(),
            $id
        );

        try {
            $pacienteActualizado = $this->repo->actualizarPaciente($id, $pacienteExistente);
        } catch (DuplicatedEntryException $e) {
            $this->verificarContactoDisponible(
                $pacienteExistente->getEmail(),
                $pacienteExistente->getTelefono(),
                $id
            );
            throw $e;
        }

        return PacienteMapper::toResponse($pacienteActualizado);
    }

    public function darDeBajaPaciente(int $id, string $motivo): void {
        if($this->reservasRepo->tieneFuturasReservasPaciente($id)) {
            throw new PacienteWithReserveException("No se puede dar de baja un paciente con futuras reservas!");
        }
        $this->repo->darDeBajaPaciente($id, $motivo);
    }

    /**
     * @param int|null $excluirId Id del propio usuario al actualizar, para que
     *                            reenviar sus datos actuales no cuente como duplicado.
     */
    private function verificarContactoDisponible(?string $email, ?string $telefono, ?int $excluirId = null): void {
        if ($email !== null && $this->authRepo->emailEnUso($email, $excluirId)) {
            throw new UserAlreadyExistsException("email", $email);
        }
        if ($telefono !== null && $this->authRepo->telefonoEnUso($telefono, $excluirId)) {
            throw new UserAlreadyExistsException("telefono", $telefono);
        }
    }
}