<?php namespace MicroStatic;

/**
 * Class Middleware
 *
 * @package MicroStatic
 */
class Middleware
{
    /**
     * @param \Closure $task
     *
     * @param array    $middlewares
     *
     * @return mixed
     */
    public static function dispatch(\Closure $task, $middlewares = [])
    {
        $beforeResponse = self::before($middlewares);

        if ($beforeResponse === false || $beforeResponse !== true) {
            return $beforeResponse;
        }

        $response = $task();

        $afterResponse = self::after($middlewares, $response);

        if ($afterResponse === false || $afterResponse !== true) {
            return $afterResponse;
        }

        return $response;
    }

    /**
     * @param array $middlewares
     *
     * @return bool
     */
    public static function before($middlewares)
    {
        return self::execute('before', $middlewares);
    }

    /**
     * @param array $middlewares
     * @param       $response
     *
     * @return bool
     */
    public static function after($middlewares, $response)
    {
        return self::execute('after', $middlewares, $response);
    }

    /**
     * @param      $when
     * @param      $middlewares
     * @param null $response
     *
     * @return bool|null
     */
    protected static function execute($when, $middlewares, $response = null)
    {
        foreach ($middlewares as $key => $middleware) {

            $parameters = [];

            if (is_array($middleware)) {
                $parameters = $middleware;
                $middleware = $key;
            }
            if (class_exists($middleware)) {
                if ($response) {
                    array_push($parameters, $response);
                }

                $middleware = IOC::make($middleware, $parameters);
                $response = $middleware->{$when}();

                if (!is_null($response) &&
                    ($response === false || $response !== true)
                ) {
                    return $response;
                }
            }
        }

        return true;
    }

}