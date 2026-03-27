<?php
namespace App\Exceptions\Nota;

use App\Shared\Exceptions\NotFoundException;

class NotaNotFoundException extends NotFoundException {
    public function __construct(mixed $identificador)
    {
        parent::__construct("Nota", $identificador);
    }
}