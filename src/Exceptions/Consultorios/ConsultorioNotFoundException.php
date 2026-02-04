<?php
namespace App\Exceptions\Consultorios;

use App\Exceptions\AppException;

class ConsultorioNotFoundException extends AppException {
    protected int $statusCode = 404;
}