<?php
namespace App\Exceptions;

class InvalidFilterException extends AppException {
    protected int $statusCode = 400;
}