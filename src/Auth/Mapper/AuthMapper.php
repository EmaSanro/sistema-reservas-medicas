<?php
namespace App\Auth\Mapper;

use App\Auth\DTOs\Request\LoginRequest;
use App\Auth\DTOs\Response\LoginResponse;

class AuthMapper {
    public static function toLoginRequest(array $input): LoginRequest {
        return new LoginRequest(
            $input["password"],
            $input["telefono"] ?? null,
            $input["email"] ?? null
        );
    }

    public static function toLoginResponse(string $token): LoginResponse {
        return new LoginResponse(
            $token
        );
    }
}