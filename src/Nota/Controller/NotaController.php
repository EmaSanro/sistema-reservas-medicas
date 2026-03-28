<?php
namespace App\Controller;

use App\Middleware\AuthMiddleware;
use App\Middleware\ErrorMiddleware;
use App\Model\DTOs\ActualizarNotaDTO;
use App\Model\DTOs\CrearNotaDTO;
use App\Model\Roles;
use App\Security\Validaciones;
use App\Service\ArchivoNotaService;
use App\Service\NotaService;

class NotaController extends BaseController {

    public function __construct(private NotaService $service, private ArchivoNotaService $archivoService) {}

    public function crearNota() {
        try {
            $usuario = AuthMiddleware::handle([Roles::PROFESIONAL]);
            $input = $_POST;
    
            $nota = CrearNotaDTO::fromArray($input);
    
            $archivos = $this->procesarArchivos();
    
            $nota = $this->service->crearNota($nota, $archivos, $usuario);
    
            return $this->jsonResponse(201, $nota);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    public function obtenerNotaPorId($id) {
        try {
            Validaciones::validarID($id);
            $usuario = AuthMiddleware::handle([Roles::PROFESIONAL]);
    
            $nota = $this->service->obtenerNotaPorId($id, $usuario);
    
            return $this->jsonResponse(200, $nota);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    public function actualizarNota($id) {
        try {
            Validaciones::validarID($id);
            $usuario = AuthMiddleware::handle([Roles::PROFESIONAL]);
            $input = $_POST;
    
            $actualizarNota = ActualizarNotaDTO::fromArray($input);
    
            $archivos = $this->procesarArchivos();
            
            $nota = $this->service->actualizarNota($id, $actualizarNota, $usuario, $archivos);
    
            return $this->jsonResponse(200, $nota);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    public function obtenerArchivoNota(int $idNota, int $idArchivo) {
        try {
            Validaciones::validarID($idNota);
            Validaciones::validarID($idArchivo);
    
            $usuario = AuthMiddleware::handle([Roles::PROFESIONAL]); 
    
            $archivo = $this->archivoService->obtenerArchivoNota($idNota, $idArchivo, $usuario);
    
            if(!file_exists($archivo->getRuta())) {
                return $this->jsonResponse(404, ["ERROR" => "Archivo no encontrado"]);
            }
    
            $modo = isset($_GET["preview"]) ? "inline" : "attachment";
    
            header("Content-Type: {$archivo->getTipoArchivo()}");
            header("Content-Disposition: $modo; filename='{$archivo->getNombreOriginal()}'");
            header("Content-Length: {$archivo->getPeso()}");
            
            readfile($archivo->getRuta());
            exit;
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    public function eliminarArchivoNota($idNota, $idArchivo) {
        try {
            Validaciones::validarID($idNota);
            Validaciones::validarID($idArchivo);
    
            $usuario = AuthMiddleware::handle([Roles::PROFESIONAL]);
    
            $this->archivoService->eliminarArchivoNota($idArchivo, $idNota, $usuario);
    
            return $this->jsonResponse(204, "");
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
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