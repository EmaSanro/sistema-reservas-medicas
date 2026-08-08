<?php
namespace App\Routing;

use App\Middleware\AuthMiddleware;
use App\Security\RequestContext;

class Router {
    private array $routes = [];

    /**
     * En todos los verbos, $rolesPermitidos define el acceso a la ruta:
     *   null  -> ruta publica, no se valida el token
     *   []    -> requiere token valido, cualquier rol
     *   [...] -> requiere token valido y alguno de esos roles
     */
    public function get(string $ruta, callable|array $operador, ?array $rolesPermitidos = null) {
        $this->addRoute("GET", $ruta, $operador, $rolesPermitidos);
    }
    public function post(string $ruta, callable|array $operador, ?array $rolesPermitidos = null) {
        $this->addRoute("POST", $ruta, $operador, $rolesPermitidos);
    }
    public function put(string $ruta, callable|array $operador, ?array $rolesPermitidos = null) {
        $this->addRoute("PUT", $ruta, $operador, $rolesPermitidos);
    }
    public function patch(string $ruta, callable|array $operador, ?array $rolesPermitidos = null) {
        $this->addRoute("PATCH", $ruta, $operador, $rolesPermitidos);
    }
    public function delete(string $ruta, callable|array $operador, ?array $rolesPermitidos = null) {
        $this->addRoute("DELETE", $ruta, $operador, $rolesPermitidos);
    }

    private function addRoute(string $verbo, string $ruta, callable|array $operador, ?array $rolesPermitidos) {
        $this->routes[] = [
            "verbo" => $verbo,
            "ruta" => $ruta,
            "operador" => $operador,
            "roles" => $rolesPermitidos
        ];
    }

    public function dispatch() {
        header('Content-Type: application/json; charset=utf-8');
        $verbo = $_SERVER['REQUEST_METHOD'];
        $URL = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        if ($basePath !== '' && str_starts_with($URL, $basePath)) {
            $URL = substr($URL, strlen($basePath));
        }
        $URL = "/" . trim($URL, "/");
        
        foreach($this->routes as $route) {
            $pattern = "#^" . preg_replace("/:[a-zA-Z0-9]+/", '([^/]+)', $route["ruta"]) . "$#";

            if($verbo === $route["verbo"] && preg_match($pattern, $URL, $matches)) {
                // Quitamos el primer elemento (que es la URL completa) para quedarnos con los parámetros
                array_shift($matches);

                if($route["roles"] !== null) {
                    RequestContext::setUsuario(AuthMiddleware::handle($route["roles"]));
                }

                $operador = $route["operador"];

                if(is_array($operador)) {
                    [$controller, $method] = $operador;
                    // Pasamos los parámetros extraídos al método del controlador
                    $controller->$method(...$matches);
                } else {
                    // Si es una función anónima, también le pasamos los parámetros
                    $operador(...$matches);
                }
                return;
            }
        }
        http_response_code(404);
        echo json_encode("Error ruta no encontrada $URL", JSON_UNESCAPED_UNICODE);
    }
}
