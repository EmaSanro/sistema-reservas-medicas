<?php
namespace App\Consultorio\Exceptions;

use App\Shared\Exceptions\AlreadyExistsException;

class ConsultorioAlreadyExistsException extends AlreadyExistsException {
    public function __construct(string $campo, mixed $valor) {
        parent::__construct("Consultorio", $campo, $valor);
    }
}