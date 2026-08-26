<?php
namespace App\Nota\Service;

use App\Auth\Exceptions\ForbiddenException;
use App\Nota\DTOs\Request\ActualizarNotaRequest;
use App\Nota\DTOs\Request\CrearNotaRequest;
use App\Nota\DTOs\Response\AdjuntoFallidoInfo;
use App\Nota\DTOs\Response\RespuestaNota;
use App\Nota\DTOs\Response\ResultadoMutacionNota;
use App\Nota\Exceptions\NotaNotFoundException;
use App\Nota\Mapper\NotaMapper;
use App\Nota\Model\Nota;
use App\Nota\Repository\NotaRepository;
use App\Reservas\Exceptions\ReservaNotFoundException;
use App\Reservas\Model\Reserva;
use App\Reservas\Repository\ReservasRepository;
use Throwable;

class NotaService {

    public function __construct(
        private NotaRepository $repo,
        private ReservasRepository $reservaRepo,
        private ArchivoNotaService $archivoService,
    ) {}

    public function obtenerNotaPorId(int $id, mixed $usuario): RespuestaNota {
        $nota = $this->validarPermisoNota($id, $usuario);
        $adjuntos = $this->archivoService->obtenerPorNotaId($id);
        return NotaMapper::toResponse($nota, $adjuntos);
    }

    public function crearNota(CrearNotaRequest $request, array $archivos, mixed $usuario): ResultadoMutacionNota {
        $nota = NotaMapper::fromRequestCrear($request);

        /** @var Reserva|null $reserva */
        $reserva = $this->reservaRepo->findById($nota->getReservaId());
        if (!$reserva) {
            throw new ReservaNotFoundException($nota->getReservaId());
        }
        if ($usuario->id != $reserva->getIdProfesional()) {
            throw new ForbiddenException("No tienes permisos de crear notas en una reserva ajena!");
        }

        $notaCreada = $this->repo->guardarNota($nota);

        [$adjuntosGuardados, $adjuntosFallidos] = $this->procesarSubidas($notaCreada->getId(), $archivos);

        return new ResultadoMutacionNota(
            NotaMapper::toResponse($notaCreada, $adjuntosGuardados),
            $adjuntosFallidos,
        );
    }

    public function actualizarNota(int $id, ActualizarNotaRequest $request, mixed $usuario, array $archivos): ResultadoMutacionNota {
        $nota = $this->validarPermisoNota($id, $usuario);

        NotaMapper::aplicarActualizacion($nota, $request);

        $notaActualizada = $this->repo->actualizarNota($id, $nota);

        [, $adjuntosFallidos] = $this->procesarSubidas($id, $archivos);

        $adjuntosDeLaNota = $this->archivoService->obtenerPorNotaId($id);

        return new ResultadoMutacionNota(
            NotaMapper::toResponse($notaActualizada, $adjuntosDeLaNota),
            $adjuntosFallidos,
        );
    }

    /**
     * Guarda cada archivo de forma aislada. Un fallo en uno no interrumpe los demas.
     *
     * @return array{0: list<\App\Nota\DTOs\Response\RespuestaArchivoNota>, 1: list<AdjuntoFallidoInfo>}
     */
    private function procesarSubidas(int $notaId, array $archivos): array {
        $guardados = [];
        $fallidos = [];

        foreach ($archivos as $archivo) {
            $nombre = $archivo['name'] ?? 'archivo_sin_nombre';

            if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $fallidos[] = new AdjuntoFallidoInfo($nombre, $this->mensajeErrorSubida($archivo['error'] ?? UPLOAD_ERR_NO_FILE));
                continue;
            }

            try {
                $guardados[] = $this->archivoService->guardarArchivo($notaId, $archivo);
            } catch (Throwable $e) {
                $fallidos[] = new AdjuntoFallidoInfo($nombre, $e->getMessage());
            }
        }

        return [$guardados, $fallidos];
    }

    private function mensajeErrorSubida(int $codigoUploadErr): string {
        return match ($codigoUploadErr) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "El archivo excede el tamaño máximo permitido",
            UPLOAD_ERR_PARTIAL => "El archivo se subió parcialmente",
            UPLOAD_ERR_NO_FILE => "No se recibió ningún archivo",
            UPLOAD_ERR_NO_TMP_DIR => "Falta el directorio temporal en el servidor",
            UPLOAD_ERR_CANT_WRITE => "No se pudo escribir el archivo en disco",
            UPLOAD_ERR_EXTENSION => "Una extensión de PHP bloqueó la subida",
            default => "Error desconocido en la subida",
        };
    }

    private function validarPermisoNota(int $notaId, mixed $usuario): Nota {
        /** @var Nota|null $nota */
        $nota = $this->repo->findById($notaId);
        if (!$nota) {
            throw new NotaNotFoundException($notaId);
        }

        /** @var Reserva|null $reserva */
        $reserva = $this->reservaRepo->findById($nota->getReservaId());
        if (!$reserva) {
            throw new ReservaNotFoundException($nota->getReservaId());
        }

        if ($reserva->getIdProfesional() != $usuario->id) {
            throw new ForbiddenException("No tienes permisos sobre esta nota");
        }

        return $nota;
    }
}
