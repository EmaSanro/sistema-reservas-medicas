<?php
namespace App\Service;

use App\Repository\ReservasRepository;
use App\Config\MailerConfig;
use App\Helper\GeneradorIcs;
use PHPMailer\PHPMailer\Exception;

class ReservasService {
    private $repo = new ReservasRepository();

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
                    $mailer = MailerConfig::getMailer();
                    $mailer->addAddress($reserva["email"]);
                    $mailer->isHTML(true);
                    $mailer->Subject = "Recordatorio de Reserva";
                    $mailer->Body = "Tienes una reserva mañana " . date("d/m/Y", strtotime($reserva["fecha_reserva"])) . " a las " . date("H:i", strtotime($reserva["fecha_reserva"])) . ". Por favor, no olvides asistir.";
                    $mailer->addStringAttachment(
                        $contenidoIcs,
                        "reserva-medica.ics",
                        "base64",
                        "text/calendar"
                    );
                    $enviado = $mailer->send();
                } else if($reserva["telefono"] != "") {
                    $whatsappService = new WhatsappService();
                    $nombreArchivo = "cita_" . $reserva['id'] . ".ics";
                    $rutaFisica = __DIR__ . "../../public/temp_ics/" . $nombreArchivo;
                    file_put_contents($rutaFisica, $contenidoIcs);

                    $urlPublica = "https://sistema-reservas.loca.lt/temp_ics/" . $nombreArchivo;
                    $enviado = $whatsappService->enviarRecordatorio($reserva["telefono"], $reserva["nombre"], date("d/m/Y", strtotime($reserva["fecha_reserva"])), date("H:i", strtotime($reserva["fecha_reserva"])), $urlPublica);
                } else {
                    throw new Exception("No hay datos de contacto para la reserva" . $reserva["id"]);
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