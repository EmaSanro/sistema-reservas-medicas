<?php
namespace App\Exceptions\Profesionales;

use App\Shared\Exceptions\ConflictException;

class ProfesionalWithReserveException extends ConflictException {
    public function __construct(string $message = "El profesional tiene reservas asociadas y no puede ser eliminado")
    {
        parent::__construct($message);
    }
}