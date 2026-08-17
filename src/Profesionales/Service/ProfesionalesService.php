<?php
namespace App\Profesionales\Service;

use App\Auth\Exceptions\ForbiddenException;
use App\Auth\Exceptions\UserAlreadyExistsException;
use App\Auth\Model\Roles;
use App\Auth\Repository\AuthRepository;
use App\Profesionales\DTOs\Request\ActualizarProfesionalRequest;
use App\Profesionales\DTOs\Request\CrearProfesionalRequest;
use App\Profesionales\DTOs\Response\RespuestaProfesional;
use App\Profesionales\Exceptions\ProfesionalNotFoundException;
use App\Profesionales\Exceptions\ProfesionalWithReserveException;
use App\Profesionales\Mapper\ProfesionalMapper;
use App\Profesionales\Repository\ProfesionalesRepository;
use App\Reservas\Repository\ReservasRepository;
use App\Shared\Exceptions\DuplicatedEntryException;

class ProfesionalesService {

    public function __construct(
        private ProfesionalesRepository $repo, 
        private ReservasRepository $reservaRepo,
        private AuthRepository $authRepository) { }

    public function listar(array $filtros = [], int $page = 1, int $limit = 10): array {
        $paginated = $this->repo->listar($filtros, $page, $limit);
        $paginated['data'] = array_map(fn($profesional) => ProfesionalMapper::toResponse($profesional), $paginated['data']);
        return $paginated;
    }

    public function obtenerPorId(int $id): RespuestaProfesional {
        $profesional = $this->repo->obtenerPorId($id);
        if(!$profesional) {
            throw new ProfesionalNotFoundException($id);
        }
        return ProfesionalMapper::toResponse($profesional);
    }

    public function registrarProfesional(CrearProfesionalRequest $request): RespuestaProfesional {
        $profesional = ProfesionalMapper::fromRequestCrear($request);

        $this->verificarContactoDisponible($profesional->getEmail(), $profesional->getTelefono());

        $passwordHash = password_hash($request->getPassword(), PASSWORD_BCRYPT);

        try {
            $profesionalCreado = $this->repo->registrarProfesional($profesional, $passwordHash);
        } catch (DuplicatedEntryException $e) {
            // Perdimos una carrera: entre el chequeo de arriba y el INSERT otro
            // request tomo el mismo contacto. Ahora el SELECT si lo encuentra,
            // asi que se puede responder 409 con el campo exacto en vez de 500.
            $this->verificarContactoDisponible($profesional->getEmail(), $profesional->getTelefono());
            throw $e;
        }

        return ProfesionalMapper::toResponse($profesionalCreado);
    }

    public function actualizarProfesional(int $id, ActualizarProfesionalRequest $request, mixed $usuario): RespuestaProfesional {
        $profesionalExistente = $this->repo->obtenerPorId($id);
        if(!$profesionalExistente) {
            throw new ProfesionalNotFoundException($id);
        }
        if($id != $usuario->id && $usuario->rol != Roles::ADMIN) {
           throw new ForbiddenException("No tienes permisos para actualizar un perfil que no sea el tuyo!");
        }
        ProfesionalMapper::fromRequestActualizar($profesionalExistente, $request);

        $this->verificarContactoDisponible(
            $profesionalExistente->getEmail(),
            $profesionalExistente->getTelefono(),
            $id
        );

        try {
            $profActualizado = $this->repo->actualizarProfesional($id, $profesionalExistente);
        } catch (DuplicatedEntryException $e) {
            $this->verificarContactoDisponible(
                $profesionalExistente->getEmail(),
                $profesionalExistente->getTelefono(),
                $id
            );
            throw $e;
        }

        return ProfesionalMapper::toResponse($profActualizado);
    }

    public function darDeBajaProfesional(int $id, string $motivo) {
        if($this->reservaRepo->tieneFuturasReservasProfesional($id)) {
            throw new ProfesionalWithReserveException("El profesional tiene reservas pendientes");
        }
        $this->repo->darDeBajaProfesional($id, $motivo);
    }

    /**
     * @param int|null $excluirId Id del propio usuario al actualizar, para que
     *                            reenviar sus datos actuales no cuente como duplicado.
     */
    private function verificarContactoDisponible(?string $email, ?string $telefono, ?int $excluirId = null): void {
        if ($email !== null && $this->authRepository->emailEnUso($email, $excluirId)) {
            throw new UserAlreadyExistsException("email", $email);
        }
        if ($telefono !== null && $this->authRepository->telefonoEnUso($telefono, $excluirId)) {
            throw new UserAlreadyExistsException("telefono", $telefono);
        }
    }
}