<?php
namespace App\Exceptions\Reservas;

use App\Exceptions\AppException;

class ReservaAlreadyExists extends AppException {
    protected int $statusCode = 409;
}