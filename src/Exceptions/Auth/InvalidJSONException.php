<?php
namespace App\Exceptions\Auth;

use App\Exceptions\AppException;

class InvalidJSONException extends AppException {
    protected int $statusCode = 400;
}