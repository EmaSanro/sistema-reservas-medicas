<?php
namespace App\Controller;

use App\Model\DTOs\RespuestaReservaDTO;
use App\Repository\ReservasRepository;
use App\Model\DTOs\ReservaDTO;
use App\Security\Validaciones;
use OpenApi\Attributes as OA;

#[OA\Info(version: "1.0.0", title: "API Reservas medicas", description: "API para gestionar las reservas medicas")]
class ReservasController extends BaseController {
    public function __construct(private ReservasRepository $repo) {}

    #[OA\Get(
        path: "/reservas",
        summary: "Obtener todas las reservas",
        tags: ["Reservas"]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de reservas",
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
        try {
            $this->autenticar(["Profesional", "Admin"]);

            $reservas = $this->repo->obtenerTodas();
            if($reservas) {
                $reservasDTO = array_map(
                    fn(array $reserva) => RespuestaReservaDTO::fromArray($reserva),
                    $reservas 
                );
                return $this->jsonResponse(200, $reservasDTO);
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
        try {
            $usuario = $this->autenticar(["Paciente", "Profesional", "Admin"]);
            Validaciones::validarID($usuario->id);
            $reservas = $this->repo->obtenerReservasPorUsuarioId($usuario->id, $usuario->rol);
            if($reservas) {
                $reservasDTO = array_map(
                    fn(array $reserva) => RespuestaReservaDTO::fromArray($reserva),
                    $reservas
                );
                return $this->jsonResponse(200, $reservasDTO);
            } else {
                return $this->jsonResponse(404, ["ERROR" => "Ese profesional no tiene reservas"]);
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
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaReserva")
    )]
    #[OA\Response(
        response: 404,
        description: "No se encontro un paciente/profesional con ese id"
    )]
    #[OA\Response(
        response: 409,
        description: "Este paciente ya tiene una reserva para esa misma fecha",
    )]
    public function reservar() {
        try {
            $paciente = $this->autenticar(["Paciente"]);
            $input = json_decode(file_get_contents("php://input"), true);
            Validaciones::validarInput($input);

            $dto = ReservaDTO::fromArray($input);
            if($this->repo->buscarCoincidencia($paciente->id, $dto->getIdProfesional(), $dto->getFecha())) {
                return $this->jsonResponse(409, ["ERROR" => "Lo siento este paciente ya tiene una reserva para esa misma fecha"]);
            }

            if(!$this->repo->reservar($dto->getIdProfesional(), $paciente->id, $dto->getFecha())) {
                return $this->jsonResponse(500, ["ERROR" => "No se ha podido reservar"]);
            }

            return $this->jsonResponse(201, ["EXITO" => "Su reserva ha sido procesada correctamente!"]);
        } catch(\DomainException $d) {
            return $this->jsonResponse(404, ["ERROR" => "No se encontro un paciente/profesional con ese id"]);
        } catch (\PDOException $e) {
            return $this->jsonResponse(500, ["ERROR" => "Ha ocurrido un error en la base de datos", "Mensaje" => $e->getMessage()]);
        }
    }

    public function cancelarReserva(int $id) {
        $paciente = $this->autenticar(["Paciente"]);
        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarID($id);
        Validaciones::validarInput($input);
        if(!$this->repo->perteneceAlPaciente($id, $paciente->id)) {
            return $this->jsonResponse(403, ["ERROR" => "No puedes cancelar una reserva que no es tuya!"]);
        }
        try {
            $borrado = $this->repo->cancelarReserva($id);
            if($borrado) {
                return $this->jsonResponse(204, "");
            } else {
                return $this->jsonResponse(404, "No hay una reserva con ese id");
            }
        } catch (\PDOException $e) {
            return $this->jsonResponse(500, ["ERROR" => "Ha ocurrido un error en la base de datos"]);
        }
    }
}