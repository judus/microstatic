<?php namespace MicroStatic;

if (!function_exists('buffer')) {
    /**
     * Simple view renderer
     *
     * @param            $filePath
     * @param array|null $data
     *
     * @return string
     */
    function buffer($filePath, Array $data = null)
    {
        if ($data) {
            extract($data);
        }

        ob_start();
        if (file_exists($filePath)) {
            include($filePath);
        } else {
            die('File \''.$filePath.'\' does not exist');
        }

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

}