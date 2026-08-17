<?php

use App\Auth\Controller\AuthController;
use App\Auth\Repository\AccessAttemptRepository;
use App\Auth\Repository\AuthRepository;
use App\Auth\Service\AuthService;
use App\Auth\Service\RateLimiter;

$authRepository = new AuthRepository();
$rateLimiter = new RateLimiter(new AccessAttemptRepository());
$authService = new AuthService($authRepository, $rateLimiter);
$authController = new AuthController($authService);

$router->post("/api/auth/login", [$authController, "login"]);