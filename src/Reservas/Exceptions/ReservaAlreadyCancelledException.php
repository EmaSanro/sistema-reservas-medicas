<?php
namespace App\Exceptions\Reservas;

use App\Shared\Exceptions\ConflictException;

class ReservaAlreadyCancelledException extends ConflictException {
    public function __construct(string $message = "La reserva ya se encuentra cancelada")
    {
        parent::__construct($message);
    }
}