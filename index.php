<?php 
require_once __DIR__ . '/vendor/autoload.php';

// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->load();

$router = require_once __DIR__ . "/src/Routes/Api.php";
$router->dispatch();