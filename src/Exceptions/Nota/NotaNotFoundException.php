<?php
namespace App\Exceptions\Nota;

use App\Exceptions\AppException;

class NotaNotFoundException extends AppException{
    protected int $statusCode = 404;
}