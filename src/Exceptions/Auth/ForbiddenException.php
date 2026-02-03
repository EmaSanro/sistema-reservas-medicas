<?php
namespace App\Exceptions\Auth;

use App\Exceptions\AppException;

class ForbiddenException extends AppException {
    protected int $statusCode = 403;
}