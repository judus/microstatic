<?php

if (!function_exists('view')) {
    /**
     * Simple view renderer
     *
     * @param            $viewPath
     * @param array|null $data
     *
     * @return string
     */
    function view($viewPath, Array $data = null)
    {
        return \MicroStatic\View::render($viewPath, $data);
    }

}