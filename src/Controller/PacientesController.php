<?php
namespace App\Controller;

use App\Model\DTOs\PacienteDTO;
use App\Model\DTOs\RespuestaPacienteDTO;
use App\Model\Roles;
use App\Repository\PacientesRepository;
use App\Security\Validaciones;

class PacientesController extends BaseController {

    public function __construct(private PacientesRepository $repo) {  }

    public function obtenerTodos() {
        try {
            $this->autenticar(["Admin", "Profesional"]);
            $pacientes = $this->repo->obtenerTodos();
            if($pacientes) {
                $pacientesDTO = array_map(
                    fn(array $paciente) => RespuestaPacienteDTO::fromArray($paciente),
                    $pacientes
                );
                return $this->jsonResponse(200, $pacientesDTO);
            } else {
                return $this->jsonResponse(404, ["ERROR" => "No se han encontrado pacientes"]);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }

    public function obtenerPorId($id) {
        try {
            $this->autenticar(["Profesional", "Admin"]);
            Validaciones::validarID($id);
            $pac = $this->repo->obtenerPorId($id);
            if($pac) {
                $dto = RespuestaPacienteDTO::fromArray($pac);
                return $this->jsonResponse(200, $dto);
            } else {
                return $this->jsonResponse(404, ["ERROR" => "No se ha encontrado un paciente con ese id"]);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }

    public function buscarPor() {
        try {
            $this->autenticar(["Admin", "Profesional"]);
            if(!isset($_GET["filtro"], $_GET["valor"])) {
                return $this->jsonResponse(400, ["ERROR" => "Es necesario poner un filtro y un valor de busqueda"]);
            }
            $filtro = $_GET["filtro"];
            $valor = $_GET["valor"];
            $filtrosPermitidos = ["nombre", "apellido", "email", "telefono"];

            if(!in_array($filtro, $filtrosPermitidos)) {
                return $this->jsonResponse(400, ["ERROR" => "El filtro no esta entre los permitidos(nombre, apellido, email, telefono)"]);
            }

            $pacientes = $this->repo->buscarPor($filtro, $valor);
            if($pacientes) {
                $pacientesDTO = array_map(
                    fn(array $paciente) => RespuestaPacienteDTO::fromArray($paciente),
                    $pacientes
                );
                return $this->jsonResponse(200, $pacientesDTO);
            }else {
                return $this->jsonResponse(404, ["ERROR" => "No se encontraron coincidencias con el criterio de busqueda"]);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }
    public function registrarPaciente() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            Validaciones::validarInput($input);
            Validaciones::validarCriteriosPassword($input["password"]);

            $dto = PacienteDTO::fromArray($input);

            $coincidencia = $this->repo->buscarCoincidencia($dto);
            if($coincidencia) {
                return $this->jsonResponse(409, [ "ERROR" => "Ya hay un usuario con ese telefono/email"]);
            }

            $passwordHash = password_hash($dto->getPassword(), PASSWORD_BCRYPT);
            $pac = $this->repo->registrarPaciente($dto, $passwordHash);
            if($pac) {
                $dto = RespuestaPacienteDTO::fromArray($pac);
                return $this->jsonResponse(201, $dto);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
        }
        
    public function actualizarPaciente($id) { // TODO terminar de segurizar los endpoints
        try {
            $usuario = $this->autenticar(["Paciente", "Admin"]);
            Validaciones::validarID($id);
            if($id != $usuario->id && $usuario->rol != Roles::ADMIN) {
                return $this->jsonResponse(403, "No tienes permiso para actualizar datos de otra persona!");
            }
            $input = json_decode(file_get_contents("php://input"), true);
            Validaciones::validarInput($input);

            $dto = PacienteDTO::fromArray($input);
        
            $coincidencia = $this->repo->buscarCoincidencia($dto);
            if($coincidencia && $coincidencia["id"] != $id) {
                return $this->jsonResponse(409, ["ERROR" => "Ya hay un usuario con ese email/telefono"]);
            }

            $pac = $this->repo->actualizarPaciente($id, $dto);
            if($pac) {
                $dto = RespuestaPacienteDTO::fromArray($pac);
                return $this->jsonResponse(200, $dto);
            } else {
                return $this->jsonResponse(204, "");
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }

    public function eliminarPaciente($id) {
        $this->autenticar(["Admin"]);
        Validaciones::validarID($id);
        try {
            $borrado = $this->repo->eliminarPaciente($id);
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