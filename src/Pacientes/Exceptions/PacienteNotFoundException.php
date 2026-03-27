<?php
namespace App\Pacientes\Exceptions;

use App\Shared\Exceptions\NotFoundException;

class PacienteNotFoundException extends NotFoundException {
    public function __construct(mixed $identificador)
    {
        parent::__construct("Paciente", $identificador);
    }
}