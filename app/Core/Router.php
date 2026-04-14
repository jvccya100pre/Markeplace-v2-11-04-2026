<?php

class Router
{
    private $routes = array();

    public function get($name, $handler)
    {
        $this->routes[$name] = $handler;
    }

    public function dispatch($name)
    {
        try {
            if (!isset($this->routes[$name])) {
                $name = 'home';
            }

            $handler = $this->routes[$name];

            if (is_array($handler) && count($handler) === 2) {
                $className = $handler[0];
                $method = $handler[1];

                if (!class_exists($className)) {
                    http_response_code(500);
                    echo 'Controlador no encontrado';
                    return;
                }

                if (!method_exists($className, $method)) {
                    http_response_code(500);
                    echo 'Accion no encontrada';
                    return;
                }

                $instance = new $className();
                $instance->$method();
                return;
            }

            if (is_callable($handler)) {
                call_user_func($handler);
                return;
            }

            http_response_code(404);
            echo 'Ruta no encontrada';
        } catch (Throwable $e) {
            error_log('Router dispatch error: ' . $e->getMessage());
            http_response_code(500);
            echo 'Error interno de aplicacion';
        }
    }
}
