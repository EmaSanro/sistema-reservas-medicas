<?php
namespace App\Service;

use App\Exceptions\Auth\ForbiddenException;
use App\Exceptions\Reservas\ReservaAlreadyCancelledException;
use App\Exceptions\Reservas\ReservaAlreadyExistsException;
use App\Repository\ReservasRepository;
use App\Helper\GeneradorIcs;
use App\Model\DTOs\RespuestaReservaDTO;
use PHPMailer\PHPMailer\Exception;

class ReservasService {
    public function __construct(private ReservasRepository $repo) {}

    public function obtenerTodas(): array {
        $reservas = $this->repo->obtenerTodas();

        return array_map(fn($reserva) => $reserva->toDTO(), $reservas ?? []);
    }

    public function obtenerReservasPorUsuarioId($id, $rol): array {
        $reservas = $this->repo->obtenerReservasPorUsuarioId($id, $rol);

        return array_map(fn($reserva) => $reserva->toDTO(), $reservas ?? []);
    }

    public function reservar($dto, $paciente): RespuestaReservaDTO {
        if($this->repo->buscarCoincidencia($paciente->id, $dto->getIdProfesional(), $dto->getFecha())) {
            throw new ReservaAlreadyExistsException("Lo siento este paciente/profesional ya tiene una reserva para esa misma fecha");
        }

        $reserva = $this->repo->reservar($dto, $paciente->id);

        return $reserva->toDTO();
    }

    public function cancelarReserva($idReserva, $paciente): void {
        if(!$this->repo->perteneceAlPaciente($idReserva, $paciente->id)) {
            throw new ForbiddenException("No puedes cancelar una reserva que no es tuya!");
        }
        if(!$this->repo->cancelarReserva($idReserva)) {
            throw new ReservaAlreadyCancelledException("Ya fue cancelada esta reserva");
        }
    }

    public function enviarNotificacion(): void {
        try {
            $reservas = $this->repo->ReservasPendientesNotificacion();
            $ics = new GeneradorIcs();
            foreach($reservas as $reserva) {
                $contenidoIcs = $ics->generarIcs(
                    $reserva["fecha_reserva"],
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
                    throw new Exception("No hay datos de contacto para la reserva {$reserva["id"]}");
                }
                if($enviado) {
                    $this->repo->marcarComoNotificado($reserva["id"]);
                    echo "Notificacion enviada al paciente\n";
                } else {
                    echo "Fallo el envio de la notificacion\n";
                }
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}