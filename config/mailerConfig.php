<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerConfig {

    public static function getMailer() {
        $mail = new PHPMailer(true);
        $SMTP_HOST = $_ENV["STMP_HOST"];
        $SMTP_USER = $_ENV["SMTP_USER"];
        $SMTP_PASS = $_ENV["SMTP_PASS"];
        try {
            $mail->isSMTP();
            $mail->Host = $SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = $SMTP_USER;
            $mail->Password = $SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->setFrom("emanuel.sanroman03@gmail.com","Emanuel San Roman");
            return $mail;
        } catch (\Exception $e) {
            throw new \Exception("Mailer Error: $mail->ErrorInfo");
        }
    }

}