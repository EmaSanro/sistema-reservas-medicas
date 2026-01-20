<?php
namespace App\Controller;

use App\Model\DTOs\PacienteDTO;
use App\Model\DTOs\RespuestaPacienteDTO;
use App\Repository\PacientesRepository;

class PacientesController {

    public function __construct(private PacientesRepository $repo) {  }

    public function obtenerTodos() {
        try {
            $pacientes = $this->repo->obtenerTodos();
            if($pacientes) {
                $pacientesDTO = array_map(
                    fn(array $paciente) => RespuestaPacienteDTO::fromArray($paciente),
                    $pacientes
                );
                http_response_code(200);
                echo json_encode($pacientesDTO);
            } else {
                http_response_code(404);
                echo json_encode([
                    "ERROR" => "No se han encontrado pacientes"
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "ERROR" => "Error interno del servidor"
            ]);
        }
    }

    public function obtenerPorId($id) {
        Validaciones::validarID($id);
        try {
            $pac = $this->repo->obtenerPorId($id);
            if($pac) {
                $dto = RespuestaPacienteDTO::fromArray($pac);
                http_response_code(200);
                echo json_encode($dto); 
            } else {
                http_response_code(404);
                echo json_encode([
                    "ERROR" => "No se ha encontrado un paciente con ese id"
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "ERROR" => "Error interno del servidor"
            ]);
        }
    }

    public function buscarPor() {
        if(!isset($_GET["filtro"], $_GET["valor"])) {
            http_response_code(400);
            echo json_encode([
                "ERROR" => "Es necesario poner un filtro y un valor de busqueda"
            ]);
            return;
        }
        $filtro = $_GET["filtro"];
        $valor = $_GET["valor"];
        $filtrosPermitidos = ["nombre", "apellido", "email", "telefono"];

        if(!in_array($filtro, $filtrosPermitidos)) {
            http_response_code(400);
            echo json_encode([
                "ERROR" => "El filtro no esta entre los permitidos(nombre, apellido, email, telefono)"
            ]);
            return;
        }

        try {
            $pacientes = $this->repo->buscarPor($filtro, $valor);
            if($pacientes) {
                $pacientesDTO = array_map(
                    fn(array $paciente) => RespuestaPacienteDTO::fromArray($paciente),
                    $pacientes
                );
                http_response_code(200);
                echo json_encode($pacientesDTO);
            }else {
                http_response_code(404);
                echo json_encode([
                    "ERROR" => "No se encontraron coincidencias con el criterio de busqueda"
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "ERROR" => "Error interno del servidor"
            ]);
        }
    }

    public function crearPaciente() {
        $input = json_decode(file_get_contents('php://input'), true);

        Validaciones::validarInput($input);

        $dto = PacienteDTO::fromArray($input);

        $coincidencia = $this->repo->buscarCoincidencia($dto);
        if($coincidencia) {
            http_response_code(409);
            echo json_encode([
                "ERROR" => "Ya hay un usuario con ese telefono/email"
            ]);
            return;
        }

        try {
            $pac = $this->repo->crearPaciente($dto);
            if($pac) {
                $dto = RespuestaPacienteDTO::fromArray($pac);
                http_response_code(201);
                echo json_encode($dto);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "ERROR" => "Error interno del servidor"
            ]);
        }
    }

    public function actualizarPaciente($id) {
        Validaciones::validarID($id);
        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarInput($input);

        $dto = PacienteDTO::fromArray($input);
        
        try {
            $coincidencia = $this->repo->buscarCoincidencia($dto);
            if($coincidencia && $coincidencia["id"] != $id) {
                http_response_code(409);
                echo json_encode([
                    "ERROR" => "Ya hay un usuario con ese email/telefono"
                ]);
                return;
            }
            $pac = $this->repo->actualizarPaciente($id, $dto);
            if($pac) {
                $dto = RespuestaPacienteDTO::fromArray($pac);
                http_response_code(200);
                echo json_encode($dto);
            } else {
                http_response_code(204);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "ERROR" => "Error interno del servidor"
            ]);
        }
    }

    public function eliminarPaciente($id) {
        Validaciones::validarID($id);
        try {
            $borrado = $this->repo->eliminarPaciente($id);
            if($borrado) {
                http_response_code(204);
            } else {
                http_response_code(404);
                echo json_encode([
                    "ERROR" => "Paciente no encontrado con ese id"
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "ERROR" => "Error interno del servidor"
            ]);
        }
    }
}