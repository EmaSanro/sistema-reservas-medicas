<?php
namespace App\Exceptions\Reservas;

use App\Exceptions\AppException;

class ReservaAlreadyExistsException extends AppException {
    protected int $statusCode = 409;
}