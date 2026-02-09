<?php
namespace App\Controller;

use App\Exceptions\DatabaseException;
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
        } catch (DatabaseException $e) {
            return $this->jsonResponse(
                500,
                [
                    "ERROR" => "Ha ocurrido un error en la base de datos"
                ]
            );
        }
    }
}