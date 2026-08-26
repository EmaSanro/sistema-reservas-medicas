<?php
namespace App\Middleware;

use App\Shared\Exceptions\AppException;
use App\Shared\Exceptions\BusinessValidationException;
use App\Shared\Exceptions\ValidationException;

class ErrorMiddleware {
    public static function handle(): void {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
    }

    public static function handleException(\Throwable $e): void {
        // Si es una excepción de la app
        if ($e instanceof AppException) {
            $body = [
                'message' => $e->getSafeMessage()
            ];
            
            if($e instanceof ValidationException) {
                $body["errors"] = $e->getErrors();
            }

            if($e instanceof BusinessValidationException && $e->getField() !== null) {
                $body["errors"] = [$e->getField() => $e->getSafeMessage()];
            }
            self::jsonResponse($e->getStatusCode(), $body, $e->getHeaders());
            return;
        }

        // Excepciones no controladas
        error_log($e->getMessage());
        self::jsonResponse(500, ['message' => 'Error interno del servidor']);
    }

    public static function handleError(int $severity, string $message, string $file, int $line): void {
        // Convertir errores PHP en excepciones
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * @param array<string, mixed> $body Cuerpo de la respuesta, siempre con la clave "message"
     * @param array<string, string> $headers Headers adicionales (ej. Retry-After en un 429)
     */
    private static function jsonResponse(int $statusCode, array $body, array $headers = []): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        foreach ($headers as $nombre => $valor) {
            header("{$nombre}: {$valor}");
        }

        echo json_encode($body, JSON_UNESCAPED_UNICODE);
    }
}