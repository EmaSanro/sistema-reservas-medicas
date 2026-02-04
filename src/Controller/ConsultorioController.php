<?php
namespace App\Controller;

use App\Middleware\AuthMiddleware;
use App\Model\DTOs\ConsultorioDTO;
use App\Model\Roles;
use App\Security\Validaciones;
use App\Service\ConsultorioService;

class ConsultorioController extends BaseController {

    public function __construct(private ConsultorioService $service ) { }

    public function obtenerConsultorios(): void {
        AuthMiddleware::handle([Roles::ADMIN]);
        
        $consultorios = $this->service->obtenerConsultorios();
        
        return $this->jsonResponse(200, $consultorios);
    }

    public function obtenerConsultorioPorId($id): void {
        AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);
        Validaciones::validarID($id);
        
        $consultorio = $this->service->obtenerConsultorio($id);

        return $this->jsonResponse(200, $consultorio);
    }

    public function crearConsultorio(): void {
        $usuario = AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);

        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarInput($input);

        $dto = ConsultorioDTO::fromArray($input);
        
        $consultorio = $this->service->crearConsultorio($dto, $usuario);
        
        return $this->jsonResponse(201, $consultorio);
    }

    public function actualizarConsultorio($id): void {
        $usuario = AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);

        $input = json_decode(file_get_contents("php://input"), true);
        Validaciones::validarID($id);
        Validaciones::validarInput($input);

        $dto = ConsultorioDTO::fromArray($input);

        $consultorio = $this->service->actualizarConsultorio($dto, $id, $usuario);
        
        return $this->jsonResponse(200, $consultorio);
    }

    public function borrarConsultorio($id): void {
        $usuario = AuthMiddleware::handle([Roles::ADMIN, Roles::PROFESIONAL]);
        Validaciones::validarID($id);

        $this->service->borrarConsultorio($id, $usuario);

        return $this->jsonResponse(204, "");
    }
}