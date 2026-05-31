<?php
namespace App\Profesionales\Controller;

use App\Auth\Model\Roles;
use App\Middleware\AuthMiddleware;
use App\Middleware\ErrorMiddleware;
use App\Profesionales\Mapper\ProfesionalMapper;
use App\Profesionales\Service\ProfesionalesService;
use App\Profesionales\Validators\ProfesionalesValidator;
use App\Shared\BaseController;
use OpenApi\Attributes as OA;

class ProfesionalesController extends BaseController {

    public function __construct(private ProfesionalesService $service) { }

    #[OA\Get(
        path: "/profesionales",
        summary: "Listado de los profesionales",
        tags: ["Profesionales"]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de los profesionales",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/RespuestaProfesional")
        )
    )]
    public function obtenerTodos() {
        try {
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

            $paginated = $this->service->obtenerTodos($page, $limit);

            return $this->paginatedResponse(200, $paginated['data'], $paginated['total'], $page, $limit);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }
    
    #[OA\Get(
        path: "/profesionales/{id}",
        summary: "Obtener un profesional por su id",
        tags: ["Profesionales"],
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
        description: "Existe un profesional con ese ID",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaProfesional")
    )]
    #[OA\Response(
        response: 400,
        description: "ID invalido",
        content: new OA\JsonContent(example:["ERROR" => "El id ingresado no es valido"])
    )]
    #[OA\Response(
        response: 404,
        description: "No se hallo un profesional con ese id",
        content: new OA\JsonContent(example:["ERROR" => "No se encontro un profesional con ese id"])
    )]
    public function obtenerPorId(string $id) {
        try {
            AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);
            ProfesionalesValidator::validarID($id);

            $profesional = $this->service->obtenerPorId((int) $id);

            return $this->jsonResponse(200, $profesional);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    #[OA\Get(
        path: "/profesionales/buscar",
        summary: "Buscar profesionales",
        tags: ["Profesionales"],
    )]
    #[OA\Parameter(
        name: "filtro",
        in: "query",
        required: true,
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Parameter(
        name: "valor",
        in: "query",
        required: true,
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Listado de profesionales obtenidos",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/RespuestaProfesional")
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Solicitud invalida: filtro o valor no ingresado | el filtro no es valido para la busqueda",
        content: new OA\JsonContent(example:["ERROR" => "El filtro ingresado no es valido para la busqueda"])
    )]
    public function obtenerPor() {
        try {
            ProfesionalesValidator::validarParametrosBusqueda($_GET["filtro"] ?? '', $_GET["valor"] ?? '');
                
            $filtro = $_GET["filtro"];
            $valor = $_GET["valor"];
            
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
            
            $paginated = $this->service->obtenerPor($filtro, $valor, $page, $limit);
            
            return $this->paginatedResponse(200, $paginated['data'], $paginated['total'], $page, $limit);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    #[OA\Post(
        path: "/profesionales/registrar",
        summary: "Registrar un profesional",
        tags: ["Profesionales"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/CrearProfesionalRequest")
    )]
    #[OA\Response(
        response: 201,
        description: "Profesional registrado correctamente",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaProfesional")
    )]
    #[OA\Response(
        response: 400,
        description: "Solicitud erronea: Campos invalidos | formato de contraseña incorrecto",
        content: new OA\JsonContent(example:["ERROR" => "La contraseña debe tener un caracter especial!"])
    )]
    #[OA\Response(
        response: 409,
        description: "Conflicto: Usuario existente!",
        content: new OA\JsonContent(example:["ERROR" => "Ya existe un usuario registrado con ese email y/o telefono ingresado/s"])
    )]
    public function registrarProfesional() {
        try {
            AuthMiddleware::handle([Roles::ADMIN]);
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            ProfesionalesValidator::validarRequestCrear($input);
            $profesionalCreado = $this->service->registrarProfesional(ProfesionalMapper::toRequestCrear($input));
    
            return $this->jsonResponse(201, $profesionalCreado);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    #[OA\Patch(
        path: "/profesionales/{id}",
        summary: "Actualizar datos del profesional",
        tags: ["Profesionales"],
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
        content: new OA\JsonContent(ref: "#/components/schemas/ActualizarProfesionalRequest")
    )]
    #[OA\Response(
        response: 200,
        description: "Profesional actualizado correctamente",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaProfesional")
    )]
    #[OA\Response(
        response: 400,
        description: "Solicitud erronea: Campos invalidos | ID invalido",
        content: new OA\JsonContent(example:["ERROR" => "JSON invalido"])
    )]
    #[OA\Response(
        response: 404,
        description: "Profesional no encontrado",
        content: new OA\JsonContent(example:["ERROR" => "No se encontro un profesional con ese id"])
    )]
    #[OA\Response(
        response: 409,
        description: "Conflicto: Usuario ya existente con esos datos!",
        content: new OA\JsonContent(example:["ERROR" => "Ya existe un usuario con ese email y/o telefono ingresado/s"])
    )]
    public function actualizarProfesional(string $id) {
        try {
            $usuario = AuthMiddleware::handle([Roles::PROFESIONAL, Roles::ADMIN]);
            ProfesionalesValidator::validarID($id);
    
            $input = json_decode(file_get_contents("php://input"), true) ?? [];
            ProfesionalesValidator::validarRequestActualizar($input);
            
            $profesionalActualizado = $this->service->actualizarProfesional((int) $id, ProfesionalMapper::toRequestActualizar($input), $usuario);
    
            return $this->jsonResponse(200, $profesionalActualizado);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    #[OA\Delete(
        path: "/profesionales/{id}",
        summary: "Baja de un profesional",
        tags: ["Profesionales"],
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
        content: new OA\JsonContent(
            required: ["motivo"],
            properties: [
                new OA\Property(
                    property: "motivo",
                    type: "string",
                    description: "Motivo de la baja del profesional",
                    example: "El profesional ha estado inactivo por mas de 6 meses"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 204,
        description: "Profesional dado de baja",
        content: new OA\JsonContent()
    )]
    #[OA\Response(
        response: 400,
        description: "Solicitud erronea: ID invalido | no hay motivo de baja | motivo muy largo",
        content: new OA\JsonContent(example:["ERROR" => "ID Invalido"])
    )]
    #[OA\Response(
        response: 404,
        description: "Profesional no encontrado",
        content: new OA\JsonContent(example:["ERROR" => "No se encontro un profesional a eliminar con ese id"])
    )]
    public function darDeBajaProfesional(string $id) {
        try {
            AuthMiddleware::handle([Roles::ADMIN]);
            ProfesionalesValidator::validarID($id);
            $input = json_decode(file_get_contents("php://input"), true) ?? [];
            ProfesionalesValidator::validarInputBajaPaciente($input);

            $this->service->darDeBajaProfesional($id, $input["motivo"]);

            return $this->jsonResponse(204, "");
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }
}