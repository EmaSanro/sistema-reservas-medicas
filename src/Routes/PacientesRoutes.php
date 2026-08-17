<?php

use App\Auth\Model\Roles;
use App\Auth\Repository\AccessAttemptRepository;
use App\Auth\Repository\AuthRepository;
use App\Auth\Service\RateLimiter;
use App\Pacientes\Controller\PacientesController;
use App\Pacientes\Repository\PacientesRepository;
use App\Pacientes\Service\PacientesService;
use App\Reservas\Repository\ReservasRepository;

$pacientesRepository = new PacientesRepository();
$reservasRepository = new ReservasRepository();
$authRepository = new AuthRepository();
$rateLimiter = new RateLimiter(new AccessAttemptRepository());
$pacientesService = new PacientesService($pacientesRepository, $reservasRepository, $authRepository, $rateLimiter);
$pacientesController = new PacientesController($pacientesService);


$router->get("/api/pacientes", [$pacientesController, "listar"], [Roles::ADMIN, Roles::PROFESIONAL]);
$router->get("/api/pacientes/:id", [$pacientesController, "obtenerPorId"], [Roles::ADMIN, Roles::PROFESIONAL]);
$router->post("/api/pacientes", [$pacientesController, "registrarPaciente"]);
$router->patch("/api/pacientes/:id", [$pacientesController, "actualizarPaciente"], [Roles::PACIENTE, Roles::ADMIN]);
$router->delete("/api/pacientes/:id", [$pacientesController, "eliminarPaciente"], [Roles::ADMIN]);
