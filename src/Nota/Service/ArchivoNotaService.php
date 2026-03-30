<?php
namespace App\Nota\Service;

use App\Auth\Exceptions\ForbiddenException;
use App\Model\Reserva;
use App\Nota\DTOs\Response\RespuestaArchivoNota;
use App\Nota\Exceptions\ArchivoNotFoundException;
use App\Nota\Exceptions\NotaNotFoundException;
use App\Nota\Exceptions\SubidaArchivoException;
use App\Nota\Mapper\ArchivoNotaMapper;
use App\Nota\Model\ArchivoNota;
use App\Nota\Model\Nota;
use App\Nota\Repository\ArchivoNotaRepository;
use App\Nota\Repository\NotaRepository;
use App\Nota\Validators\ArchivoNotaValidator;
use App\Repository\ReservasRepository;

class ArchivoNotaService {
    private const RUTA_BASE = __DIR__ . '/../../storage/notas_adjuntos/';

    public function __construct(
        private ArchivoNotaRepository $repo,
        private ReservasRepository $reservaRepo,
        private NotaRepository $notaRepo
    ) {}

    public function guardarArchivo(int $idNota, array $archivo): RespuestaArchivoNota {
        ArchivoNotaValidator::validarArchivo($archivo);

        // Generar nombre único
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreSistema = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        
        // Crear estructura de carpetas por año/mes
        $year = date('Y');
        $month = date('m');
        $directorioDestino = self::RUTA_BASE . "{$year}/{$month}/";
        
        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0755, true);
        }
        
        $rutaCompleta = "$directorioDestino $nombreSistema";
        
        // Mover archivo
        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            throw new SubidaArchivoException("Error al subir el archivo");
        }

        $adjunto = ArchivoNotaMapper::toArchivoNota([
            "nombreOriginal" => $archivo["name"],
            "nombreSistema" => $nombreSistema,
            "ruta" => $rutaCompleta,
            "tipo" => $archivo["type"],
            "tamanio" => $archivo["size"],
            "fechaSubida" => date("Y-m-d H:i:s"),
            "idNota" => $idNota
        ]);

        $archivoGuardado = $this->repo->guardarArchivo($adjunto);

        return ArchivoNotaMapper::toResponse($archivoGuardado);
    }

    public function obtenerPorNotaId(int $idNota): array {
        $archivos = $this->repo->obtenerPorNotaId($idNota);
        if(!$archivos) {
            return [];
        }
        return array_map(fn($archivo): RespuestaArchivoNota => ArchivoNotaMapper::toResponse($archivo), $archivos);
    }

    public function obtenerArchivoNota(int $idNota, int $idArchivo, mixed $usuario): ArchivoNota {
        /** @var ArchivoNota $archivo */
        $archivo = $this->repo->findById($idArchivo);
        if(!$archivo) {
            throw new ArchivoNotFoundException($idArchivo);
        }
        if($archivo->getNotaId() != $idNota) {
            throw new NotaNotFoundException($idNota);
        }

        /** @var Nota $nota */
        $nota = $this->notaRepo->findById($idNota);
        /** @var Reserva $reserva */
        $reserva = $this->reservaRepo->findById($nota->getReservaId());
        if($reserva->getIdProfesional() != $usuario->id) {
            throw new ForbiddenException("No tienes permisos para descargar archivos que no son tuyos!");
        }

        return $archivo;
    }

    public function eliminarArchivoNota(int $id, int $idNota, mixed $usuario): void {
        /** @var ArchivoNota $archivo */
        $archivo = $this->repo->findById($id);

        if(!$archivo) {
            throw new ArchivoNotFoundException($id);
        }
        /** @var Nota $nota */
        $nota = $this->notaRepo->findById($archivo->getNotaId());

        if($nota->getId() != $idNota) {
            throw new NotaNotFoundException($idNota);
        }
            
        /** @var Reserva $reserva */
        $reserva = $this->reservaRepo->findById($nota->getReservaId());

        if($reserva->getIdProfesional() != $usuario->id) {
            throw new ForbiddenException("No puedes eliminar archivos ajenos!");
        }

        if(file_exists($archivo->getRuta())) {
            unlink($archivo->getRuta());
        }

        $this->repo->eliminarArchivo($id);
    }
}