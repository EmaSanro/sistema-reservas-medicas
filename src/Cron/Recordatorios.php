<?php
namespace App\Cron;

use App\Reservas\Service\ReservasService;

class Recordatorios {
    private $service;

    public function __construct(ReservasService $service) {
        $this->service = $service;
    }

    public function enviarNotificaciones() {
        $this->service->enviarNotificacion();
    }
}