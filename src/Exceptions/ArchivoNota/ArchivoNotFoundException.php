<?php
namespace App\Exceptions\ArchivoNota;

use App\Exceptions\AppException;

class ArchivoNotFoundException extends AppException {
    protected int $statusCode = 404;
}