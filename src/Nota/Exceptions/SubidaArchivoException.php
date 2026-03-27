<?php
namespace App\Exceptions\Nota;

use App\Shared\Exceptions\AppException;

class SubidaArchivoException extends AppException {
    public function __construct(string $message = "Error al subir el archivo")
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return 500;
    }

    public function getSafeMessage(): string
    {
        return $this->message;
    }
}