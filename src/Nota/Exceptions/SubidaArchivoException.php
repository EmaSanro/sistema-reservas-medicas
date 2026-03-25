<?php
namespace App\Exceptions\ArchivoNota;
use App\Exceptions\AppException;

class SubidaArchivoException extends AppException {
    protected int $statusCode = 400;
}