<?php
namespace App\Controller;

use App\Security\JWTHandler;
use OpenApi\Attributes as OA;
#[OA\Info(version: "1.0.0", title: "API Reservas medicas", description: "API para gestionar las reservas medicas")]
abstract class BaseController {
    
    protected function autenticar(array $rolesPermitidos) {
        try {
            // 1. Validar el token usando la funcionalidad creada
            $usuario = JWTHandler::validateToken();

            // 2. Si se especificaron roles, verificar que el usuario tenga uno de ellos
            if (!empty($rolesPermitidos) && !in_array($usuario->rol, $rolesPermitidos)) {
                throw new \Exception("No tienes permisos para acceder a este recurso");
            }
            return $usuario;
        } catch (\Exception $e) {
            // Si el token es inválido o no existe
            $this->jsonResponse(401, [ 
                "ERROR" => $e->getMessage()
            ]);
        }
    }

    protected function jsonResponse(int $code, mixed $response) {
        http_response_code($code);
        echo json_encode($response);
        exit;
    }
}