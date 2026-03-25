<?php
namespace App\Exceptions\Reservas;

use App\Exceptions\AppException;

class CancelacionTardiaException extends AppException {
    protected int $statusCode = 400;
}