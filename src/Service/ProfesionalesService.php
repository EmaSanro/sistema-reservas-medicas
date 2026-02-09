<?php
namespace App\Service;

use App\Exceptions\Auth\ForbiddenException;
use App\Exceptions\InvalidFilterException;
use App\Exceptions\Profesionales\ProfesionalNotFoundException;
use App\Exceptions\Profesionales\ProfesionalWithReserveException;
use App\Exceptions\UserAlreadyExistsException;
use App\Exceptions\ValidationException;
use App\Model\DTOs\RespuestaProfesionalDTO;
use App\Model\Roles;
use App\Repository\ProfesionalesRepository;
use App\Repository\ReservasRepository;

class ProfesionalesService {

    public function __construct(private ProfesionalesRepository $repo, private ReservasRepository $reservaRepo) { }

    public function obtenerTodos(): array {
        $profesionales = $this->repo->obtenerTodos();

        return array_map(fn($profesional) => $profesional->toDTO(), $profesionales ?? []);
    }

    public function obtenerPorId($id): RespuestaProfesionalDTO {
        $profesional = $this->repo->obtenerPorId($id);
        if(!$profesional) {
            throw new ProfesionalNotFoundException("No se encontro un profesional con ese id");
        }
        return $profesional->toDTO();
    }

    public function obtenerPor($filtro, $valor): array {
        $columnasPermitidas = ["nombre", "apellido", "profesion", "email", "telefono", "consultorio"];
            
        if(!in_array($filtro, $columnasPermitidas)) {
            throw new InvalidFilterException("El filtro ingresado no es valido para la busqueda(nombre, apellido, profesion, email, telefono, consultorio)");
        }

        $profs = match($filtro) {
            'profesion' => $this->repo->obtenerPorProfesion($valor),
            'consultorio' => $this->repo->obtenerProfesionalPorUbicacion($valor),
            default => $this->repo->buscarPor($filtro, $valor)
        };

        return array_map(fn($prof) => $prof->toDTO(), $profs ?? []);
    }

    public function registrarProfesional($dto): RespuestaProfesionalDTO {
        $coincidencia = $this->repo->buscarCoincidencia($dto);
        if($coincidencia) {
            throw new UserAlreadyExistsException("Asegurate de que no haya ningun usuario con ese email y/o telefono ya registrado");
        }

        $passwordHash = password_hash($dto->getPassword(), PASSWORD_BCRYPT);

        $prof = $this->repo->registrarProfesional($dto, $passwordHash);

        return $prof->toDTO();
    }

    public function actualizarProfesional($id, $dto, $usuario): RespuestaProfesionalDTO|null {
        if(!$this->repo->obtenerPorId($id)) {
            throw new ProfesionalNotFoundException("No se encontro un profesional con ese id");
        }
        if($id != $usuario->id && $usuario->rol != Roles::ADMIN) {
           throw new ForbiddenException("No tienes permisos para actualizar un perfil que no sea el tuyo!");
        }

        $coincidencia = $this->repo->buscarCoincidencia($dto);
        if($coincidencia && $coincidencia["id"] != $id) {
            throw new UserAlreadyExistsException("Ya hay un usuario con ese email/telefono");
        }
        $passwordHash = null;
        if($dto->getPassword()) {
            $passwordHash = password_hash($dto->getPassword(), PASSWORD_BCRYPT);
        }
        $profActualizado = $this->repo->actualizarProfesional($id, $dto, $passwordHash);
        return $profActualizado->toDTO();
    }

    public function darDeBajaProfesional($id, $motivo) {
        if(strlen($motivo) > 255) {
            throw new ValidationException("El motivo no puede superar los 255 caracteres");
        }
        if($this->reservaRepo->tieneFuturasReservasProfesional($id)) {
            throw new ProfesionalWithReserveException("El profesional tiene futuras reservas");
        }
        $this->repo->darDeBajaProfesional($id, $motivo);
    }
}