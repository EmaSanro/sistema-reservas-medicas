<?php
namespace App\Reservas\Controller;

use App\Auth\Model\Roles;
use App\Middleware\AuthMiddleware;
use App\Middleware\ErrorMiddleware;
use App\Reservas\Mapper\ReservaMapper;
use App\Reservas\Service\ReservasService;
use App\Reservas\Validators\ReservaValidator;
use App\Shared\BaseController;
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
        try {
            AuthMiddleware::handle([Roles::ADMIN]);
    
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
            
            $paginated = $this->service->obtenerTodas($page, $limit);
    
            return $this->paginatedResponse(200, $paginated['data'], $paginated['total'], $page, $limit);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
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
        try {
            $usuario = AuthMiddleware::handle([Roles::PACIENTE, Roles::PROFESIONAL]);
    
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
            
            $paginated = $this->service->obtenerReservasPorUsuarioId($usuario->id, $usuario->rol, $page, $limit);
            
            return $this->paginatedResponse(200, $paginated['data'], $paginated['total'], $page, $limit);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }
    #[OA\Post(
        path: "/reservas/reservar",
        summary: "Realizar una reserva",
        tags: ["Reservas"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/CrearReservaRequest")
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
        try {
            $paciente = AuthMiddleware::handle([Roles::PACIENTE]);
    
            $input = json_decode(file_get_contents("php://input"), true) ?? [];
            ReservaValidator::validarRequestCrear($input);
    
            $reserva = $this->service->reservar(ReservaMapper::toRequestCrear($input, $paciente->id));
    
            return $this->jsonResponse(201, $reserva);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }
    #[OA\Patch(
        path: "/reservas/{id}",
        summary: "Actualizar una reserva",
        tags: ["Reservas"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type:"integer")
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/ActualizarReservaRequest")
    )]
    #[OA\Response(
        response: 200,
        description: "Reserva actualizada",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaReserva")
    )]
    #[OA\Response(
        response: 400,
        description: "Campos erroneos",
        content: new OA\JsonContent(example:["ERROR" => "La fecha no puede ser en el pasado"])
    )]
    #[OA\Response(
        response: 404,
        description: "No hay resultado",
        content: new OA\JsonContent(example:["ERROR" => "No se encontro una reserva con ese id"])
    )]
    public function actualizarReserva(string $id) {
        try {
            AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);
            ReservaValidator::validarId($id);
            $input = json_decode(file_get_contents("php://input"), true) ?? [];
            ReservaValidator::validarRequestActualizar($input);

            $reservaActualizada = $this->service->actualizarReserva((int) $id, ReservaMapper::toRequestActualizar($input));

            return $this->jsonResponse(200, $reservaActualizada);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }
    #[OA\Put(
        path: "/reservas/{id}/cancelar",
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
    public function cancelarReserva(string $id) {
        try {
            $paciente = AuthMiddleware::handle([Roles::PACIENTE]);
            ReservaValidator::validarId($id);
    
            $this->service->cancelarReserva((int) $id, $paciente);
    
            return $this->jsonResponse(200, ["EXITO" => "Reserva cancelada!"]);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }
}