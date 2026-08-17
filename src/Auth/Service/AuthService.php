<?php
namespace App\Auth\Service;

use App\Auth\DTOs\Request\LoginRequest;
use App\Auth\DTOs\Response\LoginResponse;
use App\Auth\Exceptions\UsuarioInactivoException;
use App\Auth\Exceptions\WrongCredentialsException;
use App\Auth\Mapper\AuthMapper;
use App\Auth\Repository\AuthRepository;
use App\Security\JWTHandler;

class AuthService {
    public function __construct(
        private AuthRepository $repo,
        private RateLimiter $rateLimiter,
    ) { }

    public function login(LoginRequest $loginRequest, string $ip): LoginResponse {
        $identificador = $loginRequest->getEmail() ?? $loginRequest->getTelefono();

        $this->rateLimiter->checkLogin($identificador, $ip);

        $credenciales = $this->repo->buscarCredenciales($identificador);

        // El orden importa: primero se validan las credenciales y recien
        // despues el estado de la cuenta. Al reves, cualquiera podria
        // averiguar que emails existen probando direcciones.
        if (!$credenciales || !password_verify($loginRequest->getPassword(), $credenciales->passwordHash)) {
            $this->rateLimiter->recordLoginFailure($identificador, $ip);
            throw new WrongCredentialsException();
        }

        // No se registra fallo: las credenciales eran correctas. Lo que falla
        // es el estado de la cuenta, y quien la tiene dada de baja no deberia
        // quedar bloqueado ademas por intentar entrar.
        if (!$credenciales->activo) {
            throw new UsuarioInactivoException();
        }

        $this->rateLimiter->clearLoginAttempts($identificador);

        $token = JWTHandler::generateToken([
            "id" => $credenciales->id,
            "nombre" => $credenciales->nombreCompleto,
            "rol" => $credenciales->rol,
            "email" => $credenciales->email,
            "telefono" => $credenciales->telefono,
            "activo" => $credenciales->activo
        ]);

        return AuthMapper::toLoginResponse($token);
    }
}