<?php
namespace App\Exceptions\Pacientes;

use App\Exceptions\AppException;

class PacienteNotFoundException extends AppException {
    protected int $statusCode = 404;
}