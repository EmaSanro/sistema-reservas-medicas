<?php
namespace App\Controller;

use App\Auth\Validators\AuthValidator;
use App\Middleware\ErrorMiddleware;
use App\Security\Validaciones;
use App\Service\AuthService;
use OpenApi\Attributes as OA;

class AuthController extends BaseController {

    public function __construct(private AuthService $service) { }

    #[OA\Post(
        path: "/auth/login",
        summary: "Loguearse",
        tags:["Auth"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(example: "#/components/schemas/Paciente")
    )]
    #[OA\Response(
        response: 200,
        description: "Logueado correctamente",
        content: new OA\JsonContent(example:["OK" => "correctamente", "TOKEN" => "{token}"])
    )]
    #[OA\Response(
        response: 400,
        description: "JSON invalido o datos de logueo incompletos",
        content: new OA\JsonContent(example: "Json Invalido")
    )]
    #[OA\Response(
        response: 401,
        description: "Datos incorrectos",
        content: new OA\JsonContent(example:["ERROR" => "Credenciales incorrectas"])
    )]
    public function login() {
        try {
            $input = json_decode(file_get_contents("php://input"), true) ?? [];
            AuthValidator::validateInputLogin($input);

            $token = $this->service->login($input);
            
            if($token) {
                $this->jsonResponse(
                    200,
                    [
                        "OK" => "logueado correctamente",
                        "TOKEN" => $token
                    ]
                );
            } else {
                $this->jsonResponse(
                    401,
                    [
                        "ERROR" => "Credenciales incorrectas!"
                    ]
                );
            }
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }
}