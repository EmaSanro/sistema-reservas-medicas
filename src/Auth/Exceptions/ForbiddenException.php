<?php
namespace App\Auth\Exceptions;

use App\Shared\Exceptions\AppException;

class ForbiddenException extends AppException {
    public function __construct(string $message = "Acceso denegado")
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return 403;
    }

    public function getSafeMessage(): string
    {
        return "Acceso denegado";
    }
}