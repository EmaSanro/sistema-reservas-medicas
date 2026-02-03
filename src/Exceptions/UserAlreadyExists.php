<?php
namespace App\Exceptions;

class UserAlreadyExists extends AppException {
    protected int $statusCode = 409;
}