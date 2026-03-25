<?php
namespace App\Exceptions\Reservas;

use App\Exceptions\AppException;

class ReservaCompletedException extends AppException {
    protected int $statusCode = 409;
}