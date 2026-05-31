<?php
namespace App\Shared; // CORREGIR NAMESPACE ACA Y EN LOS DEMAS CONTROLADORES

use OpenApi\Attributes as OA;
#[OA\Info(version: "1.0.0", title: "API Reservas medicas", description: "API para gestionar las reservas medicas")]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Ingresa tu token JWT aquí para autenticarte"
)]
abstract class BaseController {
    
    protected function jsonResponse(int $code, mixed $response) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function paginatedResponse(int $code, array $data, int $total, int $page, int $limit) {
        $response = [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
        $this->jsonResponse($code, $response);
    }
}