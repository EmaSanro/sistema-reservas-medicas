<?php

use App\Helper\GeneradorIcs;
use App\Reservas\Repository\ReservasRepository;
use App\Reservas\Service\MailService;
use App\Reservas\Service\RecordatorioService;
use App\Reservas\Service\WhatsappService;

require_once __DIR__ . '/../../vendor/autoload.php';

$recordatorioService = new RecordatorioService(
    new ReservasRepository(),
    new MailService(),
    new WhatsappService(),
    new GeneradorIcs(),
);

$recordatorioService->enviarPendientes();
