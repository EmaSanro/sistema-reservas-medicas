<?php

use App\Repository\ReservasRepository;

class ReservasService {
    private $repo = new ReservasRepository();

    public function enviarNotificacion() {
        try {
            $mailer = MailerConfig::getMailer();
            $reservas = $this->repo->ReservasPendientesNotificacion();
            $ics = new GeneradorIcs();
            foreach($reservas as $reserva) {
                $contenidoIcs = $ics->generarIcs(
                    $reserva["fecha_reserva"],
                    date('Y-m-d H:i:s', strtotime($reserva["fecha_reserva"] . ' +30 minutes')),
                    "Reserva con " . $reserva["profesional"],
                    "Recuerda llegar 10 minutos antes. No olvidar estudios previos si fueron solicitados."
                );
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
                if($enviado) {
                    $this->repo->marcarComoNotificado($reserva["id"]);
                    echo "Correo enviado a {$reserva["email"]}\n";
                } else {
                    echo "Fallo el envio del correo a {$reserva["email"]}\n";
                }
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}