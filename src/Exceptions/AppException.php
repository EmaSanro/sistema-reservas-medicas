<?php
namespace App\Exceptions;

use Exception;

abstract class AppException extends Exception {
    protected int $statusCode;

    public function getStatusCode(): int {
        return $this->statusCode;
    }
}