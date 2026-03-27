<?php
namespace App\Shared\Exceptions;

class ConflictException extends AppException {

    public function __construct(private string $safeMessage)
    {
        parent::__construct($safeMessage);
    }

    public function getStatusCode(): int
    {
        return 409;
    }

    public function getSafeMessage(): string
    {
        return $this->safeMessage;
    }
}
