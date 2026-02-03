<?php

use App\Exceptions\AppException;
use App\Middleware\ErrorMiddleware;

require_once __DIR__ . '/vendor/autoload.php';

// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->load();
ErrorMiddleware::handle();
$router = require_once __DIR__ . "/src/Routes/Api.php";
$router->dispatch();