<?php

use App\Controller\AuthController;
use App\Repository\AuthRepository;

$authRepository = new AuthRepository();
$authController = new AuthController($authRepository);

$router->post("/api/auth/login", [$authController, "login"]);