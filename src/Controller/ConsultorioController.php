<?php
namespace App\Controller;

use App\Model\DTOs\ConsultorioDTO;
use App\Security\Validaciones;
use App\Service\ConsultorioService;

class ConsultorioController extends BaseController {

    public function __construct(private ConsultorioService $service ) { }

    public function obtenerConsultorios() {
        $this->autenticar(["Admin"]);
        $consultorios = $this->service->obtenerConsultorios();
        if($consultorios) {
            return $this->jsonResponse(200, $consultorios);
        } else {
            return $this->jsonResponse(404, ["ERROR" => "No se encontraron consultorios"]);
        }
    }

    public function obtenerConsultorioPorId($id) {
        $this->autenticar(["Admin", "Profesional"]);
        Validaciones::validarID($id);
        
        $consultorio = $this->service->obtenerConsultorio($id);
        if($consultorio) {
            return $this->jsonResponse(200, $consultorio);
        } else {
            return $this->jsonResponse(404,["ERROR" => "No existe un consultorio con ese id"]);
        }
    }

    public function crearConsultorio() {
        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarInput($input);

        try {
            $dto = ConsultorioDTO::fromArray($input);
            $consultorio = $this->service->crearConsultorio($dto);
            return $this->jsonResponse(201, $consultorio);
        } catch (\Exception $e) {
            return $this->jsonResponse(400, ["ERROR" => $e->getMessage()]);
        }
    }

    public function actualizarConsultorio($id) {
        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarID($id);
        Validaciones::validarInput($input);

        try {
            $dto = ConsultorioDTO::fromArray($input);
            $consultorio = $this->service->actualizarConsultorio($dto, $id);
            return $this->jsonResponse(200, $consultorio);
        } catch (\Exception $e) {
            return $this->jsonResponse(400, ["ERROR" => $e->getMessage()]);
        }
    }

    public function borrarConsultorio($id) {
        Validaciones::validarID($id);

        try {
            $borrado = $this->service->borrarConsultorio($id);
            if($borrado) {
                return $this->jsonResponse(204, "");
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(400, ["ERROR" => $e->getMessage()]);
        }
    }
}