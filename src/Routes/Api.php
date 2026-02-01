<?php
namespace App\Routes;
use App\Routing\Router;

$router = new Router();

require_once __DIR__ . '/ProfesionalRoutes.php';
require_once __DIR__ . '/PacientesRoutes.php';
require_once __DIR__ . '/ReservasRoutes.php';
require_once __DIR__ . '/AuthRoutes.php';

$router->get("/api/docs", function() {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../../public/docs.php';
});

$router->get("/api/openapi", function() {
    header('Content-Type: application/json');
    echo file_get_contents(__DIR__ . '/../../public/openapi.json');
});

return $router;