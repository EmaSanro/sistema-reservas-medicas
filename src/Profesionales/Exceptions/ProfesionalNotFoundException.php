<?php
namespace App\Exceptions\Profesionales;

use App\Shared\Exceptions\NotFoundException;

class ProfesionalNotFoundException extends NotFoundException {
    public function __construct(mixed $identificador)
    {
        parent::__construct("Profesional", $identificador);
    }
}