<?php
namespace App\Reservas\Exceptions;

use App\Shared\Exceptions\AlreadyExistsException;

class ReservaAlreadyExistsException extends AlreadyExistsException {
    public function __construct(string $campo, mixed $valor)
    {
        parent::__construct("Reserva", $campo, $valor);
    }
}