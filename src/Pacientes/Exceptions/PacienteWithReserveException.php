<?php
namespace App\Pacientes\Exceptions;

use App\Shared\Exceptions\ConflictException;

class PacienteWithReserveException extends ConflictException {
    public function __construct(string $message = "El paciente tiene reservas asociadas")
    {
        parent::__construct($message);
    }
}