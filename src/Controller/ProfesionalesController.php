<?php
namespace App\Controller;

use App\Model\DTOs\ProfesionalDTO;
use App\Model\DTOs\RespuestaProfesionalDTO;
use App\Repository\ProfesionalesRepository;

class ProfesionalesController {

    public function __construct(private ProfesionalesRepository $repo) { }

    public function obtenerTodos() {
        try {
            $profesionales = $this->repo->obtenerTodos();
            if($profesionales) {
                $profesionalesDTO = array_map(
                    fn(array $prof) => RespuestaProfesionalDTO::fromArray($prof),
                    $profesionales
                );
                http_response_code(200);
                echo json_encode($profesionalesDTO);
            } else {
                http_response_code(404);
                echo json_encode([
                    "ERROR" => "No se han encontrado profesionales"
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
            $prof = $this->repo->obtenerPorId($id);
            if($prof) {
                $dto = RespuestaProfesionalDTO::fromArray($prof);
                http_response_code(200);
                echo json_encode($dto);
            } else {
                http_response_code(404);
                echo json_encode("No hay profesional con ese id");
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "ERROR" => "Error interno del servidor"
            ]);
        }
    }

    public function obtenerPor() {
        if(!isset($_GET["filtro"]) && !isset($_GET["valor"])) {
            http_response_code(400);
            echo json_encode([
                "ERROR" => "Es necesario un filtro y un valor de busqueda"
            ]);
            return;
        }
        $filtro = $_GET["filtro"];
        $valor = $_GET["valor"];
        $columnasPermitidas = ["nombre", "apellido", "profesion", "email", "telefono"];

        if(!in_array($filtro, $columnasPermitidas)) {
            http_response_code(400);
            echo json_encode([
                "ERROR" => "El filtro ingresado no es valido para la busqueda"
            ]);
            return;
        }
        try {
            if($filtro == "profesion") {
                $profs = $this->repo->obtenerPorProfesion($valor);
            } else {
                $profs = $this->repo->buscarPor($filtro, $valor);
            }
            if($profs) {
                $profsDTO = array_map(
                    fn(array $prof) => RespuestaProfesionalDTO::fromArray($prof),
                    $profs
                );
                http_response_code(response_code: 200);
                echo json_encode($profsDTO);
            } else {
                http_response_code(404);
                echo json_encode([
                    "ERROR" => "No se encontro ninguna coincidencia con tu criterio de busqueda"
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "ERROR" => "Error interno del servidor"
            ]);
        }
    }

    public function crearProfesional() {
        $input = json_decode(file_get_contents('php://input'), true);
        Validaciones::validarInput($input);

        $dto = ProfesionalDTO::fromArray($input);

        $coincidencia = $this->repo->buscarCoincidencia($dto);
        if($coincidencia) {
            http_response_code(409);
            echo "Error: Asegurate de que no haya ningun profesional con ese email y/o telefono ya registrado";
            return;
        }
        
        try {
            $prof = $this->repo->crearProfesional($dto);
            if($prof) {
                $dto = RespuestaProfesionalDTO::fromArray($prof);
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

    public function actualizarProfesional($id) {
        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarID($id);
        Validaciones::validarInput($input);

        $dto = ProfesionalDTO::fromArray($input);

        try {
            $coincidencia = $this->repo->buscarCoincidencia($dto);
            if($coincidencia && $coincidencia["id"] != $id) {
                http_response_code(409);
                echo json_encode([
                    "ERROR" => "Ya hay un usuario con ese email/telefono"
                ]);
                return;
            }
            $prof = $this->repo->actualizarProfesional($id, $dto);
            if($prof) {
                http_response_code(200);
                $profesional = $this->repo->obtenerPorId($id);
                $dto = RespuestaProfesionalDTO::fromArray($profesional);
                echo json_encode($dto);
            } else {
                http_response_code(204);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "ERROR" => "Error interno"
            ]);
        }
    }

    public function eliminarProfesional($id) {
        Validaciones::validarID($id);

        try {
            $borrado = $this->repo->eliminarProfesional($id);
            if($borrado) {
                http_response_code(204);
            } else {
                http_response_code(404);
                echo json_encode([
                    "ERROR" => "No hay ningun profesional con ese id"
                ]);
            }
        } catch(\Exception $e) { 
            http_response_code(500);
            echo json_encode([
                "ERROR" => "Error interno del servidor"
            ]);
        }
    }
}