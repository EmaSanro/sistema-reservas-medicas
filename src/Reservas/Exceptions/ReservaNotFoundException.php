<?php
namespace App\Exceptions\Reservas;

use App\Shared\Exceptions\NotFoundException;

class ReservaNotFoundException extends NotFoundException {
    public function __construct(mixed $identificador)
    {
        parent::__construct("Reserva", $identificador);
    }
}