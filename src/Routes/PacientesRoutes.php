<?php

use App\Auth\Model\Roles;
use App\Auth\Repository\AuthRepository;
use App\Pacientes\Controller\PacientesController;
use App\Pacientes\Repository\PacientesRepository;
use App\Pacientes\Service\PacientesService;
use App\Reservas\Repository\ReservasRepository;

$pacientesRepository = new PacientesRepository();
$reservasRepository = new ReservasRepository();
$authRepository = new AuthRepository();
$pacientesService = new PacientesService($pacientesRepository, $reservasRepository, $authRepository);
$pacientesController = new PacientesController($pacientesService);


$router->get("/api/pacientes", [$pacientesController, "listar"], [Roles::ADMIN, Roles::PROFESIONAL]);
$router->get("/api/pacientes/:id", [$pacientesController, "obtenerPorId"], [Roles::ADMIN, Roles::PROFESIONAL]);
$router->post("/api/pacientes/registrar", [$pacientesController, "registrarPaciente"]);
$router->patch("/api/pacientes/:id", [$pacientesController, "actualizarPaciente"], [Roles::PACIENTE, Roles::ADMIN]);
$router->delete("/api/pacientes/:id", [$pacientesController, "eliminarPaciente"], [Roles::ADMIN]);
