<?php

use App\Controller\ReservasController;
use App\Repository\ReservasRepository;
use App\Service\ReservasService;

$reservasRepository = new ReservasRepository();
$reservasService = new ReservasService($reservasRepository);
$reservasController = new ReservasController($reservasService);

$router->get("/api/reservas", [$reservasController, "obtenerTodas"]);
$router->get("/api/reservas/mis-reservas", [$reservasController, "obtenerReservasPorUsuarioId"]);
$router->get("/api/reservas/profesional/:idProfesional", [$reservasController, "obtenerReservasDeProfesional"]);
$router->get("/api/reservas/paciente/:idPaciente", [$reservasController, "obtenerReservasDePaciente"]);
$router->get("/api/reservas/buscarPor", [$reservasController, "obtenerPor"]);
$router->post("/api/reservas/reservar", [$reservasController, "reservar"]);
$router->delete("/api/reservas/:id", [$reservasController, "cancelarReserva"]);