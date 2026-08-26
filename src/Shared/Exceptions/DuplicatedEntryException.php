<?php

namespace App\Shared\Exceptions;

use Throwable;

/**
 * La base de datos rechazo una escritura por violar una restriccion UNIQUE.
 *
 * La lanza Repository::translateException() traduciendo el error 1062 de
 * MySQL, para que las capas superiores no necesiten conocer codigos del driver.
 *
 * Los servicios la interceptan para devolver un mensaje que identifique el
 * campo en conflicto. Si nadie la intercepta, el ErrorMiddleware responde un
 * 409 generico: menos informativo, pero semanticamente correcto.
 */
class DuplicatedEntryException extends AppException {

    public function __construct(string $message = "Duplicated entry", ?Throwable $previous = null) {
        parent::__construct($message, 0, $previous);
    }

    public function getStatusCode(): int {
        return 409;
    }

    public function getSafeMessage(): string {
        return "Ya existe un registro con estos datos";
    }
}
