<?php
namespace App\Exceptions\Reservas;

use App\Shared\Exceptions\BusinessValidationException;

class CancelacionTardiaException extends BusinessValidationException {
    public function __construct(string $message = "No se puede cancelar la reserva con tan poca anticipación")
    {
        parent::__construct($message);
    }
}