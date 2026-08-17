<?php
namespace App\Shared\Exceptions;

use Exception;

abstract class AppException extends Exception {

    abstract public function getStatusCode(): int;

    abstract public function getSafeMessage(): string;

    /**
     * Headers HTTP adicionales que debe emitir la respuesta de error
     * (por ejemplo Retry-After en un 429).
     *
     * @return array<string, string>
     */
    public function getHeaders(): array {
        return [];
    }
}