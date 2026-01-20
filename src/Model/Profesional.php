<?php
use App\Model\Roles;
class Profesional extends Usuario {
    private string $profesion;

    public function __construct(string $nombre, string $apellido, string $profesion, string $email, string $telefono) {
        parent::__construct($nombre, $apellido, Roles::PROFESIONAL, $email, $telefono);
        $this->profesion = $profesion;
    }
    public function getProfesion() {
        return $this->profesion;
    }

    public function setProfesion(string $profesion) {
        $this->profesion = $profesion;
    }
}