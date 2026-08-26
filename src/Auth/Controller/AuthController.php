<?php
namespace App\Auth\Controller;

use App\Auth\Mapper\AuthMapper;
use App\Auth\Service\AuthService;
use App\Auth\Validators\AuthValidator;
use App\Middleware\ErrorMiddleware;
use App\Shared\BaseController;
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
        content: new OA\JsonContent(ref: "#/components/schemas/LoginRequest")
    )]
    #[OA\Response(
        response: 200,
        description: "Logueado correctamente",
        content: new OA\JsonContent(ref: "#/components/schemas/LoginResponse")
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
    #[OA\Response(
        response: 403,
        description: "La cuenta se encuentra dada de baja",
        content: new OA\JsonContent(example:["message" => "Este usuario se encuentra dado de baja"])
    )]
    #[OA\Response(
        response: 429,
        description: "Demasiados intentos. Incluye header Retry-After con los segundos restantes",
        content: new OA\JsonContent(example:["message" => "Demasiados intentos. Probá de nuevo más tarde."])
    )]
    public function login() {
        try {
            $input = json_decode(file_get_contents("php://input"), true) ?? [];
            AuthValidator::validateInputLogin($input);

            $response = $this->service->login(AuthMapper::toLoginRequest($input), $this->clientIp());

            $this->jsonResponse(200, $response);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }
}