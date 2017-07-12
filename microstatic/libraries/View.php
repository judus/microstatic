<?php namespace MicroStatic;

/**
 * Class View
 *
 * @package MicroStatic
 */
class View
{
    /**
     * @var string
     */
    private static $baseDir;

    /**
     * @var string
     */
    private static $fallbackDir;

    /**
     * @return mixed
     */
    public static function getBaseDir()
    {
        return self::$baseDir;
    }

    /**
     * @param $baseDir
     *
     * @return null|string
     */
    public static function setBaseDir($baseDir)
    {
        return self::$baseDir = !empty($baseDir) ?
            rtrim($baseDir, '/') . '/' : null;
    }

    /**
     * @return mixed
     */
    public static function getFallbackDir()
    {
        return self::$fallbackDir;
    }

    /**
     * @param $fallbackDir
     *
     * @return null|string
     */
    public static function setFallbackDir($fallbackDir)
    {
        return self::$fallbackDir = !empty($fallbackDir) ?
            rtrim($fallbackDir, '/') . '/' : null;
    }

    /**
     * @param      $viewPath
     * @param null $data
     *
     * @return string
     */
    public static function render($viewPath, $data = null)
    {
        if ($data) {
            extract($data);
        }

        ob_start();
        if (file_exists(self::getBaseDir() . $viewPath . '.php')) {
            include(self::getBaseDir() . $viewPath . '.php');
        } else {
            include(self::getFallbackDir() . $viewPath . '.php');
        }

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

}