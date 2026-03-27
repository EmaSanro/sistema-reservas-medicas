<?php
namespace App\Shared\Exceptions;

class NotFoundException extends AppException {

    public function __construct(private string $entidad, private mixed $identificador)
    {
        parent::__construct(sprintf("%s con identificador '%s' no encontrado", $entidad, $identificador));
    }

    public function getStatusCode(): int
    {
        return 404;
    }

    public function getSafeMessage(): string
    {
        return sprintf("%s con identificador '%s' no encontrado", $this->entidad, $this->identificador);
    }
}