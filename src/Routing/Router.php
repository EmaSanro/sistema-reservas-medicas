<?php
namespace App\Routing;
class Router {
    private array $routes = [];

    public function get(string $ruta, callable|array $operador) {
        $this->addRoute("GET", $ruta, $operador);
    }
    public function post(string $ruta, callable|array $operador) {
        $this->addRoute("POST", $ruta, $operador);
    }
    public function put(string $ruta, callable|array $operador) {
        $this->addRoute("PUT", $ruta, $operador);
    }
    public function delete(string $ruta, callable|array $operador) {
        $this->addRoute("DELETE", $ruta, $operador);
    }

    private function addRoute(string $verbo, string $ruta, callable|array $operador) {
        $this->routes[] = [
            "verbo" => $verbo,
            "ruta" => $ruta,
            "operador" => $operador
        ];
    }

    public function dispatch() {
        header('Content-Type: application/json; charset=utf-8');
        $verbo = $_SERVER['REQUEST_METHOD'];
        $URL = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1));
        // 2. Limpiamos la URL quitando el basePath
        // Quedará solo como /api/{URL}
        $URL = "/api".substr($URL, strlen($basePath));
        // Aseguramos que siempre empiece con /
        if (empty($URL)) $URL = '/';
        foreach($this->routes as $route) {
            $pattern = "#^" . preg_replace("/:[a-zA-Z0-9]+/", '([^/]+)', $route["ruta"]) . "$#";

            if($verbo === $route["verbo"] && preg_match($pattern, $URL, $matches)) {
                // Quitamos el primer elemento (que es la URL completa) para quedarnos con los parámetros
                array_shift($matches);
                
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
        echo json_encode('Error ruta no encontrada');
    }
}