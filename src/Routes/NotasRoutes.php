<?php

use App\Controller\NotaController;
use App\Repository\ArchivoNotaRepository;
use App\Repository\NotaRepository;
use App\Repository\ReservasRepository;
use App\Service\ArchivoNotaService;
use App\Service\NotaService;

$notaRepository = new NotaRepository();
$reservasRepository = new ReservasRepository();
$archivoNotaRepository = new ArchivoNotaRepository();
$archivoService = new ArchivoNotaService($archivoNotaRepository, $reservasRepository, $notaRepository);
$notaService = new NotaService($notaRepository, $reservasRepository, $archivoService);
$notaController = new NotaController($notaService);

$router->get("/api/notas/:id", [$notaController, "obtenerNotaPorId"]);
$router->post("/api/notas", [$notaController, "crearNota"]);