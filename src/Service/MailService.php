<?php

namespace App\Service;

use AppConfig\MailerConfig;

class MailService {
    private $mailer;
    public function __construct() {
        $this->mailer = MailerConfig::getMailer();
    }

    public function enviarRecordatorio($emailPaciente, $fecha, $contenidoIcs) {
        try {
            $this->mailer->addAddress($emailPaciente);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Recordatorio de Reserva";
            $this->mailer->Body = "Tienes una reserva mañana " . date("d/m/Y", strtotime($fecha)) . " a las " . date("H:i", strtotime($fecha)) . ". Por favor, no olvides asistir.";
            $this->mailer->addStringAttachment(
                $contenidoIcs,
                "reserva-medica.ics",
                "base64",
                "text/calendar"
            );
            return $this->mailer->send();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}