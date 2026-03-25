<?php
namespace App\Controller;

use App\Middleware\AuthMiddleware;
use App\Model\DTOs\PacienteDTO;
use App\Model\Roles;
use App\Security\Validaciones;
use App\Service\PacientesService;
use OpenApi\Annotations\Tag;
use OpenApi\Attributes as OA;

use function PHPSTORM_META\map;

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
        AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);

        $pacientes = $this->service->obtenerTodos();

        return $this->jsonResponse(200, $pacientes);
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
    public function obtenerPorId($id) {
        AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);
        Validaciones::validarID($id);

        $pac = $this->service->obtenerPorId($id);
        
        return $this->jsonResponse(200, $pac);
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
        AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);

        if(!isset($_GET["filtro"]) || !isset($_GET["valor"])) {
            return $this->jsonResponse(400, ["ERROR" => "Es necesario poner un filtro y un valor de busqueda"]);
        }
        $filtro = $_GET["filtro"];
        $valor = $_GET["valor"];
        
        $pacientes = $this->service->buscarPor($filtro, $valor);

        return $this->jsonResponse(200, $pacientes);
    }

    #[OA\Post(
        path: "/pacientes",
        summary: "Registrarse como paciente",
        tags: ["Pacientes"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(example: "#/components/schemas/Paciente")
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
        $input = json_decode(file_get_contents('php://input'), true);
        
        Validaciones::validarInput($input);
        Validaciones::validarCriteriosPassword($input["password"]);
        
        $dto = PacienteDTO::fromArray($input);

        $pac = $this->service->registrarPaciente($dto);
        
        return $this->jsonResponse(201, $pac);
    }
    
    #[OA\Put(
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
        content: new OA\JsonContent(example: "#/components/schemas/Paciente")
    )]
    #[OA\Response(
        response: 200,
        description: "Datos actualizados",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaPaciente")
    )]
    #[OA\Response(
        response: 400,
        description: "id o json invalido o criterios de contraseña no respetados",
        content: new OA\JsonContent(example:["ERROR" => "JSON invalido"])
    )]
    #[OA\Response(
        response: 409,
        description: "Usuario existente",
        content: new OA\JsonContent(example:["ERROR" => "Ya existe un usuario registrado con ese email y/o telefono"])
    )]
    public function actualizarPaciente($id) {
        $usuario = AuthMiddleware::handle([Roles::PACIENTE, Roles::ADMIN]);
        Validaciones::validarID($id);

        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarInput($input);

        if(isset($input["password"])) {
            Validaciones::validarCriteriosPassword($input["password"]);
        }

        $dto = PacienteDTO::fromArray($input);

        $pac = $this->service->actualizarPaciente($id, $dto, $usuario);
        
        return $this->jsonResponse(200, $pac);
    }
    
    #[OA\Delete(
        path: "/pacientes/{id}",
        summary: "Eliminar paciente",
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
        description: "Paciente eliminado",
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
    public function eliminarPaciente($id) {
        AuthMiddleware::handle([Roles::ADMIN]);
        Validaciones::validarID($id);
        $data = json_decode(file_get_contents("php://input"), true);
        $motivo = $data["motivo"] ?? "";

        if(empty(trim($motivo))) {
            return $this->jsonResponse(400, ["ERROR" => "El motivo de baja es obligatorio!"]);
        }

        $this->service->darDeBajaPaciente($id, $motivo);

        return $this->jsonResponse(204, "");
    }
}