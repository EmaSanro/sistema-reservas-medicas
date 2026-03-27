<?php
namespace App\Auth\Exceptions;

use App\Shared\Exceptions\AppException;

class WrongCredentialsException extends AppException {
    public function __construct($message = "Credenciales incorrectas") {
        parent::__construct($message);
    }

    public function getStatusCode(): int {
        return 401;
    }

    public function getSafeMessage(): string
    {
        return "Las credenciales proporcionadas son incorrectas. Por favor, verifica tu email/telefono y contraseña.";
    }
}