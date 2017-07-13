<?php namespace MicroStatic;

use MicroStatic\Exception as ClassNotExistsException;
use MicroStatic\Exception as MethodNotExistsException;

/**
 * Class Middleware
 *
 * @package MicroStatic
 */
class FrontController
{
    /**
     * @param null $route
     *
     * @return bool
     * @throws Exception
     */
    public static function dispatch($route = null)
    {
        /** @var Route $route */
        if (!class_exists($route->getController())) {
            throw new ClassNotExistsException(
                "Class '" . $route->getController() . "' does not exist."
            );
        }

        if (!method_exists($route->getController(), $route->getAction())) {
            throw new MethodNotExistsException(
                "Method '" . $route->getAction() . "' does not exist in "
                . $route->getController()
            );
        }

        $params = $route->getParams() ? $route->getParams() : [];

        return call_user_func_array(
            [IOC::make($route->getController()), $route->getAction()],
            $route->getParams()
        );
    }

}