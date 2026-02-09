<?php
namespace App\Controller;

use App\Middleware\AuthMiddleware;
use App\Model\DTOs\ConsultorioDTO;
use App\Model\Roles;
use App\Security\Validaciones;
use App\Service\ConsultorioService;
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
        AuthMiddleware::handle([Roles::ADMIN]);
        
        $consultorios = $this->service->obtenerConsultorios();
        
        return $this->jsonResponse(200, $consultorios);
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
    public function obtenerConsultorioPorId($id) {
        AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);
        Validaciones::validarID($id);
        
        $consultorio = $this->service->obtenerConsultorio($id);

        return $this->jsonResponse(200, $consultorio);
    }

    #[OA\Post(
        path: "/consultorios",
        summary: "Registrar consultorio",
        tags: ["Consultorios"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(example:"#/components/schemas/Consultorio")
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
        $usuario = AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);

        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarInput($input);

        $dto = ConsultorioDTO::fromArray($input);
        
        $consultorio = $this->service->crearConsultorio($dto, $usuario);
        
        return $this->jsonResponse(201, $consultorio);
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
        content: new OA\JsonContent(example:"#/components/schemas/Consultorio")
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
    public function actualizarConsultorio($id) {
        $usuario = AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);

        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarID($id);
        Validaciones::validarInput($input);

        $dto = ConsultorioDTO::fromArray($input);

        $consultorio = $this->service->actualizarConsultorio($dto, $id, $usuario);
        
        return $this->jsonResponse(200, $consultorio);
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
    public function borrarConsultorio($id) {
        $usuario = AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);
        Validaciones::validarID($id);

        $this->service->borrarConsultorio($id, $usuario);

        return $this->jsonResponse(204, "");
    }
}