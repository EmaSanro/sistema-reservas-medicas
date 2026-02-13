<?php
namespace App\Service;

use App\Exceptions\ArchivoNota\ArchivoNotFoundException;
use App\Exceptions\ArchivoNota\SubidaArchivoException;
use App\Exceptions\Auth\ForbiddenException;
use App\Exceptions\DatabaseException;
use App\Exceptions\Nota\NotaNotFoundException;
use App\Model\ArchivoNota;
use App\Model\DTOs\CrearArchivoNotaDTO;
use App\Repository\ArchivoNotaRepository;
use App\Repository\NotaRepository;
use App\Repository\ReservasRepository;
use App\Security\Validaciones;

class ArchivoNotaService {
    private const RUTA_BASE = __DIR__ . '/../../storage/notas_adjuntos/';

    public function __construct(
        private ArchivoNotaRepository $repo,
        private ReservasRepository $reservaRepo,
        private NotaRepository $notaRepo) {}

    public function guardarArchivo(int $idNota, array $archivo) {
        Validaciones::ValidarArchivo($archivo);

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

        $adjunto = new CrearArchivoNotaDTO(
            $archivo["name"],
            $nombreSistema,
            $rutaCompleta,
            $archivo["type"],
            $archivo["size"],
            date("Y-m-d H:i:s"),
            $idNota
        );

        $archivoGuardado = $this->repo->guardarArchivo($adjunto);

        return $archivoGuardado->toDTO();
    }

    public function obtenerPorNotaId(int $idNota) {
        $archivos = $this->repo->obtenerPorNotaId($idNota);
        if(!$archivos) {
            return [];
        }
        return array_map(fn(ArchivoNota $archivo) => $archivo->toDTO(), $archivos);
    }

    public function obtenerArchivoNota(int $idNota, int $idArchivo, $usuario) {
        $archivo = $this->repo->obtenerPorId($idArchivo);
        if(!$archivo) {
            throw new ArchivoNotFoundException("No se encontro el archivo");
        }
        if($archivo->getNotaId() != $idNota) {
            throw new NotaNotFoundException("El archivo no pertenece a esa nota");
        }

        $nota = $this->notaRepo->obtenerNotaPorId($idNota);

        $reserva = $this->reservaRepo->obtenerReserva($nota->getReservaId());
        if($reserva->getIdProfesional() != $usuario->id) {
            throw new ForbiddenException("No tienes permisos para descargar archivos que no son tuyos!");
        }

        return $archivo;
    }

    public function eliminarArchivoNota($id, $idNota, $usuario) {
        $archivo = $this->repo->obtenerPorId($id);

        if(!$archivo) {
            throw new ArchivoNotFoundException("No hay ningun archivo");
        }

        $nota = $this->notaRepo->obtenerNotaPorId($archivo->getNotaId());
        if($nota->getId() != $idNota) {
            throw new NotaNotFoundException("El archivo no pertenece a esta nota");
        }
        
        $reserva = $this->reservaRepo->obtenerReserva($nota->getReservaId());

        if($reserva->getIdProfesional() != $usuario->id) {
            throw new ForbiddenException("No puedes eliminar archivos ajenos!");
        }

        if(file_exists($archivo->getRuta())) {
            unlink($archivo->getRuta());
        }

        $eliminado = $this->repo->eliminarArchivo($id);
        if(!$eliminado) {
            throw new DatabaseException("Error en la base de datos");
        }
    }
}