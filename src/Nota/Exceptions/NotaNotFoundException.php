<?php
namespace App\Nota\Exceptions;

use App\Shared\Exceptions\NotFoundException;

class NotaNotFoundException extends NotFoundException {
    public function __construct(mixed $identificador)
    {
        parent::__construct("Nota", $identificador);
    }
}