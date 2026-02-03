<?php
namespace App\Exceptions\Profesionales;

use App\Exceptions\AppException;

class ProfesionalNotFoundException extends AppException {
    protected int $statusCode = 404;
}