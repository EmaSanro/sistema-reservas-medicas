<?php
namespace App\Nota\Exceptions;

use App\Shared\Exceptions\NotFoundException;

class ArchivoNotFoundException extends NotFoundException {
    public function __construct(mixed $identificador)
    {
        parent::__construct("Archivo", $identificador);
    }
}