<?php

use App\Auth\Repository\AuthRepository;
use App\Profesionales\Controller\ProfesionalesController;
use App\Profesionales\Repository\ProfesionalesRepository;
use App\Profesionales\Service\ProfesionalesService;
use App\Reservas\Repository\ReservasRepository;

$profesionalesRepository = new ProfesionalesRepository();
$authRepository = new AuthRepository();
$reservasRepository = new ReservasRepository();
$profesionalesService = new ProfesionalesService($profesionalesRepository, $reservasRepository, $authRepository);
$profesionalesController = new ProfesionalesController($profesionalesService);

$router->get('/api/profesionales', [$profesionalesController, "obtenerTodos"]);
$router->get("/api/profesionales/buscar", [$profesionalesController, "obtenerPor"]);
$router->get('/api/profesionales/:id', [$profesionalesController, "obtenerPorId"]);
$router->get('/api/profesionales/profesion/:profesion', [$profesionalesController, "obtenerPorProfesion"]);
$router->get('/api/profesionales/email/:email', [$profesionalesController, "obtenerPorEmail"]);
$router->get('/api/profesionales/telefono/:telefono', [$profesionalesController, "obtenerPorTelefono"]);
$router->post("/api/profesionales/registrar", [$profesionalesController, "registrarProfesional"]);
$router->patch("/api/profesionales/:id", [$profesionalesController, "actualizarProfesional"]);
$router->delete("/api/profesionales/:id", [$profesionalesController, "darDeBajaProfesional"]);