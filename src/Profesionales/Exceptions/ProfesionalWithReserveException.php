<?php
namespace App\Exceptions\Profesionales;
use App\Exceptions\AppException;

class ProfesionalWithReserveException extends AppException {
    protected int $statusCode = 409;
}