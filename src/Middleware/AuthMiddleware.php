<?php
namespace App\Middleware;

use App\Auth\Exceptions\ForbiddenException;
use App\Security\JwtHandler;

class AuthMiddleware {
    public static function handle(array $rolesPermitidos = []) {
        $usuario = JwtHandler::validateToken();

        if (!empty($rolesPermitidos) && !in_array($usuario->rol, $rolesPermitidos)) {
            throw new ForbiddenException("No tenés permisos");
        } elseif(!$usuario->activo) {
            throw new ForbiddenException();
        }
        return $usuario;
    }
}