<?php
namespace App\Exceptions\Reservas;

use App\Exceptions\AppException;

class ReservaAlreadyCancelled extends AppException {
    protected int $statusCode = 409;
}