<?php
namespace App\Shared\Exceptions;

class InvalidFilterException extends BusinessValidationException {
    public function __construct(string $filtro)
    {
        parent::__construct("Filtro '$filtro' no válido", $filtro);
    }
}