<?php
namespace App\Controller;

use OpenApi\Attributes as OA;
#[OA\Info(version: "1.0.0", title: "API Reservas medicas", description: "API para gestionar las reservas medicas")]
abstract class BaseController {
    
    protected function jsonResponse(int $code, mixed $response) {
        http_response_code($code);
        echo json_encode($response);
        exit;
    }
}