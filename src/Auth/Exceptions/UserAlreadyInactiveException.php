<?php
namespace App\Exceptions\Auth;

use App\Shared\Exceptions\ConflictException;

class UserAlreadyInactiveException extends ConflictException {
    public function __construct(string $message = "El usuario ya se encuentra inactivo")
    {
        parent::__construct($message);
    }
}