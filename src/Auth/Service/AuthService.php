<?php
namespace App\Auth\Service;

use App\Auth\DTOs\Request\LoginRequest;
use App\Auth\DTOs\Response\LoginResponse;
use App\Auth\Exceptions\WrongCredentialsException;
use App\Auth\Mapper\AuthMapper;
use App\Auth\Repository\AuthRepository;
use App\Security\JWTHandler;

class AuthService {
    public function __construct(private AuthRepository $repo){ }

    public function login(LoginRequest $loginRequest): LoginResponse {
        $usuario = $this->repo->buscarUsuario(($loginRequest->getEmail() ?? $loginRequest->getTelefono()));
        if($usuario && password_verify($loginRequest->getPassword(), $usuario->getPassword())) {
            $payload = [
                "id" => $usuario->getId(),
                "nombre" => $usuario->getNombre() . " ". $usuario->getApellido(),
                "rol" => $usuario->getRol(),
                "email" => $usuario->getEmail(),
                "telefono" => $usuario->getTelefono()
            ];
            $token = JWTHandler::generateToken($payload);
            return AuthMapper::toLoginResponse($token);
        } else {
            throw new WrongCredentialsException();
        }
    }
}