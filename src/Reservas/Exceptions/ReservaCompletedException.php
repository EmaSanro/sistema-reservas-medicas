<?php
namespace App\Exceptions\Reservas;

use App\Shared\Exceptions\ConflictException;

class ReservaCompletedException extends ConflictException {
    public function __construct(string $message = "La reserva ya se encuentra completada")
    {
        parent::__construct($message);
    }
}