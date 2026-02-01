<?php
use App\Controller\PacientesController;
use App\Repository\PacientesRepository;
use App\Service\PacientesService;

$pacientesRepository = new PacientesRepository();
$pacientesService = new PacientesService($pacientesRepository);
$pacientesController = new PacientesController($pacientesService);


$router->get("/api/pacientes", [$pacientesController, "obtenerTodos"]);
$router->get("/api/pacientes/buscar", [$pacientesController, "buscarPor"]);
$router->get("/api/pacientes/:id", [$pacientesController, "obtenerPorId"]);
$router->post("/api/pacientes/registrar", [$pacientesController, "registrarPaciente"]);
$router->put("/api/pacientes/:id", [$pacientesController, "actualizarPaciente"]);
$router->delete("/api/pacientes/:id", [$pacientesController, "eliminarPaciente"]);