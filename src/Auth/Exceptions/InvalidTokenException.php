<?php
namespace App\Auth\Exceptions;

use App\Shared\Exceptions\AppException;

class InvalidTokenException extends AppException {

    public function __construct(string $message = "Token de autenticación inválido")
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return 401;
    }

    public function getSafeMessage(): string
    {
        return "$this->message";
    }
}