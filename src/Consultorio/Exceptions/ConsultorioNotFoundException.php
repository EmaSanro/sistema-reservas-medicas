<?php
namespace App\Exceptions\Consultorios;

use App\Shared\Exceptions\NotFoundException;

class ConsultorioNotFoundException extends NotFoundException {
    public function __construct(mixed $identificador)
    {
        parent::__construct("Consultorio", $identificador);
    }
}