<?php
namespace App\Controller;

use App\Model\DTOs\RespuestaReservaDTO;
use App\Model\DTOs\ReservaDTO;
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
        $this->autenticar(["Admin"]);
        try {
            $reservas = $this->service->obtenerTodas();
            if($reservas) {
                return $this->jsonResponse(200, $reservas);
            } else {
                return $this->jsonResponse(404, ["ERROR" => "No se encontraron reservas"]);
            }
        } catch (\PDOException $e) {
            return $this->jsonResponse(500, ["ERROR" => "Ha ocurrido un error en la base de datos"]);
        }
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
        $usuario = $this->autenticar(["Paciente", "Profesional", "Admin"]);
        try {
            $reservas = $this->service->obtenerReservasPorUsuarioId($usuario->id, $usuario->rol);
            if($reservas) {
                return $this->jsonResponse(200, $reservas);
            } else {
                return $this->jsonResponse(404, ["ERROR" => "No tienes reservas"]);
            }
        } catch (\PDOException $e) {
            return $this->jsonResponse(500, ["ERROR" => "Ha ocurrido un error en la base de datos"]);
        }
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
        $paciente = $this->autenticar(["Paciente"]);
        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarInput($input);
        try {
            $dto = ReservaDTO::fromArray($input);

            $reserva = $this->service->reservar($dto, $paciente);
            if($reserva) {
                return $this->jsonResponse(201, ["EXITO" => "Su reserva ha sido procesada correctamente!"]);
            }
        } catch(\DomainException $d) {
            return $this->jsonResponse(404, ["ERROR" => "No se encontro un profesional con ese id"]);
        } catch (\PDOException $e) {
            return $this->jsonResponse(500, ["ERROR" => "Ha ocurrido un error en la base de datos", "Mensaje" => $e->getMessage()]);
        }
    }
    // TODO cambiar funcionalidad cancelarReserva para que no se elimine el registro, sino que se marque como CANCELADA (constituye a cambios en la BD y modelos y DTOs)
    public function cancelarReserva(int $id) {
        $paciente = $this->autenticar(["Paciente"]);
        try {
            $borrado = $this->service->cancelarReserva($id, $paciente);
            if($borrado) {
                return $this->jsonResponse(204, "");
            } else {
                return $this->jsonResponse(404, "No hay una reserva con ese id");
            }
        } catch(\Exception $e) {
                return $this->jsonResponse(400, $e->getMessage());
        } catch (\PDOException $e) {
            return $this->jsonResponse(500, ["ERROR" => "Ha ocurrido un error en la base de datos"]);
        }
    }
}