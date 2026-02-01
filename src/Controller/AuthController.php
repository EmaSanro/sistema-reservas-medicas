<?php
namespace App\Controller;

use App\Security\Validaciones;
use App\Service\AuthService;

class AuthController extends BaseController {

    public function __construct(private AuthService $service) { }

    public function login() {
        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarInput($input);
        Validaciones::validarLogin($input);

        try {
            $token = $this->service->login($input);
            
            if($token) {
                return $this->jsonResponse(
                    200,
                    [
                        "OK" => "logueado correctamente",
                        "TOKEN" => $token
                    ]
                );
            } else {
                return $this->jsonResponse(
                    401,
                    [
                        "ERROR" => "Credenciales incorrectas!"
                    ]
                );
            }
        } catch (\PDOException $e) {
            return $this->jsonResponse(
                500,
                [
                    "ERROR" => "Ha ocurrido un error en la base de datos"
                ]
            );
        }
    }
}