<?php

use App\Cron\Recordatorios;
use App\Repository\ReservasRepository;
use App\Service\ReservasService;

require_once "vendor/autoload.php";

$reservasRepository = new ReservasRepository();
$service = new ReservasService($reservasRepository);
$cron = new Recordatorios($service);

// 2. Corres la tarea
$cron->enviarNotificaciones();