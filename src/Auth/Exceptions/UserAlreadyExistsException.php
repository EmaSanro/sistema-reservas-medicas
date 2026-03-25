<?php
namespace App\Exceptions;

class UserAlreadyExistsException extends AppException {
    protected int $statusCode = 409;
}