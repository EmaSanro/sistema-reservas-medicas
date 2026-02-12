<?php
namespace App\Exceptions\ArchivoNota;

use App\Exceptions\AppException;

class TamanioArchivoException extends AppException {
    protected int $statusCode = 413;
}