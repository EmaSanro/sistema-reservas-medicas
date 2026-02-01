<?php

use App\Controller\AuthController;
use App\Repository\AuthRepository;
use App\Service\AuthService;

$authRepository = new AuthRepository();
$authService = new AuthService($authRepository);
$authController = new AuthController($authService);

$router->post("/api/auth/login", [$authController, "login"]);