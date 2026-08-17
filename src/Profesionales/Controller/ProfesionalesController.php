<?php
namespace App\Profesionales\Controller;

use App\Middleware\ErrorMiddleware;
use App\Profesionales\Mapper\ProfesionalMapper;
use App\Profesionales\Service\ProfesionalesService;
use App\Profesionales\Validators\ProfesionalesValidator;
use App\Profesionales\Validators\ProfesionalSearchValidator;
use App\Shared\BaseController;
use OpenApi\Attributes as OA;

class ProfesionalesController extends BaseController {

    public function __construct(private ProfesionalesService $service) { }

    #[OA\Get(
        path: "/profesionales",
        summary: "Listado de los profesionales. Opcionalmente se pueden pasar filtros como query params (nombre, apellido, email, telefono, profesion, ciudad, direccion)",
        tags: ["Profesionales"]
    )]
    #[OA\Parameter(name: "nombre", in: "query", required: false, schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "apellido", in: "query", required: false, schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "email", in: "query", required: false, schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "telefono", in: "query", required: false, schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "profesion", in: "query", required: false, schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "ciudad", in: "query", required: false, description: "Filtra por ciudad del consultorio", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "direccion", in: "query", required: false, description: "Filtra por direccion del consultorio", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "limit", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Lista paginada de profesionales (opcionalmente filtrada)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/RespuestaProfesional")
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
            $filtros = ProfesionalSearchValidator::validar($this->extractFilterParams());
            ['page' => $page, 'limit' => $limit] = $this->extractPagination();

            $paginated = $this->service->listar($filtros, $page, $limit);

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
            ProfesionalesValidator::validarID($id);

            $profesional = $this->service->obtenerPorId((int) $id);

            return $this->jsonResponse(200, $profesional);
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
            $usuario = $this->usuarioAutenticado();
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