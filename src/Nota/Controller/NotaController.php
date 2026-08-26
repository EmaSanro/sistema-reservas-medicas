<?php
namespace App\Nota\Controller;

use App\Middleware\ErrorMiddleware;
use App\Nota\Mapper\NotaMapper;
use App\Nota\Service\ArchivoNotaService;
use App\Nota\Service\NotaService;
use App\Nota\Validators\NotaValidator;
use App\Shared\BaseController;
use OpenApi\Attributes as OA;

class NotaController extends BaseController {

    public function __construct(private NotaService $service, private ArchivoNotaService $archivoService) {}

    #[OA\Post(
        path: "/notas",
        summary: "Crear una nota sobre una reserva propia",
        description: "Solo el profesional dueño de la reserva puede crear la nota. La respuesta separa la nota guardada de los adjuntos que fallaron.",
        tags: ["Notas"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/CrearNotaRequest")
    )]
    #[OA\Response(
        response: 201,
        description: "Nota creada",
        content: new OA\JsonContent(ref: "#/components/schemas/ResultadoMutacionNota")
    )]
    #[OA\Response(
        response: 400,
        description: "Campo obligatorio faltante o invalido",
        content: new OA\JsonContent(example: [
            "message" => "Los datos ingresados no son válidos.",
            "errors" => ["motivo_visita" => ["El motivo de visita solo puede contener letras, números y espacios."]]
        ])
    )]
    #[OA\Response(
        response: 403,
        description: "La reserva pertenece a otro profesional",
        content: new OA\JsonContent(example: ["message" => "No tienes permisos de crear notas en una reserva ajena!"])
    )]
    #[OA\Response(
        response: 404,
        description: "La reserva indicada no existe",
        content: new OA\JsonContent(example: ["message" => "Reserva con identificador '123' no encontrado"])
    )]
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

    #[OA\Get(
        path: "/notas/{id}",
        summary: "Nota por ID, con sus adjuntos",
        tags: ["Notas"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Nota encontrada",
        content: new OA\JsonContent(ref: "#/components/schemas/RespuestaNota")
    )]
    #[OA\Response(
        response: 400,
        description: "ID invalido",
        content: new OA\JsonContent(example: [
            "message" => "Los datos ingresados no son válidos.",
            "errors" => ["id" => ["El ID debe ser un número entero positivo."]]
        ])
    )]
    #[OA\Response(
        response: 403,
        description: "La nota pertenece a la reserva de otro profesional",
        content: new OA\JsonContent(example: ["message" => "No tienes permisos sobre esta nota"])
    )]
    #[OA\Response(
        response: 404,
        description: "No existe una nota con ese ID",
        content: new OA\JsonContent(example: ["message" => "Nota con identificador '5' no encontrado"])
    )]
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

    #[OA\Put(
        path: "/notas/{id}",
        summary: "Actualizar una nota propia",
        description: "Los campos omitidos conservan su valor. La respuesta devuelve la nota con todos sus adjuntos actuales.",
        tags: ["Notas"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/ActualizarNotaRequest")
    )]
    #[OA\Response(
        response: 200,
        description: "Nota actualizada",
        content: new OA\JsonContent(ref: "#/components/schemas/ResultadoMutacionNota")
    )]
    #[OA\Response(
        response: 400,
        description: "ID o campo/s invalido/s",
        content: new OA\JsonContent(example: [
            "message" => "Los datos ingresados no son válidos.",
            "errors" => ["motivo_visita" => ["El motivo de visita solo puede contener letras, números y espacios."]]
        ])
    )]
    #[OA\Response(
        response: 403,
        description: "La nota pertenece a la reserva de otro profesional",
        content: new OA\JsonContent(example: ["message" => "No tienes permisos sobre esta nota"])
    )]
    #[OA\Response(
        response: 404,
        description: "No existe una nota con ese ID",
        content: new OA\JsonContent(example: ["message" => "Nota con identificador '5' no encontrado"])
    )]
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

    #[OA\Get(
        path: "/notas/{idNota}/archivos/{idArchivo}",
        summary: "Descargar un adjunto de una nota propia",
        description: "Devuelve el binario del archivo. Sin query params se envia como descarga (Content-Disposition: attachment); con ?preview se envia para mostrar en el navegador (inline).",
        tags: ["Notas"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Parameter(
        name: "idNota",
        in: "path",
        required: true,
        description: "ID de la nota dueña del archivo",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Parameter(
        name: "idArchivo",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Parameter(
        name: "preview",
        in: "query",
        required: false,
        description: "Presente (con cualquier valor) devuelve el archivo inline en vez de como descarga",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Contenido binario del archivo",
        headers: [
            new OA\Header(
                header: "Content-Disposition",
                description: "attachment o inline, segun el query param preview",
                schema: new OA\Schema(type: "string")
            )
        ],
        content: [
            new OA\MediaType(
                mediaType: "application/pdf",
                schema: new OA\Schema(type: "string", format: "binary")
            ),
            new OA\MediaType(
                mediaType: "image/jpeg",
                schema: new OA\Schema(type: "string", format: "binary")
            ),
            new OA\MediaType(
                mediaType: "image/png",
                schema: new OA\Schema(type: "string", format: "binary")
            ),
        ]
    )]
    #[OA\Response(
        response: 400,
        description: "ID de nota o de archivo invalido",
        content: new OA\JsonContent(example: [
            "message" => "Los datos ingresados no son válidos.",
            "errors" => ["id" => ["El ID debe ser un número entero positivo."]]
        ])
    )]
    #[OA\Response(
        response: 403,
        description: "El archivo pertenece a la nota de otro profesional",
        content: new OA\JsonContent(example: ["message" => "No tienes permisos para descargar archivos que no son tuyos!"])
    )]
    #[OA\Response(
        response: 404,
        description: "No existe el archivo, no pertenece a esa nota, o falta en disco",
        content: new OA\JsonContent(example: ["message" => "Archivo con identificador '7' no encontrado"])
    )]
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

    #[OA\Delete(
        path: "/notas/{idNota}/archivos/{idArchivo}",
        summary: "Eliminar un adjunto de una nota propia",
        description: "Borra el registro y el archivo en disco. La operacion es idempotente respecto del archivo fisico: si ya no existe en disco, igual se elimina el registro.",
        tags: ["Notas"],
        security: [ ["bearerAuth" => []] ]
    )]
    #[OA\Parameter(
        name: "idNota",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Parameter(
        name: "idArchivo",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 204,
        description: "Archivo eliminado correctamente",
        content: new OA\JsonContent()
    )]
    #[OA\Response(
        response: 400,
        description: "ID de nota o de archivo invalido",
        content: new OA\JsonContent(example: [
            "message" => "Los datos ingresados no son válidos.",
            "errors" => ["id" => ["El ID debe ser un número entero positivo."]]
        ])
    )]
    #[OA\Response(
        response: 403,
        description: "El archivo pertenece a la nota de otro profesional",
        content: new OA\JsonContent(example: ["message" => "No puedes eliminar archivos ajenos!"])
    )]
    #[OA\Response(
        response: 404,
        description: "No existe el archivo o no pertenece a esa nota",
        content: new OA\JsonContent(example: ["message" => "Archivo con identificador '7' no encontrado"])
    )]
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