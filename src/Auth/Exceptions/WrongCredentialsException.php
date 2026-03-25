<?php
namespace App\Exceptions\Auth;

use App\Exceptions\AppException;

class WrongCredentialsException extends AppException {
    protected int $statusCode = 401;
     
}