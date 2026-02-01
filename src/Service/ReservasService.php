<?php
namespace App\Service;

use App\Repository\ReservasRepository;
use App\Helper\GeneradorIcs;
use PHPMailer\PHPMailer\Exception;

class ReservasService {
    public function __construct(private ReservasRepository $repo) {}

    public function obtenerTodas() {
        $reservas = $this->repo->obtenerTodas();
        if($reservas) {
            $reservasDTO = array_map(
                    fn($reserva) => $reserva->toDTO(),
                    $reservas
                );
            return $reservasDTO;
        }
        return null;
    }

    public function obtenerReservasPorUsuarioId($id, $rol) {
        $reservas = $this->repo->obtenerReservasPorUsuarioId($id, $rol);
        if($reservas) {
            $reservasDTO = array_map(
                fn($reserva) => $reserva->toDTO(),
                $reservas  
            );
            return $reservasDTO;
        }
        return null;
    }

    public function reservar($dto, $paciente) {
        if($this->repo->buscarCoincidencia($paciente->id, $dto->getIdProfesional(), $dto->getFecha())) {
            throw new \Exception("Lo siento este paciente/profesional ya tiene una reserva para esa misma fecha");
        }

        $reserva = $this->repo->reservar($dto, $paciente->id);
        if($reserva) {
            return $reserva->toDTO();
        }
        return null;
    }

    public function cancelarReserva($idReserva, $paciente) {
        if(!$this->repo->perteneceAlPaciente($idReserva, $paciente->id)) {
            throw new \Exception("No puedes cancelar una reserva que no es tuya!");
        }
        return $this->repo->cancelarReserva($idReserva);
    }

    public function enviarNotificacion() {
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