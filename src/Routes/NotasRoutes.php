<?php

use App\Nota\Controller\NotaController;
use App\Nota\Repository\ArchivoNotaRepository;
use App\Nota\Repository\NotaRepository;
use App\Nota\Service\ArchivoNotaService;
use App\Nota\Service\NotaService;
use App\Reservas\Repository\ReservasRepository;

$notaRepository = new NotaRepository();
$reservasRepository = new ReservasRepository();
$archivoNotaRepository = new ArchivoNotaRepository();
$archivoService = new ArchivoNotaService($archivoNotaRepository, $reservasRepository, $notaRepository);
$notaService = new NotaService($notaRepository, $reservasRepository, $archivoService);
$notaController = new NotaController($notaService, $archivoService);

$router->get("/api/notas/:id", [$notaController, "obtenerNotaPorId"]);
$router->get("/api/notas/:id/archivos/:idArchivo", [$notaController, "obtenerArchivoNota"]);
$router->post("/api/notas", [$notaController, "crearNota"]);
$router->put("/api/notas/:id", [$notaController, "actualizarNota"]);
$router->delete("/api/notas/:idNota/archivos/:idArchivo", [$notaController, "eliminarArchivoNota"]);