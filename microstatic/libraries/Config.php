<?php namespace MicroStatic;

use MicroStatic\Exception as KeyDoesNotExistException;

/**
 * Class Config
 *
 * @package MicroStatic
 */
class Config
{
    /**
     * @var array
     */
    protected static $items = [];

    /**
     * @var bool
     */
    protected static $literal = false;

    /**
     * @return array
     */
    public static function getItems()
    {
        return self::$items;
    }

    /**
     * @param array $items
     *
     * @return Config
     */
    public static function setItems(array $items)
    {
        self::$items = $items;
    }

    /**
     * @return bool
     */
    public static function isLiteral()
    {
        return self::$literal;
    }

    /**
     * @param bool $literal
     *
     * @return Config
     */
    public static function setLiteral(bool $literal)
    {
        self::$literal = $literal;
    }

    /**
     * @param           $name
     * @param null      $value
     * @param null|bool $literal
     *
     * @return mixed
     * @throws KeyDoesNotExistException
     */
    public static function item($name, $value = null, $literal = null)
    {
        $literal = !is_null($literal) ? $literal : self::isLiteral();

        if (func_num_args() > 1) {
            self::$items[$name] = $value;
        }

        if (!$literal) {
            return self::find($name, self::$items);
        }

        isset(self::$items[$name]) || self::throwKeyDoesNotExist($name);

        return self::$items[$name];
    }

    public static function init($config = null, $basePath = null)
    {
        $basePath = $basePath ? $basePath : realpath(__DIR__ .'/../');
        defined('PATH') || define('PATH', $basePath);

        if (is_array($config)) {
            self::setItems($config);
        } else {
            self::file(rtrim($basePath, '/') . '/' . ltrim($config, '/'));
        }

        if (isset(self::$items['errors'])) {
            ini_set('error_reporting',
                self::$items['errors']['error_reporting']);
            ini_set('display_errors', self::$items['errors']['display_errors']);
        }

        if (isset(self::$items['constants'])) {
            foreach (self::$items['constants'] as $key => $path) {
                $key = strtoupper($key);
                defined($key) || define($key, $path);
            }
        }

        if (isset(self::$items['paths'])) {
            foreach (self::$items['paths'] as $key => $path) {
                $key = strtoupper($key);
                defined($key) || define($key, $path);
            }
        }
    }

    public static function file($file)
    {
        if (is_file($file)) {
            /** @noinspection PhpIncludeInspection */
            self::setItems(
                array_merge_recursive(self::getItems(), require_once $file)
            );
        }
    }

    /**
     * @param      $name
     * @param      $array
     * @param null $parent
     *
     * @return mixed
     */
    protected static function find($name, $array, $parent = null)
    {
        list($key, $child) = array_pad(explode('.', $name, 2), 2, null);
        isset($array[$key]) || self::throwKeyDoesNotExist($name);

        return $child ? self::find($child, $array[$key], $name) : $array[$key];
    }

    /**
     * @param $name
     *
     * @throws KeyDoesNotExistException
     */
    protected static function throwKeyDoesNotExist($name)
    {
        throw new KeyDoesNotExistException(
            'Config key \'' . $name . '\' does not exist',
            ['Config' => self::$items]
        );
    }
}