<?php

namespace App\Exceptions;

class UserAlreadyInactiveException extends AppException {
    protected int $statusCode = 409;
}