<?php
namespace App\Controller;

use App\Model\DTOs\PacienteDTO;
use App\Security\Validaciones;
use App\Service\PacientesService;

class PacientesController extends BaseController {

    public function __construct(private PacientesService $service) {  }

    public function obtenerTodos() {
        $this->autenticar(["Admin", "Profesional"]);
        try {
            $pacientes = $this->service->obtenerTodos();
            if($pacientes) {
                return $this->jsonResponse(200, $pacientes);
            } else {
                return $this->jsonResponse(404, ["ERROR" => "No se han encontrado pacientes"]);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }

    public function obtenerPorId($id) {
        $this->autenticar(["Profesional", "Admin"]);
        Validaciones::validarID($id);
        try {
            $pac = $this->service->obtenerPorId($id);
            if($pac) {
                return $this->jsonResponse(200, $pac);
            } else {
                return $this->jsonResponse(404, ["ERROR" => "No se ha encontrado un paciente con ese id"]);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }

    public function buscarPor() {
        $this->autenticar(["Admin", "Profesional"]);
        if(!isset($_GET["filtro"], $_GET["valor"])) {
            return $this->jsonResponse(400, ["ERROR" => "Es necesario poner un filtro y un valor de busqueda"]);
        }
        $filtro = $_GET["filtro"];
        $valor = $_GET["valor"];
        
        try {
            $pacientes = $this->service->buscarPor($filtro, $valor);
            if($pacientes) {
                return $this->jsonResponse(200, $pacientes);
            }else {
                return $this->jsonResponse(404, ["ERROR" => "No se encontraron coincidencias con el criterio de busqueda"]);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }
    public function registrarPaciente() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        Validaciones::validarInput($input);
        Validaciones::validarCriteriosPassword($input["password"]);
        
        $dto = PacienteDTO::fromArray($input);
        try {
            $pac = $this->service->registrarPaciente($dto);
            if($pac) {
                return $this->jsonResponse(201, $pac);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
        }
        
    public function actualizarPaciente($id) {
        $usuario = $this->autenticar(["Paciente", "Admin"]);
        Validaciones::validarID($id);

        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarInput($input);

        $dto = PacienteDTO::fromArray($input);
        try {
            $pac = $this->service->actualizarPaciente($id, $dto, $usuario);
            if($pac) {
                return $this->jsonResponse(200, $pac);
            } else {
                return $this->jsonResponse(204, "");
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => $e->getMessage()]);
        }
    }

    public function eliminarPaciente($id) {
        $this->autenticar(["Admin"]);
        Validaciones::validarID($id);
        try {
            $borrado = $this->service->eliminarPaciente($id);
            if($borrado) {
                return $this->jsonResponse(204, "");
            } else {
                return $this->jsonResponse(404, ["ERROR" => "Paciente no encontrado con ese id"]);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }
}