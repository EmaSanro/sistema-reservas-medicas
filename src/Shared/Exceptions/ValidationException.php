<?php
namespace App\Shared\Exceptions;

use App\Shared\Exceptions\AppException;

class ValidationException extends AppException {

    private array $errors;

    public function __construct(array $errors, $message = "Error de validacion")
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getStatusCode(): int
    {
        return 400;
    }

    public function getSafeMessage(): string
    {
        return "Los datos ingresados no son válidos.";
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public static function forField(string $field, string $message): self {
        return new self([$field => $message]);
    }

    public function toArray(): array {
        return [
            "message" => $this->getSafeMessage(),
            "errors" => $this->getErrors()
        ];

    }
}