<?php

namespace App\Exceptions;

class ValidationException extends AppException {
    protected int $statusCode = 400;
}