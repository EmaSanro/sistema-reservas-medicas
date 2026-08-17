<?php
namespace App\Pacientes\Controller;

use App\Middleware\ErrorMiddleware;
use App\Pacientes\Mapper\PacienteMapper;
use App\Pacientes\Service\PacientesService;
use App\Pacientes\Validators\PacienteSearchValidator;
use App\Pacientes\Validators\PacienteValidator;
use App\Shared\BaseController;
use OpenApi\Attributes as OA;

class PacientesController extends BaseController {

    public function __construct(private PacientesService $service) {  }

    #[OA\Get(
        path: "/pacientes",
        summary: "Lista de pacientes. Opcionalmente se pueden pasar filtros como query params (nombre, apellido, email, telefono)",
        tags: ["Pacientes"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Parameter(name: "nombre", in: "query", required: false, schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "apellido", in: "query", required: false, schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "email", in: "query", required: false, schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "telefono", in: "query", required: false, schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "limit", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Lista paginada de pacientes (opcionalmente filtrada)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/RespuestaPaciente")
                ),
                new OA\Property(property: "total", type: "integer", example: 42),
                new OA\Property(property: "page", type: "integer", example: 1),
                new OA\Property(property: "limit", type: "integer", example: 10),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Filtro no permitido o valor invalido",
        content: new OA\JsonContent(example:["ERROR" => "Filtro no permitido"])
    )]
    public function listar() {
        try {
            $filtros = PacienteSearchValidator::validar($this->extractFilterParams());
            ['page' => $page, 'limit' => $limit] = $this->extractPagination();

            $paginated = $this->service->listar($filtros, $page, $limit);

            return $this->paginatedResponse(200, $paginated['data'], $paginated['total'], $page, $limit);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    #[OA\Get(
        path: "/pacientes/{id}",
        summary: "Paciente por ID",
        tags: ["Pacientes"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Paciente obtenido por su ID",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaPaciente")
    )]
    #[OA\Response(
        response: 400,
        description: "ID invalido",
        content: new OA\JsonContent(example:["ERROR" => "el ID ingresado no es valido"])
    )]
    #[OA\Response(
        response: 404,
        description: "Paciente no encontrado",
        content: new OA\JsonContent(example:["ERROR" => "No hay un paciente con ese id"])
    )]
    public function obtenerPorId(string $id) {
        try {
            PacienteValidator::validarID($id);

            $paciente = $this->service->obtenerPorId((int) $id);
            
            return $this->jsonResponse(200, $paciente);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    #[OA\Post(
        path: "/pacientes",
        summary: "Registrarse como paciente",
        tags: ["Pacientes"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/CrearPacienteRequest")
    )]
    #[OA\Response(
        response: 201,
        description: "Registrado correctamente",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaPaciente")
    )]
    #[OA\Response(
        response: 400,
        description: "JSON invalido o criterios de password no cumplidos",
        content: new OA\JsonContent(example:["ERROR" => "La contraseña debe tener minimo 8 caracteres"])
    )]
    #[OA\Response(
        response: 409,
        description: "Usuario ya registrado",
        content: new OA\JsonContent(example:["ERROR" => "Ya se registro un usuario con ese email y/o telefono"])
    )]
    #[OA\Response(
        response: 429,
        description: "Demasiados intentos desde esta IP. Incluye header Retry-After con los segundos restantes",
        content: new OA\JsonContent(example:["message" => "Demasiados intentos. Probá de nuevo más tarde."])
    )]
    public function registrarPaciente() {
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            PacienteValidator::validarRequestCrear($input);

            $pacienteCreado = $this->service->registrarPaciente(
                PacienteMapper::toRequestCrear($input),
                $this->clientIp()
            );

            return $this->jsonResponse(201, $pacienteCreado);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }
    
    #[OA\Patch(
        path: "/pacientes/{id}",
        summary: "Actualizar datos del usuario",
        tags: ["Pacientes"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/ActualizarPacienteRequest")
    )]
    #[OA\Response(
        response: 200,
        description: "Datos actualizados",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaPaciente")
    )]
    #[OA\Response(
        response: 400,
        description: "id o campo/s invalido/s",
        content: new OA\JsonContent(example:["ERROR" => "JSON invalido"])
    )]
    #[OA\Response(
        response: 409,
        description: "Usuario existente",
        content: new OA\JsonContent(example:["ERROR" => "Ya existe un usuario registrado con ese email y/o telefono"])
    )]
    public function actualizarPaciente(string $id) {
        try {
            $usuario = $this->usuarioAutenticado();
            PacienteValidator::validarID($id);

            $input = json_decode(file_get_contents("php://input"), true) ?? [];
            PacienteValidator::validarRequestActualizar($input);

            $pacienteActualizado = $this->service->actualizarPaciente((int) $id, PacienteMapper::toRequestActualizar($input), $usuario);
            
            return $this->jsonResponse(200, $pacienteActualizado);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }
    
    #[OA\Delete(
        path: "/pacientes/{id}",
        summary: "Dar de baja paciente",
        tags: ["Pacientes"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 204,
        description: "Paciente dado de baja correctamente",
        content: new OA\JsonContent()
    )]
    #[OA\Response(
        response: 400,
        description: "Solicitud erronea: ID invalido | No hay motivo | motivo muy largo",
        content: new OA\JsonContent(example:["ERROR" => "ID invalido"])
    )]
    #[OA\Response(
        response: 404,
        description: "Paciente no encontrado",
        content: new OA\JsonContent(example:["ERROR" => "No existe un paciente con el id especificado"])
    )]
    public function eliminarPaciente(string $id) {
        try {
            PacienteValidator::validarID($id);
            $input = json_decode(file_get_contents("php://input"), true) ?? [];
            PacienteValidator::validarInputBajaPaciente($input);
            
            $this->service->darDeBajaPaciente((int) $id, $input["motivo"]);

            return $this->jsonResponse(204, "");
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }
}