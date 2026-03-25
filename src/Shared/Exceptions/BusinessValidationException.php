<?php
namespace App\Shared\Exceptions;

class BusinessValidationException extends AppException {

    public function __construct(private string $safeMessage, private ?string $field = null)
    {
        parent::__construct($safeMessage);
    }

    public function getStatusCode(): int
    {
        return 422;
    }

    public function getSafeMessage(): string
    {
        return $this->safeMessage;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public static function forField(string $field, string $message): self
    {
        return new self($field, $message);
    }
}