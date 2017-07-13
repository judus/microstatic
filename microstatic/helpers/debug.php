<?php

if (!function_exists('show')) {
    /**
     * Debug helper to display arrays or objects to string
     *
     * @param null $data
     * @param null $heading
     * @param bool $print
     *
     * @return string
     */
    function show($data = null, $heading = null, $print = true)
    {
        $backtrace = debug_backtrace();
        (!is_null($data) && !empty($data)) OR $data =
            'Your parameter is NULL or EMPTY in ' . $backtrace[0]['file'] .
            ' at line ' . $backtrace[0]['line'];

        $html = '<div class="debug_show" style="text-align: left; display: block;">';
        $html .= $heading ? '<span>' . $heading : '';
        $html .= $heading ? '</span>' : '';
        $html .= '<pre style="text-align: left">';
        $html .= htmlentities(print_r($data, true));
        $html .= '</pre>';
        $html .= '</div>';

        if (!$print)
            return $html;
        echo $html;
    }
}
