<?php
namespace App\Exceptions\Pacientes;

use App\Shared\Exceptions\NotFoundException;

class PacienteNotFoundException extends NotFoundException {
    public function __construct(mixed $identificador)
    {
        parent::__construct("Paciente", $identificador);
    }
}