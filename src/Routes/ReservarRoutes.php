<?php

use App\Controller\ReservasController;
use App\Repository\ReservasRepository;

$reservarRepository = new ReservasRepository();
$reservasController = new ReservasController($reservarRepository);

$router->get("/api/reservas", [$reservasController, "obtenerTodas"]);
$router->get("/api/reservas/profesional/:idProfesional", [$reservasController, "obtenerReservasDeProfesional"]);
$router->get("/api/reservas/paciente/:idPaciente", [$reservasController, "obtenerReservasDePaciente"]);
$router->get("/api/reservas/buscarPor", [$reservasController, "obtenerPor"]);
$router->post("/api/reservas/reservar", [$reservasController, "reservar"]);
$router->delete("/api/reservas/:id", [$reservasController, "cancelarReserva"]);