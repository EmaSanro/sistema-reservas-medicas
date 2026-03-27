<?php
namespace App\Exceptions\Nota;

use App\Shared\Exceptions\AppException;

class TamanioArchivoException extends AppException {
    public function __construct(string $message = "El tamaño del archivo excede el límite permitido")
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return 413;
    }

    public function getSafeMessage(): string
    {
        return $this->message;
    }
}