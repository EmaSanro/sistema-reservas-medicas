<?php

use App\Security\Validaciones;
use Twilio\Rest\Client;

class WhatsappService {
    private Client $cliente;
    private string $numeroTwilio;

    public function __construct() {
        $this->cliente = new Client($_ENV["SID_TWILIO"], $_ENV["TOKEN_TWILIO"]);
        $this->numeroTwilio = $_ENV["NUM_TWILIO"];
    }

    public function enviarRecordatorio($telefonoDestino, $paciente, $fecha, $hora) {
        $mensaje = "Hola {$paciente}, recordamos que tienes una reserva para el día {$fecha} a las {$hora}.";
        $this->cliente->messages->create(
            "whatsapp:{$telefonoDestino}",
            [
                "from" => "whatsapp:{$this->numeroTwilio}",
                "body" => $mensaje
            ]
        );
        return true;
    }
}