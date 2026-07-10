<?php

use App\Auth\Model\Roles;
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

$router->get('/api/profesionales', [$profesionalesController, "listar"]);
$router->get('/api/profesionales/:id', [$profesionalesController, "obtenerPorId"], [Roles::ADMIN, Roles::PROFESIONAL]);
// $router->get('/api/profesionales/profesion/:profesion', [$profesionalesController, "obtenerPorProfesion"]);
// $router->get('/api/profesionales/email/:email', [$profesionalesController, "obtenerPorEmail"]);
// $router->get('/api/profesionales/telefono/:telefono', [$profesionalesController, "obtenerPorTelefono"]);
$router->post("/api/profesionales/registrar", [$profesionalesController, "registrarProfesional"], [Roles::ADMIN]);
$router->patch("/api/profesionales/:id", [$profesionalesController, "actualizarProfesional"], [Roles::PROFESIONAL, Roles::ADMIN]);
$router->delete("/api/profesionales/:id", [$profesionalesController, "darDeBajaProfesional"], [Roles::ADMIN]);
