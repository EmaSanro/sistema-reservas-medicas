<?php
namespace App\Exceptions;

class DatabaseException extends AppException {
    protected int $statusCode = 500;
}