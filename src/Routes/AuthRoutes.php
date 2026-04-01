<?php

use App\Auth\Controller\AuthController;
use App\Auth\Repository\AuthRepository;
use App\Auth\Service\AuthService;

$authRepository = new AuthRepository();
$authService = new AuthService($authRepository);
$authController = new AuthController($authService);

$router->post("/api/auth/login", [$authController, "login"]);