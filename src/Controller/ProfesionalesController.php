<?php
namespace App\Controller;

use App\Model\DTOs\ProfesionalDTO;
use App\Model\DTOs\RespuestaProfesionalDTO;
use App\Model\Roles;
use App\Security\Validaciones;
use App\Service\ProfesionalesService;

class ProfesionalesController extends BaseController {

    public function __construct(private ProfesionalesService $service) { }

    public function obtenerTodos() {
        try {
            $profesionales = $this->service->obtenerTodos();
            if($profesionales) {
                return $this->jsonResponse(200, $profesionales);
            } else {
                return $this->jsonResponse(404, ["ERROR" => "No se han encontrado profesionales"]);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }
    
    public function obtenerPorId($id) {
        $this->autenticar(["Admin", "Profesional"]);
        
        Validaciones::validarID($id);
        
        try {
            $prof = $this->service->obtenerPorId($id);
            if($prof) {
                return $this->jsonResponse(200, $prof);
            } else {
                return $this->jsonResponse(404, ["ERROR" => "No hay profesional con ese id"]);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }

    public function obtenerPor() {
        if(!isset($_GET["filtro"]) && !isset($_GET["valor"])) {
            return $this->jsonResponse(400, ["ERROR" => "Es necesario un filtro y un valor de busqueda"]);
        }
            
        $filtro = $_GET["filtro"];
        $valor = $_GET["valor"];
        

        try {
            $profs = $this->service->obtenerPor($filtro, $valor);
            if($profs) {
                return $this->jsonResponse(200, $profs);
            } else {
                return $this->jsonResponse(404, ["ERROR" => "No se encontro ninguna coincidencia con tu criterio de busqueda"]);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }

    public function registrarProfesional() {
        $this->autenticar(["Admin"]);
        $input = json_decode(file_get_contents('php://input'), true);
        
        Validaciones::validarInput($input);
        Validaciones::validarCriteriosPassword($input["password"]);
        
        $dto = ProfesionalDTO::fromArray($input);
        
        try {
            $prof = $this->service->registrarProfesional($dto);
            if($prof) {
                return $this->jsonResponse(201, $prof);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }

    public function actualizarProfesional($id) {
        $usuario = $this->autenticar(["Profesional", "Admin"]);
        Validaciones::validarID($id);

        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarInput($input);
        Validaciones::validarCriteriosPassword($input["password"]);
        
        $dto = ProfesionalDTO::fromArray($input);
        try {
            $profActualizado = $this->service->actualizarProfesional($id, $dto, $usuario);
            if($profActualizado) {
                return $this->jsonResponse(200, $profActualizado);
            } else {
                return $this->jsonResponse(204, "");
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }

    public function eliminarProfesional($id) {
        $this->autenticar(["Admin"]);
        Validaciones::validarID($id);
        try {
            $borrado = $this->service->eliminarProfesional($id);
            if($borrado) {
                return $this->jsonResponse(204, "");
            } else {
                return $this->jsonResponse(404, ["ERROR" => "No hay ningun profesional con ese id"]);
            }
        } catch(\Exception $e) { 
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }
}