<?php

namespace App\Auth\Exceptions;

use App\Shared\Exceptions\AppException;

/**
 * Las credenciales son validas pero la cuenta esta dada de baja.
 *
 * Se responde 403 y no 401 porque no es un problema de credenciales sino
 * del estado de la cuenta. No confundir con UserAlreadyInactiveException,
 * que es 409 y aplica al intentar dar de baja a alguien ya inactivo.
 */
class UsuarioInactivoException extends AppException
{
    public function __construct(string $message = "Este usuario se encuentra dado de baja")
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return 403;
    }

    public function getSafeMessage(): string
    {
        return $this->message;
    }
}
