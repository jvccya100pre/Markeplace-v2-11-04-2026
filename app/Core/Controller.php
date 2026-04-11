<?php

class Controller
{
    protected function render($view, $data = array())
    {
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'Vista no encontrada';
            exit;
        }

        extract($data);
        require __DIR__ . '/../../includes/header.php';
        require $viewFile;
        require __DIR__ . '/../../includes/footer.php';
    }
}
