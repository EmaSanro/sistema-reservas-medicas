<?php
namespace App\Controller;

use App\Middleware\AuthMiddleware;
use App\Model\DTOs\ReservaDTO;
use App\Model\Roles;
use App\Security\Validaciones;
use App\Service\ReservasService;
use OpenApi\Attributes as OA;

class ReservasController extends BaseController {
    public function __construct(private ReservasService $service) {}

    #[OA\Get(
        path: "/reservas",
        summary: "Obtener todas las reservas",
        tags: ["Reservas"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista todas las reservas existentes",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/RespuestaReserva")
        )
    )]
    public function obtenerTodas() {
        AuthMiddleware::handle([Roles::ADMIN]);

        $reservas = $this->service->obtenerTodas();

        return $this->jsonResponse(200, $reservas);
    }
    #[OA\Get(
        path: "/reservas/mis-reservas",
        summary: "Obtener las reservas de un usuario ya sea profesional o paciente",
        tags: ["Reservas"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de las reservas de un usuario dado",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/RespuestaReserva")
        )
    )]
    #[OA\Response(
        response: 404,
        description: "No hay reservas",
        content: new OA\JsonContent(example:["ERROR" => "No tienes reservas realizadas"])
    )]
    public function obtenerReservasPorUsuarioId() {
        $usuario = AuthMiddleware::handle([Roles::PACIENTE, Roles::PROFESIONAL]);

        $reservas = $this->service->obtenerReservasPorUsuarioId($usuario->id, $usuario->rol);
        
        return $this->jsonResponse(200, $reservas);
    }
    #[OA\Post(
        path: "/reservas/reservar",
        summary: "Realizar una reserva",
        tags: ["Reservas"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(example: "#/components/schemas/Reserva")
    )]
    #[OA\Response(
        response: 201,
        description: "Reserva realizada",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaReserva")
    )]
    #[OA\Response(
        response: 404,
        description: "No hay resultado",
        content: new OA\JsonContent(example:["ERROR" => "No se encontro un profesional con ese id"])
    )]
    #[OA\Response(
        response: 409,
        description: "Reserva superpuesta",
        content: new OA\JsonContent(example:["ERROR" => "Este usuario ya tiene una reserva para esa misma fecha"])
    )]
    public function reservar() {
        $paciente = AuthMiddleware::handle([Roles::PACIENTE]);

        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarInput($input);

        $dto = ReservaDTO::fromArray($input);

        $reserva = $this->service->reservar($dto, $paciente);

        return $this->jsonResponse(201, $reserva);
    }
    #[OA\Put(
        path: "/reservas/cancelar/{id}",
        summary: "Cancelar una reserva",
        tags: ["Reservas"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type:"integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Reserva cancelada!",
        content: new OA\JsonContent(example:["EXITO" => "Reserva cancelada!"])
    )]
    #[OA\Response(
        response: 400,
        description: "Cancelacion tardia",
        content: new OA\JsonContent(example:["ERROR" => "Solo se puede cancelar una reserva con Xhs de anticipacion"])
    )]
    #[OA\Response(
        response: 409,
        description: "La reserva ya fue cancelada o completada",
        content: new OA\JsonContent(example: ["ERROR" => "La reserva ya fue cancelada!"])
    )]
    #[OA\Response(
        response: 500,
        description: "Error del servidor",
        content: new OA\JsonContent(example:["ERROR" => "Error Interno del Servidor!"])
    )]
    public function cancelarReserva(int $id) {
        $paciente = AuthMiddleware::handle([Roles::PACIENTE]);

        $this->service->cancelarReserva($id, $paciente);

        return $this->jsonResponse(200, ["EXITO" => "Reserva cancelada!"]);
    }
}