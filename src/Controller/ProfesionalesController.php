<?php
namespace App\Controller;

use App\Model\DTOs\ProfesionalDTO;
use App\Model\DTOs\RespuestaProfesionalDTO;
use App\Model\Roles;
use App\Repository\ProfesionalesRepository;
use App\Security\Validaciones;

class ProfesionalesController extends BaseController {

    public function __construct(private ProfesionalesRepository $repo) { }

    public function obtenerTodos() {
        try {
            $profesionales = $this->repo->obtenerTodos();
            if($profesionales) {
                $profesionalesDTO = array_map(
                    fn(array $prof) => RespuestaProfesionalDTO::fromArray($prof),
                    $profesionales
                );
                return $this->jsonResponse(200, $profesionalesDTO);
            } else {
                return $this->jsonResponse(404, ["ERROR" => "No se han encontrado profesionales"]);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }
    
    public function obtenerPorId($id) {
        $this->autenticar(["Admin"]);
        
        Validaciones::validarID($id);
        
        try {
            $prof = $this->repo->obtenerPorId($id);
            if($prof) {
                $dto = RespuestaProfesionalDTO::fromArray($prof);
                return $this->jsonResponse(200, $dto);
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
        $columnasPermitidas = ["nombre", "apellido", "profesion", "email", "telefono"];
            
        if(!in_array($filtro, $columnasPermitidas)) {
            return $this->jsonResponse(400, ["ERROR" => "El filtro ingresado no es valido para la busqueda"]);
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
                return $this->jsonResponse(200, $profsDTO);
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
        
        $coincidencia = $this->repo->buscarCoincidencia($dto);
        if($coincidencia) {
            return $this->jsonResponse(409, ["ERROR" => "Asegurate de que no haya ningun profesional con ese email y/o telefono ya registrado"]);
        }

        try {
        
            $passwordHash = password_hash($dto->getPassword(), PASSWORD_BCRYPT);
            $prof = $this->repo->registrarProfesional($dto, $passwordHash);
            if($prof) {
                $dto = RespuestaProfesionalDTO::fromArray($prof);
                return $this->jsonResponse(201, $dto);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }

    public function actualizarProfesional($id) {
        $usuario = $this->autenticar(["Profesional", "Admin"]);
        Validaciones::validarID($id);
        if($id != $usuario->id && $usuario->rol != Roles::ADMIN) {
            return $this->jsonResponse(403, ["ERROR" => "No tienes permisos para actualizar un perfil que no sea el tuyo!"]);
        }
        
        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarInput($input);
        
        $dto = ProfesionalDTO::fromArray($input);
        
        $coincidencia = $this->repo->buscarCoincidencia($dto);
        if($coincidencia && $coincidencia["id"] != $id) {
            return $this->jsonResponse(409, ["ERROR" => "Ya hay un usuario con ese email/telefono"]);
        }
        try {
            $actualizado = $this->repo->actualizarProfesional($id, $dto);
            if($actualizado) {
                $profesional = $this->repo->obtenerPorId($id);
                $dto = RespuestaProfesionalDTO::fromArray($profesional);
                return $this->jsonResponse(200, $dto);
            } else {
                return $this->jsonResponse(204, "");
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(500, ["ERROR" => "Error interno del servidor"]);
        }
    }

    public function eliminarProfesional($id) {
        try {
            $this->autenticar(["Admin"]);
            Validaciones::validarID($id);

            $borrado = $this->repo->eliminarProfesional($id);
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