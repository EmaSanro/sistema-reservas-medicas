<?php
namespace App\Nota\Controller;

use App\Middleware\ErrorMiddleware;
use App\Nota\Mapper\NotaMapper;
use App\Nota\Service\ArchivoNotaService;
use App\Nota\Service\NotaService;
use App\Nota\Validators\NotaValidator;
use App\Shared\BaseController;

class NotaController extends BaseController {

    public function __construct(private NotaService $service, private ArchivoNotaService $archivoService) {}

    public function crearNota() {
        try {
            $usuario = $this->usuarioAutenticado();
            $input = json_decode(file_get_contents("php://input"), true) ?? [];
            NotaValidator::validarRequestCrear($input);
            $archivos = $this->procesarArchivos();
    
            $nota = $this->service->crearNota(NotaMapper::toRequestCrear($input), $archivos, $usuario);
    
            return $this->jsonResponse(201, $nota);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    public function obtenerNotaPorId(string $id) {
        try {
            $usuario = $this->usuarioAutenticado();

            NotaValidator::validarID($id);

            $nota = $this->service->obtenerNotaPorId((int) $id, $usuario);
    
            return $this->jsonResponse(200, $nota);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    public function actualizarNota(string $id) {
        try {
            $usuario = $this->usuarioAutenticado();
            $input = json_decode(file_get_contents("php://input"), true) ?? [];
            NotaValidator::validarID($id);
            NotaValidator::validarRequestActualizar($input);
    
            $archivos = $this->procesarArchivos();
            
            $notaActualizada = $this->service->actualizarNota((int) $id, NotaMapper::toRequestActualizar($input), $usuario, $archivos);
    
            return $this->jsonResponse(200, $notaActualizada);
        } catch (\Throwable $e) {
            ErrorMiddleware::handleException($e);
        }
    }

    public function obtenerArchivoNota(string $idNota, string $idArchivo) {
        try {
            NotaValidator::validarID($idNota);
            NotaValidator::validarID($idArchivo);
    
            $usuario = $this->usuarioAutenticado();

            $archivo = $this->archivoService->obtenerArchivoNota((int) $idNota, (int) $idArchivo, $usuario);
    
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

    public function eliminarArchivoNota(string $idNota, string $idArchivo) {
        try {
            NotaValidator::validarID($idNota);
            NotaValidator::validarID($idArchivo);
    
            $usuario = $this->usuarioAutenticado();

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