<?php
namespace App\Exceptions\ArchivoNota;

use App\Exceptions\AppException;

class TipoArchivoInvalidoException extends AppException {
    protected int $statusCode = 415;
}