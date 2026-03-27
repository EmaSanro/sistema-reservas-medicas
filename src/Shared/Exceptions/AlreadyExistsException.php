<?php
namespace App\Shared\Exceptions;

class AlreadyExistsException extends AppException {

    public function __construct(private string $entidad, string $campo, mixed $valor) {
        parent::__construct(sprintf("%s con %s '%s' ya existe", $entidad, $campo, (string) $valor));
    }

    public function getStatusCode(): int {
        return 409;
    }

    public function getSafeMessage(): string
    {
        return sprintf("%s ya existente", $this->entidad);
    }
}