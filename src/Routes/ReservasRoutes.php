<?php

use App\Auth\Model\Roles;
use App\Profesionales\Repository\ProfesionalesRepository;
use App\Reservas\Controller\ReservasController;
use App\Reservas\Repository\ReservasRepository;
use App\Reservas\Service\ReservasService;

$reservasRepository = new ReservasRepository();
$profesionalesRepository = new ProfesionalesRepository();
$reservasService = new ReservasService($reservasRepository, $profesionalesRepository);
$reservasController = new ReservasController($reservasService);

$router->get("/api/reservas", [$reservasController, "listar"], [Roles::ADMIN]);
$router->get("/api/reservas/mis-reservas", [$reservasController, "obtenerReservasPorUsuarioId"], [Roles::PACIENTE, Roles::PROFESIONAL]);
$router->post("/api/reservas", [$reservasController, "reservar"], [Roles::PACIENTE]);
$router->patch("/api/reservas/:id", [$reservasController, "actualizarReserva"], [Roles::ADMIN, Roles::PROFESIONAL]);
$router->put("/api/reservas/cancelar/:id", [$reservasController, "cancelarReserva"], [Roles::PACIENTE]);
