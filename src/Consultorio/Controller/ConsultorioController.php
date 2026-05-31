<?php
namespace App\Consultorio\Controller;

use App\Auth\Model\Roles;
use App\Consultorio\Mapper\ConsultorioMapper;
use App\Consultorio\Service\ConsultorioService;
use App\Consultorio\Validators\ConsultorioValidator;
use App\Middleware\AuthMiddleware;
use App\Middleware\ErrorMiddleware;
use App\Shared\BaseController;
use OpenApi\Attributes as OA;

class ConsultorioController extends BaseController {

    public function __construct(private ConsultorioService $service ) { }

    #[OA\Get(
        path: "/consultorios",
        summary: "Listado de consultorios",
        tags: ["Consultorios"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de todos los consultorios registrados",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/RespuestaConsultorio")
        )
    )]
    public function obtenerConsultorios() {
        try {
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

            $paginated = $this->service->obtenerConsultorios($page, $limit);
            
            return $this->paginatedResponse(200, $paginated['data'], $paginated['total'], $page, $limit);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    #[OA\Get(
        path: "/consultorios/{id}",
        summary: "Consultorio obtenido por id",
        tags: ["Consultorios"],
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
        description: "Consultorio obtenido",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaConsultorio")
    )]
    #[OA\Response(
        response: 404,
        description: "Consultorio no encontrado",
        content: new OA\JsonContent(example:["ERROR" => "No se ha encontrado un consultorio con ese id"])
    )]
    public function obtenerConsultorioPorId(string $id) {
        try {
            ConsultorioValidator::validarID($id);
            
            $consultorio = $this->service->obtenerConsultorio((int) $id);
    
            return $this->jsonResponse(200, $consultorio);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    #[OA\Post(
        path: "/consultorios",
        summary: "Registrar consultorio",
        tags: ["Consultorios"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref:"#/components/schemas/CrearConsultorioRequest")
    )]
    #[OA\Response(
        response: 201,
        description: "Consultorio creado",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaConsultorio")
    )]
    #[OA\Response(
        response: 400,
        description: "JSON invalido o datos de registro incompletos/erroneos",
        content: new OA\JsonContent(example:["ERROR" => "Formato de horario invalido"])
    )]
    #[OA\Response(
        response: 409,
        description: "Conflicto: consultorio ya existente",
        content: new OA\JsonContent(example:["ERROR" => "Ya existe un consultorio registrado en esa ciudad y direccion"])
    )] 
    public function crearConsultorio() {
        try {
            $usuario = AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);
    
            $input = json_decode(file_get_contents("php://input"), true) ?? [];
            ConsultorioValidator::validarRequestCrear($input);
    
            $request = ConsultorioMapper::toRequestCrear($input);
            
            $consultorio = $this->service->crearConsultorio($request, $usuario);
            
            return $this->jsonResponse(201, $consultorio);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    #[OA\Put(
        path: "/consultorios/{id}",
        summary: "Actualizar datos de consultorio",
        tags: ["Consultorios"],
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
        content: new OA\JsonContent(ref:"#/components/schemas/ActualizarConsultorioRequest")
    )]
    #[OA\Response(
        response: 200,
        description: "Datos del consultorio actualizados",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaConsultorio")
    )]
    #[OA\Response(
        response: 400,
        description: "Peticion erronea: id invalido | json invalido | formato horario invalido",
        content: new OA\JsonContent(example:["ERROR" => "Datos invalidos"])
    )]
    #[OA\Response(
        response: 404,
        description: "Consultorio no encontrado",
        content: new OA\JsonContent(example:["ERROR" => "No se encontro un consultorio para actualizar con ese id"])
    )]
    #[OA\Response(
        response: 409,
        description: "Conflicto: consultorio existente",
        content: new OA\JsonContent(example:["ERROR" => "Ya existe un consultorio con la direccion y ciudad ingresadas"])
    )]
    public function actualizarConsultorio(string $id) {
        try {
            $usuario = AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);
    
            $input = json_decode(file_get_contents("php://input"), true) ?? [];
            ConsultorioValidator::validarID($id);
            ConsultorioValidator::validarRequestActualizar($input);
    
            $request = ConsultorioMapper::toRequestActualizar($input);
    
            $consultorio = $this->service->actualizarConsultorio($request, (int) $id, $usuario);
            
            return $this->jsonResponse(200, $consultorio);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    #[OA\Delete(
        path: "/consultorios/{id}",
        summary: "Eliminar consultorio",
        tags: ["Consultorios"],
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
        description: "Consultorio eliminado",
        content: new OA\JsonContent()
    )]
    #[OA\Response(
        response: 400,
        description: "ID invalido",
        content: new OA\JsonContent(example:["ERROR" => "El id ingresado es invalido"])
    )]
    #[OA\Response(
        response: 404,
        description: "Consultorio no encontrado",
        content: new OA\JsonContent(example:["ERROR" => "No se encontro un consultorio para eliminar"])
    )]
    public function borrarConsultorio(string $id) {
        try {
            $usuario = AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);
            ConsultorioValidator::validarID($id);
    
            $this->service->borrarConsultorio((int) $id, $usuario);
    
            return $this->jsonResponse(204, "");
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }
}