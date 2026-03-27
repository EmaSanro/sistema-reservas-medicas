<?php
namespace App\Middleware;

use App\Auth\Exceptions\ForbiddenException;
use App\Security\JWTHandler;

class AuthMiddleware {
    public static function handle(array $rolesPermitidos = []) {
        $usuario = JWTHandler::validateToken();

        if (!empty($rolesPermitidos) && !in_array($usuario->rol, $rolesPermitidos)) {
            throw new ForbiddenException("No tenés permisos");
        }

        return $usuario;
    }
}