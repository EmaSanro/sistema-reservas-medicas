<?php
use App\Model\Roles;
class Profesional extends Usuario {
    private string $profesion;

    public function __construct(int $id, string $nombre, string $apellido, string $profesion, string $email, string $telefono, string $password) {
        parent::__construct($id, $nombre, $apellido, Roles::PROFESIONAL, $email, $telefono, $password);
        $this->profesion = $profesion;
    }
    public function getProfesion() {
        return $this->profesion;
    }
}