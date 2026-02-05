<?php
namespace App\Controller;

use App\Middleware\AuthMiddleware;
use App\Model\DTOs\PacienteDTO;
use App\Model\Roles;
use App\Security\Validaciones;
use App\Service\PacientesService;

class PacientesController extends BaseController {

    public function __construct(private PacientesService $service) {  }

    public function obtenerTodos() {
        AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);

        $pacientes = $this->service->obtenerTodos();

        return $this->jsonResponse(200, $pacientes);
    }

    public function obtenerPorId($id) {
        AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);
        Validaciones::validarID($id);

        $pac = $this->service->obtenerPorId($id);
        
        return $this->jsonResponse(200, $pac);
    }

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
    public function registrarPaciente() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        Validaciones::validarInput($input);
        Validaciones::validarCriteriosPassword($input["password"]);
        
        $dto = PacienteDTO::fromArray($input);

        $pac = $this->service->registrarPaciente($dto);
        
        return $this->jsonResponse(201, $pac);
    }
        
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

    public function eliminarPaciente($id) {
        AuthMiddleware::handle([Roles::ADMIN]);
        Validaciones::validarID($id);

        $this->service->eliminarPaciente($id);

        return $this->jsonResponse(204, "");
    }
}