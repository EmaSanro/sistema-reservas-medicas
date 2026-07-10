<?php
namespace App\Shared; // CORREGIR NAMESPACE ACA Y EN LOS DEMAS CONTROLADORES

use App\Security\RequestContext;
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

    protected const PARAMS_PAGINACION = ['page', 'limit'];
    protected const PAGE_DEFAULT = 1;
    protected const LIMIT_DEFAULT = 10;
    protected const LIMIT_MAX = 100;

    /**
     * Usuario autenticado por el Router. Solo disponible en rutas registradas con roles.
     */
    protected function usuarioAutenticado(): mixed {
        return RequestContext::usuario();
    }

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

    /**
     * Returns $_GET without the reserved pagination keys.
     *
     * @return array<string, mixed>
     */
    protected function extractFilterParams(): array {
        return array_diff_key($_GET, array_flip(self::PARAMS_PAGINACION));
    }

    /**
     * Reads `page` and `limit` from $_GET applying safe defaults and upper bounds.
     *
     * @return array{page: int, limit: int}
     */
    protected function extractPagination(): array {
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : self::PAGE_DEFAULT;
        $limit = isset($_GET['limit'])
            ? min(self::LIMIT_MAX, max(1, (int) $_GET['limit']))
            : self::LIMIT_DEFAULT;
        return ['page' => $page, 'limit' => $limit];
    }
}