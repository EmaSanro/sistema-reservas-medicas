<?php
namespace App\Controller;

use App\Middleware\AuthMiddleware;
use App\Model\DTOs\CrearNotaDTO;
use App\Model\Roles;
use App\Service\NotaService;

class NotaController extends BaseController {

    public function __construct(private NotaService $service) {}

    public function crearNota() {
        $usuario = AuthMiddleware::handle([Roles::PROFESIONAL]);
        $input = $_POST;

        $nota = CrearNotaDTO::fromArray($input);

        $archivos = $this->procesarArchivos();

        $nota = $this->service->crearNota($nota, $archivos, $usuario);

        return $this->jsonResponse(201, $nota);
    }

    public function obtenerNotaPorId($id) {
        $usuario = AuthMiddleware::handle([Roles::PROFESIONAL]);

        $nota = $this->service->obtenerNotaPorId($id, $usuario);

        return $this->jsonResponse(200, $nota);
    }

    private function procesarArchivos() {
        if(empty($_FILES)) {
            return [];
        }

        $archivos = [];
        $files = $_FILES["archivos"];

        if(!is_array($files["name"])) {
            if($files["error"] === UPLOAD_ERR_OK) {
                $archivos[] = $files;
            }
            return $archivos;
        }

        $count = count($files["name"]);

        for($i = 0; $i < $count; $i++) {
            if($files["error"][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $archivos[] = [
                "name" => $files["name"][$i],
                "type" => $files["type"][$i],
                "tmp_name" => $files["tmp_name"][$i],
                "error" => $files["error"][$i],
                "size" => $files["size"][$i],
            ];
        }

        return $archivos;
    }
}