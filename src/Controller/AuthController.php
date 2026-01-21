<?php
namespace App\Controller;

use App\Repository\AuthRepository;

class AuthController {

    public function __construct(private AuthRepository $repo) { }

    public function login() {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            Validaciones::validarInput($input);
            Validaciones::validarLogin($input);
    
            $usuario = $this->repo->buscarUsuario(($input["email"] ?? $input["telefono"]), $input["password"]);
            if($usuario) {
                http_response_code(200);
                echo json_encode([
                    "OK" => "logueado correctamente",
                    "USUARIO" => $usuario
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    "ERROR" => "Credenciales incorrectas!"
                ]);
            }
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "ERROR" => "Ha ocurrido un error en la base de datos"
            ]);
        }
    }
}