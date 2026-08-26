<?php

namespace App\Auth\Exceptions;

use App\Shared\Exceptions\AppException;

/**
 * Se supero el limite de intentos permitidos.
 *
 * El mensaje es deliberadamente generico: no debe revelar si el bloqueo se
 * disparo por el identificador o por la IP, ni si la cuenta existe.
 */
class TooManyAttemptsException extends AppException
{
    public function __construct(private readonly int $retryAfterSeconds)
    {
        parent::__construct("Demasiados intentos. Probá de nuevo más tarde.");
    }

    public function getStatusCode(): int
    {
        return 429;
    }

    public function getSafeMessage(): string
    {
        return $this->message;
    }

    public function getHeaders(): array
    {
        return ["Retry-After" => (string) $this->retryAfterSeconds];
    }
}
