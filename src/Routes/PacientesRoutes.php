<?php
use App\Controller\PacientesController;
use App\Repository\PacientesRepository;

$pacientesRepository = new PacientesRepository();
$pacientesController = new PacientesController($pacientesRepository);


$router->get("/api/pacientes", [$pacientesController, "obtenerTodos"]);
$router->get("/api/pacientes/buscar", [$pacientesController, "buscarPor"]);
$router->get("/api/pacientes/:id", [$pacientesController, "obtenerPorId"]);
$router->post("/api/pacientes", [$pacientesController, "crearPaciente"]);
$router->put("/api/pacientes/:id", [$pacientesController, "actualizarPaciente"]);
$router->delete("/api/pacientes/:id", [$pacientesController, "eliminarPaciente"]);