<?php

use App\Middleware\ErrorMiddleware;

require_once __DIR__ . '/vendor/autoload.php';

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

ErrorMiddleware::handle();
$router = require_once __DIR__ . "/src/Routes/Api.php";
$router->dispatch();