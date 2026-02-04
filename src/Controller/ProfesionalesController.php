<?php
namespace App\Controller;

use App\Middleware\AuthMiddleware;
use App\Model\DTOs\ProfesionalDTO;
use App\Model\Roles;
use App\Security\Validaciones;
use App\Service\ProfesionalesService;

class ProfesionalesController extends BaseController {

    public function __construct(private ProfesionalesService $service) { }

    public function obtenerTodos() {
        $profesionales = $this->service->obtenerTodos();

        return $this->jsonResponse(200, $profesionales);
    }
    
    public function obtenerPorId($id) {
        AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);
        
        Validaciones::validarID($id);
        
        $prof = $this->service->obtenerPorId($id);

        return $this->jsonResponse(200, $prof);
    }

    public function obtenerPor() {
        if(!isset($_GET["filtro"]) || !isset($_GET["valor"])) {
            return $this->jsonResponse(400, ["ERROR" => "Es necesario un filtro y un valor de busqueda"]);
        }
            
        $filtro = $_GET["filtro"];
        $valor = $_GET["valor"];
        
        $profs = $this->service->obtenerPor($filtro, $valor);
        
        return $this->jsonResponse(200, $profs);
    }

    public function registrarProfesional() {
        AuthMiddleware::handle([Roles::ADMIN]);
        $input = json_decode(file_get_contents('php://input'), true);
        
        Validaciones::validarInput($input);
        Validaciones::validarCriteriosPassword($input["password"]);
        
        $dto = ProfesionalDTO::fromArray($input);
        $prof = $this->service->registrarProfesional($dto);

        return $this->jsonResponse(201, $prof);
    }

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

    public function eliminarProfesional($id) {
        AuthMiddleware::handle([Roles::ADMIN]);
        Validaciones::validarID($id);

        $this->service->eliminarProfesional($id);

        return $this->jsonResponse(204, "");
    }
}