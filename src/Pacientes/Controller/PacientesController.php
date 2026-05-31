<?php
namespace App\Pacientes\Controller;

use App\Auth\Model\Roles;
use App\Middleware\AuthMiddleware;
use App\Middleware\ErrorMiddleware;
use App\Pacientes\Mapper\PacienteMapper;
use App\Pacientes\Service\PacientesService;
use App\Pacientes\Validators\PacienteValidator;
use App\Shared\BaseController;
use OpenApi\Attributes as OA;

class PacientesController extends BaseController {

    public function __construct(private PacientesService $service) {  }

    #[OA\Get(
        path: "/pacientes",
        summary: "Lista de pacientes",
        tags: ["Pacientes"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de pacientes",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/RespuestaPaciente")
        )
    )]
    public function obtenerTodos() {
        try {
            AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);

            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

            $paginated = $this->service->obtenerTodos($page, $limit);

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
            AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);
            PacienteValidator::validarID($id);

            $paciente = $this->service->obtenerPorId((int) $id);
            
            return $this->jsonResponse(200, $paciente);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    #[OA\Get(
        path: "/pacientes/buscar",
        summary: "Buscar pacientes",
        tags: ["Pacientes"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Parameter(
        name: "filtro",
        in: "query",
        description: "Filtro de busqueda(nombre, apellido, email, telefono)",
        required: true,
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Parameter(
        name: "valor",
        in: "query",
        description: "Valor de busqueda",
        required: true,
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Listado de pacientes filtrados",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/RespuestaPaciente")
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Filtro invalido",
        content: new OA\JsonContent(example:["ERROR" => "El filtro ingresado es un filtro invalido"])
    )]
    public function buscarPor() {
        try {
            AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);

            PacienteValidator::validarParametrosBusqueda($_GET["filtro"] ?? "", $_GET["valor"] ?? "");
            $filtro = $_GET["filtro"];
            $valor = $_GET["valor"];
            
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

            $paginated = $this->service->buscarPor($filtro, $valor, $page, $limit);
    
            return $this->paginatedResponse(200, $paginated['data'], $paginated['total'], $page, $limit);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    #[OA\Post(
        path: "/pacientes/registrar",
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
    public function registrarPaciente() {
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            PacienteValidator::validarRequestCrear($input);
            
            $pacienteCreado = $this->service->registrarPaciente(PacienteMapper::toRequestCrear($input));
            
            return $this->jsonResponse(201, $pacienteCreado);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }
    
    #[OA\Patch(
        path: "/paciente/{id}",
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
            $usuario = AuthMiddleware::handle([Roles::PACIENTE, Roles::ADMIN]);
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
            AuthMiddleware::handle([Roles::ADMIN]);

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