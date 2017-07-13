<?php namespace MicroStatic;

/**
 * Class Middleware
 *
 * @package MicroStatic
 */
class Micro
{
    /**
     * @param $config
     * @param $closure
     *
     * @return bool
     * @internal param null $route
     *
     */
    public static function run($config, $closure)
    {
        Config::init($config['config'], $config['path']);

        Router::init();

        $closure();

        $route = Router::getRoute();

        Response::setContent(
            Middleware::dispatch(function () use ($route) {
                return FrontController::dispatch($route);
            }, $route->getMiddlewares())
        );

        Response::send();
    }

}