<?php
use App\Controller\ProfesionalesController;
use App\Repository\ProfesionalesRepository;
use App\Repository\ReservasRepository;
use App\Service\ProfesionalesService;

$profesionalesRepository = new ProfesionalesRepository();
$reservasRepository = new ReservasRepository();
$profesionalesService = new ProfesionalesService($profesionalesRepository, $reservasRepository);
$profesionalesController = new ProfesionalesController($profesionalesService);

$router->get('/api/profesionales', [$profesionalesController, "obtenerTodos"]);
$router->get("/api/profesionales/buscar", [$profesionalesController, "obtenerPor"]);
$router->get('/api/profesionales/:id', [$profesionalesController, "obtenerPorId"]);
$router->get('/api/profesionales/profesion/:profesion', [$profesionalesController, "obtenerPorProfesion"]);
$router->get('/api/profesionales/email/:email', [$profesionalesController, "obtenerPorEmail"]);
$router->get('/api/profesionales/telefono/:telefono', [$profesionalesController, "obtenerPorTelefono"]);
$router->post("/api/profesionales/registrar", [$profesionalesController, "registrarProfesional"]);
$router->put("/api/profesionales/:id", [$profesionalesController, "actualizarProfesional"]);
$router->delete("/api/profesionales/:id", [$profesionalesController, "eliminarProfesional"]);