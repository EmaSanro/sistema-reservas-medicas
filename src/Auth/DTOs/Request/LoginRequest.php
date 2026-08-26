<?php
namespace App\Auth\DTOs\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "LoginRequest",
    required: ["password"]
)]
class LoginRequest {
    public function __construct(
        #[OA\Property(description: "Contraseña del usuario", example: "password123")]
        private string $password,
        #[OA\Property(description: "Telefono del usuario", example: 1234567890, nullable: true)]
        private string|null $telefono = null,
        #[OA\Property(description: "Email del usuario", example: "example@gmail.com", nullable: true)]
        private string|null $email = null
    ) {}

    public function getTelefono(): string|null {
        return $this->telefono;
    }

    public function getEmail(): string|null {
        return $this->email;
    }

    public function getPassword(): string {
        return $this->password;
    }
}