<?php

class BaseController
{
    protected function render($view, $data = array(), $useLayout = true)
    {
        extract($data);

        if ($useLayout) {
            require dirname(__DIR__, 2) . '/includes/header.php';
        }

        require dirname(__DIR__) . '/Views/' . $view . '.php';

        if ($useLayout) {
            require dirname(__DIR__, 2) . '/includes/footer.php';
        }
    }
}
