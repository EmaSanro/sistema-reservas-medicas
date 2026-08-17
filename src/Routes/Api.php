<?php
namespace App\Routes;
use App\Routing\Router;

$router = new Router();

require_once __DIR__ . '/ProfesionalRoutes.php';
require_once __DIR__ . '/PacientesRoutes.php';
require_once __DIR__ . '/ReservasRoutes.php';
require_once __DIR__ . '/AuthRoutes.php';
require_once __DIR__ . '/NotasRoutes.php';
require_once __DIR__ . '/ConsultorioRoutes.php';

$router->get("/api/docs", function() {
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $openapiUrl = $basePath . '/api/openapi';

    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../../public/docs.php';
});

$router->get("/api/openapi", function() {
    $spec = __DIR__ . '/../../public/openapi.json';

    if (!is_file($spec)) {
        http_response_code(500);
        echo json_encode("Falta public/openapi.json, generalo con: php generateDocs.php", JSON_UNESCAPED_UNICODE);
        return;
    }

    header('Content-Type: application/json; charset=utf-8');
    readfile($spec);
});

return $router;