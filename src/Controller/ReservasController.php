<?php
namespace App\Controller;

use App\Middleware\AuthMiddleware;
use App\Model\DTOs\RespuestaReservaDTO;
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
        tags: ["Reservas"]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista todas las reservas existentes",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/RespuestaReserva")
        )
    )]
    #[OA\Response(
        response: 404,
        description: "No hay reservas",
        content: new OA\JsonContent()
    )]
    public function obtenerTodas() {
        AuthMiddleware::handle([Roles::ADMIN]);

        $reservas = $this->service->obtenerTodas();

        return $this->jsonResponse(200, $reservas);
    }
    #[OA\Get(
        path: "/reservas/mis-reservas",
        summary: "Obtener las reservas de un usuario ya sea profesional o paciente",
        tags: ["Reservas"]
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
        content: new OA\JsonContent()
    )]
    public function obtenerReservasPorUsuarioId() {
        $usuario = AuthMiddleware::handle([Roles::PACIENTE, Roles::PROFESIONAL]);

        $reservas = $this->service->obtenerReservasPorUsuarioId($usuario->getId(), $usuario->getRol());
        
        return $this->jsonResponse(200, $reservas);
    }
    #[OA\Post(
        path: "/reservas/reservar",
        summary: "Realizar una reserva",
        tags: ["Reservas"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/Reserva")
    )]
    #[OA\Response(
        response: 201,
        description: "Reserva realizada",
        content: "EXITO => Su reserva ha sido procesada correctamente!"
    )]
    #[OA\Response(
        response: 404,
        description: "No se encontro un profesional con ese id"
    )]
    #[OA\Response(
        response: 409,
        description: "Este paciente/profesional ya tiene una reserva para esa misma fecha"
    )]
    public function reservar() {
        $paciente = AuthMiddleware::handle([Roles::PACIENTE]);

        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarInput($input);

        $dto = ReservaDTO::fromArray($input);

        $reserva = $this->service->reservar($dto, $paciente);

        return $this->jsonResponse(201, $reserva);
    }
    // TODO cambiar funcionalidad cancelarReserva para que no se elimine el registro, sino que se marque como CANCELADA (constituye a cambios en la BD y modelos y DTOs)
    public function cancelarReserva(int $id) {
        $paciente = AuthMiddleware::handle([Roles::PACIENTE]);

        $this->service->cancelarReserva($id, $paciente);

        return $this->jsonResponse(204, "");
    }
}