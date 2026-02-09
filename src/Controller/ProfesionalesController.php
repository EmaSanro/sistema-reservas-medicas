<?php
namespace App\Controller;

use App\Middleware\AuthMiddleware;
use App\Model\DTOs\ProfesionalDTO;
use App\Model\Roles;
use App\Security\Validaciones;
use App\Service\ProfesionalesService;
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
        $profesionales = $this->service->obtenerTodos();

        return $this->jsonResponse(200, $profesionales);
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
    public function obtenerPorId($id) {
        AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);
        
        Validaciones::validarID($id);
        
        $prof = $this->service->obtenerPorId($id);

        return $this->jsonResponse(200, $prof);
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
        if(!isset($_GET["filtro"]) || !isset($_GET["valor"])) {
            return $this->jsonResponse(400, ["ERROR" => "Es necesario un filtro y un valor de busqueda"]);
        }
            
        $filtro = $_GET["filtro"];
        $valor = $_GET["valor"];
        
        $profs = $this->service->obtenerPor($filtro, $valor);
        
        return $this->jsonResponse(200, $profs);
    }

    #[OA\Post(
        path: "/profesionales",
        summary: "Registrar un profesional",
        tags: ["Profesionales"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/Profesional")
    )]
    #[OA\Response(
        response: 201,
        description: "Profesional registrado correctamente",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaProfesional")
    )]
    #[OA\Response(
        response: 400,
        description: "Solicitud erronea: JSON invalido | formato de contraseña incorrecto",
        content: new OA\JsonContent(example:["ERROR" => "La contraseña debe tener un caracter especial!"])
    )]
    #[OA\Response(
        response: 409,
        description: "Conflicto: Usuario existente!",
        content: new OA\JsonContent(example:["ERROR" => "Ya existe un usuario registrado con ese email y/o telefono ingresado/s"])
    )]
    public function registrarProfesional() {
        AuthMiddleware::handle([Roles::ADMIN]);
        $input = json_decode(file_get_contents('php://input'), true);
        
        Validaciones::validarInput($input);
        Validaciones::validarCriteriosPassword($input["password"]);
        
        $dto = ProfesionalDTO::fromArray($input);
        $prof = $this->service->registrarProfesional($dto);

        return $this->jsonResponse(201, $prof);
    }

    #[OA\Put(
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
        content: new OA\JsonContent(ref: "#/components/schemas/Profesional")
    )]
    #[OA\Response(
        response: 200,
        description: "Profesional actualizado correctamente",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaProfesional")
    )]
    #[OA\Response(
        response: 400,
        description: "Solicitud erronea: JSON Invalido | ID invalido",
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
    public function actualizarProfesional($id) {
        $usuario = AuthMiddleware::handle([Roles::PROFESIONAL, Roles::ADMIN]);
        Validaciones::validarID($id);

        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarInput($input);
        if(isset($input["password"])) {
            Validaciones::validarCriteriosPassword($input["password"]);
        }
        
        $dto = ProfesionalDTO::fromArray($input);

        $profActualizado = $this->service->actualizarProfesional($id, $dto, $usuario);

        return $this->jsonResponse(200, $profActualizado);
    }

    #[OA\Delete(
        path: "/profesionales/{id}",
        summary: "Eliminar un profesional",
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
        response: 204,
        description: "Profesional eliminado",
        content: new OA\JsonContent()
    )]
    #[OA\Response(
        response: 400,
        description: "Solicitud erronea: ID invalido",
        content: new OA\JsonContent(example:["ERROR" => "ID Invalido"])
    )]
    #[OA\Response(
        response: 404,
        description: "Profesional no encontrado",
        content: new OA\JsonContent(example:["ERROR" => "No se encontro un profesional a eliminar con ese id"])
    )]
    public function eliminarProfesional($id) {
        AuthMiddleware::handle([Roles::ADMIN]);
        Validaciones::validarID($id);

        $this->service->eliminarProfesional($id);

        return $this->jsonResponse(204, "");
    }
}