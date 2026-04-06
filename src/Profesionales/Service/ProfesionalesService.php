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

class ProfesionalesService {

    public function __construct(
        private ProfesionalesRepository $repo, 
        private ReservasRepository $reservaRepo,
        private AuthRepository $authRepository) { }

    public function obtenerTodos(): array {
        $profesionales = $this->repo->obtenerTodos();
        $response = array_map(fn($profesional) => ProfesionalMapper::toResponse($profesional), $profesionales);
        return $response;
    }

    public function obtenerPorId(int $id): RespuestaProfesional {
        $profesional = $this->repo->obtenerPorId($id);
        if(!$profesional) {
            throw new ProfesionalNotFoundException($id);
        }
        return ProfesionalMapper::toResponse($profesional);
    }

    public function obtenerPor(string $filtro, string $valor): array {
        $profesionales = match($filtro) {
            'profesion' => $this->repo->obtenerPorProfesion($valor),
            'consultorio' => $this->repo->obtenerProfesionalPorUbicacion($valor),
            default => $this->repo->buscarPor($filtro, $valor)
        };

        $response = array_map(fn($prof) => ProfesionalMapper::toResponse($prof), $profesionales);

        return $response;
    }

    public function registrarProfesional(CrearProfesionalRequest $request): RespuestaProfesional {
        $profesional = ProfesionalMapper::fromRequestCrear($request);

        $profesionalExistente = $this->authRepository->buscarCoincidencia($profesional);
        if($profesionalExistente) {
            if($profesionalExistente->getEmail() === $profesional->getEmail() && $profesionalExistente->getEmail() !== null) {
                throw new UserAlreadyExistsException("email", $profesional->getEmail());
            }
            throw new UserAlreadyExistsException("telefono", $profesional->getTelefono());
        }

        $passwordHash = password_hash($profesional->getPassword(), PASSWORD_BCRYPT);

        $profesionalCreado = $this->repo->registrarProfesional($profesional, $passwordHash);

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

        $coincidencia = $this->authRepository->buscarCoincidencia($profesionalExistente);
        if($coincidencia && $coincidencia->getId() != $id) {
            if($coincidencia->getEmail() === $profesionalExistente->getEmail() && $coincidencia->getEmail() !== null) {
                throw new UserAlreadyExistsException("email", $profesionalExistente->getEmail());
            }
            throw new UserAlreadyExistsException("telefono", $profesionalExistente->getTelefono());
        }
        $profActualizado = $this->repo->actualizarProfesional($id, $profesionalExistente);
        return ProfesionalMapper::toResponse($profActualizado);
    }

    public function darDeBajaProfesional(int $id, string $motivo) {
        if($this->reservaRepo->tieneFuturasReservasProfesional($id)) {
            throw new ProfesionalWithReserveException("El profesional tiene reservas pendientes");
        }
        $this->repo->darDeBajaProfesional($id, $motivo);
    }
}