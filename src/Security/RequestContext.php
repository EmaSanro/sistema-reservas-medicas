<?php
namespace App\Security;

/**
 * Guarda el usuario autenticado durante el request en curso.
 *
 * Lo completa el Router al ejecutar el AuthMiddleware sobre una ruta protegida.
 * Los controllers lo leen a traves de BaseController::usuarioAutenticado().
 */
final class RequestContext {

    private static mixed $usuario = null;

    public static function setUsuario(mixed $usuario): void {
        self::$usuario = $usuario;
    }

    public static function usuario(): mixed {
        if (self::$usuario === null) {
            throw new \LogicException(
                "No hay usuario autenticado en el contexto: la ruta fue registrada como publica en el router."
            );
        }
        return self::$usuario;
    }

    public static function tieneUsuario(): bool {
        return self::$usuario !== null;
    }
}
