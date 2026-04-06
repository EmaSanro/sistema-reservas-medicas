<?php
namespace App\Auth\DTOs\Response;

class LoginResponse {
    public readonly string $token;
    public function __construct(string $token)
    {
        $this->token = $token;
    }
}