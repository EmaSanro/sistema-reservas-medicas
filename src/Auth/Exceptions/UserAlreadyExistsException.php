<?php
namespace App\Exceptions\Auth;

use App\Shared\Exceptions\AlreadyExistsException;

class UserAlreadyExistsException extends AlreadyExistsException {
    public function __construct(string $campo, mixed $valor)
    {
        parent::__construct("Usuario", $campo, $valor);
    }
}