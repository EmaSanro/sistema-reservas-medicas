<?php
namespace App\Service;

use App\Repository\AuthRepository;
use App\Security\JWTHandler;

class AuthService {
    public function __construct(private AuthRepository $repo){ }

    public function login($loginRequest) {
        $usuario = $this->repo->buscarUsuario(($loginRequest["email"] ?? $loginRequest["telefono"]));
        if($usuario && password_verify($loginRequest["password"], $usuario->getPassword())) {
            $payload = [
                "id" => $usuario->getId(),
                "nombre" => $usuario->getNombre() . " ". $usuario->getApellido(),
                "rol" => $usuario->getRol(),
                "email" => $usuario->getEmail(),
                "telefono" => $usuario->getTelefono()
            ];
            $token = JWTHandler::generateToken($payload);
            return $token;
        }
    }
}