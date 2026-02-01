<?php

namespace App\Model;

use App\Model\DTOs\RespuestaProfesionalDTO;
use App\Model\Roles;
class Profesional extends Usuario {
    private string $profesion;

    public function __construct(int $id, string $nombre, string $apellido, string $profesion, string|null $email, string|null $telefono, string $password) {
        parent::__construct($id, $nombre, $apellido, Roles::PROFESIONAL, $email, $telefono, $password);
        $this->profesion = $profesion;
    }
    public function getProfesion() {
        return $this->profesion;
    }

    public function toDTO() {
        return new RespuestaProfesionalDTO(
            parent::getId(),
            parent::getNombre() . " " . parent::getApellido(),
            $this->profesion,
            parent::getEmail(),
            parent::getTelefono(),
        );
    }
}