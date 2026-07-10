<?php
namespace App\Reservas\Service;

use App\Auth\Exceptions\ForbiddenException;
use App\Helper\GeneradorIcs;
use App\Profesionales\Exceptions\ProfesionalNotFoundException;
use App\Profesionales\Repository\ProfesionalesRepository;
use App\Reservas\DTOs\Request\ActualizarReservaRequest;
use App\Reservas\DTOs\Request\CrearReservaRequest;
use App\Reservas\DTOs\Response\RespuestaReserva;
use App\Reservas\Exceptions\CancelacionTardiaException;
use App\Reservas\Exceptions\ReservaAlreadyCancelledException;
use App\Reservas\Exceptions\ReservaNotFoundException;
use App\Reservas\Exceptions\UsuarioConReservaException;
use App\Reservas\Mapper\ReservaMapper;
use App\Reservas\Model\Reserva;
use App\Reservas\Repository\ReservasRepository;
use DateInterval;
use DateTime;

class ReservasService {
    public function __construct(
        private ReservasRepository $repo,
        private ProfesionalesRepository $profesionalesRepo,
    ) {}

    public function listar(array $filtros = [], int $page = 1, int $limit = 10): array {
        $paginated = $this->repo->listar($filtros, $page, $limit);
        $paginated['data'] = array_map(fn($reserva) => ReservaMapper::toResponse($reserva), $paginated['data']);
        return $paginated;
    }

    public function obtenerReservasPorUsuarioId(int $id, string $rol, int $page = 1, int $limit = 10): array {
        $paginated = $this->repo->obtenerReservasPorUsuarioId($id, $rol, $page, $limit);
        $paginated['data'] = array_map(fn($reserva) => ReservaMapper::toResponse($reserva), $paginated['data']);

        return $paginated;
    }


    public function reservar(CrearReservaRequest $request): RespuestaReserva {
        $reserva = ReservaMapper::fromRequestCrear($request);

        if(!$this->profesionalesRepo->existePorId($reserva->getIdProfesional())) {
            throw new ProfesionalNotFoundException($reserva->getIdProfesional());
        }

        $conflicto = $this->repo->buscarCoincidencia(
            $reserva->getIdPaciente(),
            $reserva->getIdProfesional(),
            $reserva->getFechaReserva()
        );
        if($conflicto) {
            throw new UsuarioConReservaException(
                $conflicto->getIdPaciente() === $reserva->getIdPaciente()
                    ? "Ya tienes una reserva para esa misma fecha y hora"
                    : "El profesional ya tiene una reserva para esa misma fecha y hora"
            );
        }

        $reservaCreada = $this->repo->reservar($reserva);

        return ReservaMapper::toResponse($reservaCreada);
    }

    public function actualizarReserva(int $id, ActualizarReservaRequest $request): RespuestaReserva {
        /** @var Reserva $reservaExistente */
        $reservaExistente = $this->repo->findById($id);
        if(!$reservaExistente) {
            throw new ReservaNotFoundException($id);
        }
        ReservaMapper::fromRequestActualizar($reservaExistente, $request);

        $conflicto = $this->repo->buscarCoincidencia(
            $reservaExistente->getIdPaciente(),
            $reservaExistente->getIdProfesional(),
            $reservaExistente->getFechaReserva()
        );

        if($conflicto && $conflicto->getId() !== $reservaExistente->getId()) {
            throw new UsuarioConReservaException("Ya existe una reserva para esa misma fecha y hora");
        }

        $reservaActualizada = $this->repo->actualizarReserva($id, $reservaExistente);

        return ReservaMapper::toResponse($reservaActualizada);
    }

    public function cancelarReserva(int $idReserva, mixed $paciente): void {
        if(!$this->repo->perteneceAlPaciente($idReserva, $paciente->id)) {
            throw new ForbiddenException("No puedes cancelar una reserva que no es tuya!");
        }
        /** @var Reserva $reserva */
        $reserva = $this->repo->findById($idReserva);
        $horasMinimas = 24;
        $fechaLimite = new DateTime();
        $fechaLimite->add(new DateInterval("PT{$horasMinimas}H"));
        
        if($reserva->getFechaReserva() < $fechaLimite) {
            throw new CancelacionTardiaException("Solo se pueden cancelar reservas con al menos {$horasMinimas}hs de anticipacion");
        }
        if (!$this->repo->cancelarReserva($reserva)) {
            throw new ReservaAlreadyCancelledException("La reserva ya fue cancelada");
        }
    }

    public function enviarNotificacion(): void {
        try {
            $reservas = $this->repo->ReservasPendientesNotificacion();
            $ics = new GeneradorIcs();
            foreach($reservas as $reserva) {
                $contenidoIcs = $ics->generarIcs(
                    $reserva->getfechaReserva(),
                    date('Y-m-d H:i:s', strtotime($reserva["fecha_reserva"] . ' +30 minutes')),
                    "Reserva con " . $reserva["profesional"],
                    "Recuerda llegar 10 minutos antes. No olvidar estudios previos si fueron solicitados."
                    );
                if($reserva["email"] != "") {
                    $mailService = new MailService();
                    $enviado = $mailService->enviarRecordatorio(
                            $reserva["email"], 
                            $reserva["fecha_reserva"], 
                            $contenidoIcs
                            );
                } else if($reserva["telefono"] != "") {
                    $whatsappService = new WhatsappService();
                    $nombreArchivo = "cita_" . $reserva['id'] . ".ics";
                    $rutaFisica = __DIR__ . "/../../public/temp_ics/" . $nombreArchivo;
                    file_put_contents($rutaFisica, $contenidoIcs);

                    $urlPublica = "https://sistema-reservas.loca.lt/public/temp_ics/" . $nombreArchivo;
                    $enviado = $whatsappService->enviarRecordatorio($reserva["telefono"], $reserva["paciente"], date("d/m/Y", strtotime($reserva["fecha_reserva"])), date("H:i", strtotime($reserva["fecha_reserva"])), $urlPublica);
                } else {
                    throw new \Exception("No hay datos de contacto para la reserva {$reserva["id"]}");
                }
                if($enviado) {
                    $this->repo->marcarComoNotificado($reserva["id"]);
                    echo "Notificacion enviada al paciente\n";
                } else {
                    echo "Fallo el envio de la notificacion\n";
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}