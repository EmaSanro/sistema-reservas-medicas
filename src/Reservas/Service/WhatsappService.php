<?php

namespace App\Reservas\Service;

use RuntimeException;
use Twilio\Rest\Client;

class WhatsappService {
    private readonly Client $cliente;
    private readonly string $numeroTwilio;

    public function __construct() {
        // Fail-fast si la config está incompleta; mejor un error al arrancar
        // el batch que un TypeError críptico dentro del SDK de Twilio.
        $sid = $_ENV['SID_TWILIO'] ?? null;
        $token = $_ENV['TOKEN_TWILIO'] ?? null;
        $numero = $_ENV['NUM_TWILIO'] ?? null;

        if (!$sid || !$token || !$numero) {
            throw new RuntimeException(
                "Configuración de Twilio incompleta. Se requieren SID_TWILIO, TOKEN_TWILIO y NUM_TWILIO."
            );
        }

        $this->cliente = new Client($sid, $token);
        $this->numeroTwilio = $numero;
    }

    /**
     * El SDK de Twilio ya lanza excepciones tipadas (Twilio\Exceptions\*)
     * cuando el envío falla. No las envolvemos: perder el tipo original
     * complica el debug y no aporta nada.
     */
    public function enviarRecordatorio(
        string $telefonoDestino,
        string $paciente,
        string $fecha,
        string $hora,
        string $archivoIcs,
    ): bool {
        $mensaje = "Hola {$paciente}, recordamos que tienes una reserva para el día {$fecha} a las {$hora}. No olvides asistir!";

        $this->cliente->messages->create(
            "whatsapp:{$telefonoDestino}",
            [
                "from" => "whatsapp:{$this->numeroTwilio}",
                "body" => $mensaje,
                "mediaUrl" => [$archivoIcs],
            ]
        );

        return true;
    }
}
