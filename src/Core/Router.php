<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function get($path, $controller, $method)
    {
        $this->routes['GET'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function post($path, $controller, $method)
    {
        $this->routes['POST'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function dispatch($requestMethod, $requestUri)
    {
        $uri = parse_url($requestUri, PHP_URL_PATH);

        if (isset($this->routes[$requestMethod][$uri])) {
            $route = $this->routes[$requestMethod][$uri];
            $controllerName = $route['controller'];
            $methodName = $route['method'];

            $controller = new $controllerName();
            return $controller->$methodName();
        }

        http_response_code(404);
        echo "404 - Page Not Found";
    }
}
