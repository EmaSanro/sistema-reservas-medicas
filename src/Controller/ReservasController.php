<?php
namespace App\Controller;

use App\Model\DTOs\RespuestaReservaDTO;
use App\Repository\ReservasRepository;
use App\Model\DTOs\ReservaDTO;
use OpenApi\Attributes as OA;

#[OA\Info(version: "1.0.0", title: "API Reservas medicas", description: "API para gestionar las reservas medicas")]
class ReservasController {
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
        path: "/reservas/profesional/:idProfesional",
        summary: "Obtener las reservas de un profesional",
        tags: ["Reservas"]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de las reservas de un profesional dado",
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
    public function obtenerReservasDeProfesional(int $idProfesional) {
        try {
            Validaciones::validarID($idProfesional);
            $reservas = $this->repo->obtenerReservasDelProfesional($idProfesional);
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
    #[OA\Get(
        path: "/reservas/paciente/:idPaciente",
        summary: "Obtener las reservas de un paciente",
        tags: ["Reservas"]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de las reservas de un paciente dado",
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
    public function obtenerReservasDePaciente(int $idPaciente) {
        try {
            Validaciones::validarID($idPaciente);
            $reservas = $this->repo->obtenerReservasDelPaciente($idPaciente);
            if($reservas) {
                $reservasDTO = array_map(
                    fn(array $reserva) => RespuestaReservaDTO::fromArray($reserva),
                    $reservas
                );
                return $this->jsonResponse(200, $reservasDTO);
            } else {
                return $this->jsonResponse(404, ["ERROR" => "Ese paciente no tiene reservas"]);
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
            $input = json_decode(file_get_contents("php://input"), true);
            Validaciones::validarInput($input);

            $dto = ReservaDTO::fromArray($input);
            if($this->repo->buscarCoincidencia($dto->getIdPaciente(), $dto->getIdProfesional(), $dto->getFecha())) {
                return $this->jsonResponse(409, ["ERROR" => "Lo siento este paciente ya tiene una reserva para esa misma fecha"]);
            }

            if(!$this->repo->reservar($dto->getIdProfesional(), $dto->getIdPaciente(), $dto->getFecha())) {
                return $this->jsonResponse(500, ["ERROR" => "No se ha podido reservar"]);
            }

            $reserva = $this->repo->obtenerReservaEspecifica($dto->getIdPaciente(), $dto->getIdProfesional(), $dto->getFecha());
            return $this->jsonResponse(201, RespuestaReservaDTO::fromArray($reserva));
        } catch(\DomainException $d) {
            return $this->jsonResponse(404, ["ERROR" => "No se encontro un paciente/profesional con ese id"]);
        } catch (\PDOException $e) {
            return $this->jsonResponse(500, ["ERROR" => "Ha ocurrido un error en la base de datos", "Mensaje" => $e->getMessage()]);
        }
    }

    public function cancelarReserva(int $id) {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            Validaciones::validarID($id);
            Validaciones::validarInput($input);

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

    private function jsonResponse(int $code, mixed $response) {
        http_response_code($code);
        echo json_encode($response);
    }
}