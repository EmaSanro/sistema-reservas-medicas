<?php
namespace App\Exceptions\Consultorios;

use App\Exceptions\AppException;

class ConsultorioAlreadyExistsException extends AppException {
    protected int $statusCode = 409;
}