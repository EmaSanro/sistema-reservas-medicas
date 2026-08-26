<?php
namespace App\Reservas\Exceptions;

use App\Shared\Exceptions\ConflictException;

class UsuarioConReservaException extends ConflictException {
    public function __construct(string $safeMessage)
    {
        parent::__construct($safeMessage);
    }
}