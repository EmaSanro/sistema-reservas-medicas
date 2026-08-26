<?php

namespace App\Reservas\Service;

use AppConfig\MailerConfig;
use PHPMailer\PHPMailer\PHPMailer;

class MailService {
    private PHPMailer $mailer;

    public function __construct() {
        $this->mailer = MailerConfig::getMailer();
    }

    public function enviarRecordatorio(string $emailPaciente, string $fecha, string $contenidoIcs): bool {
        // PHPMailer acumula destinatarios y adjuntos entre llamadas; hay que
        // limpiar para que la misma instancia pueda usarse en varios envíos.
        $this->mailer->clearAllRecipients();
        $this->mailer->clearAttachments();

        $this->mailer->addAddress($emailPaciente);
        $this->mailer->isHTML(true);
        $this->mailer->Subject = "Recordatorio de Reserva";
        $this->mailer->Body = "Tienes una reserva mañana "
            . date("d/m/Y", strtotime($fecha))
            . " a las " . date("H:i", strtotime($fecha))
            . ". Por favor, no olvides asistir.";
        $this->mailer->addStringAttachment(
            $contenidoIcs,
            "reserva-medica.ics",
            "base64",
            "text/calendar"
        );

        return $this->mailer->send();
    }
}