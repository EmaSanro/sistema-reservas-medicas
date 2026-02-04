<?php
namespace App\Exceptions\Reservas;

use App\Exceptions\AppException;

class ReservaNotFoundException extends AppException {
    protected int $statusCode = 404;
}