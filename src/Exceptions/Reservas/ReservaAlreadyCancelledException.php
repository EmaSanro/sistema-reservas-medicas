<?php
namespace App\Exceptions\Reservas;

use App\Exceptions\AppException;

class ReservaAlreadyCancelledException extends AppException {
    protected int $statusCode = 409;
}