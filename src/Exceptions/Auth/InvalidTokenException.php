<?php
namespace App\Exceptions\Auth;

use App\Exceptions\AppException;

class InvalidTokenException extends AppException {
    protected int $statusCode = 401; 
}