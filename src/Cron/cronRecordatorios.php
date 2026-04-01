<?php

use App\Cron\Recordatorios;
use App\Reservas\Repository\ReservasRepository;
use App\Reservas\Service\ReservasService;

require_once "vendor/autoload.php";

$reservasRepository = new ReservasRepository();
$service = new ReservasService($reservasRepository);
$cron = new Recordatorios($service);

// 2. Corres la tarea
$cron->enviarNotificaciones();