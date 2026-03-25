<?php
namespace App\Exceptions\Pacientes;

use App\Exceptions\AppException;

class PacienteWithReserveException extends AppException {
    protected int $statusCode = 409;
}