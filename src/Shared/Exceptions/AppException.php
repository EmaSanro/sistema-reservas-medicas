<?php
namespace App\Shared\Exceptions;

use Exception;

abstract class AppException extends Exception {

    abstract public function getStatusCode(): int;

    abstract public function getSafeMessage(): string;
}