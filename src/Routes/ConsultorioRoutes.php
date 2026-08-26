<?php

use App\Auth\Model\Roles;
use App\Consultorio\Controller\ConsultorioController;
use App\Consultorio\Repository\ConsultorioRepository;
use App\Consultorio\Service\ConsultorioService;

$consultorioRepository = new ConsultorioRepository();
$consultorioService = new ConsultorioService($consultorioRepository);
$consultorioController = new ConsultorioController($consultorioService);

$router->get("/api/consultorios", [$consultorioController, "listar"]);
$router->get("/api/consultorios/:id", [$consultorioController, "obtenerConsultorioPorId"]);
$router->post("/api/consultorios", [$consultorioController, "crearConsultorio"], [Roles::ADMIN, Roles::PROFESIONAL]);
$router->put("/api/consultorios/:id", [$consultorioController, "actualizarConsultorio"], [Roles::ADMIN, Roles::PROFESIONAL]);
$router->delete("/api/consultorios/:id", [$consultorioController, "borrarConsultorio"], [Roles::ADMIN, Roles::PROFESIONAL]);
