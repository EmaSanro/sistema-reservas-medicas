<?php
namespace App\Auth\DTOs\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: "LoginResponse")]
class LoginResponse {
    #[OA\Property(example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTAsInJvbCI6IlBhY2llbnRlIn0.firma")]
    public readonly string $token;
    public function __construct(string $token)
    {
        $this->token = $token;
    }
}
