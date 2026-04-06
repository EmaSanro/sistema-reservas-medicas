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
                $body["errors"] = $e->getField();
            }
            self::jsonResponse($e->getStatusCode(), $body);
            return;
        }

        // Excepciones no controladas
        self::jsonResponse(500, "Error interno del servidor");

        // Opcional: loggear el error real
        error_log($e->getMessage());
    }

    public static function handleError(int $severity, string $message, string $file, int $line): void {
        // Convertir errores PHP en excepciones
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    private static function jsonResponse(int $statusCode, mixed $message): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        echo json_encode($message);
    }
}