<?php namespace MicroStatic;

use MicroStatic\Exception as RouteNotFoundException;

/**
 * Class Router
 *
 * @package MicroStatic\Core
 */
class Router 
{
    /**
     * @var Route
     */
    private static $route;

    /**
     * @var Collection
     */
    private static $routes;

    /**
     * @var
     */
    private static $groupUriPrefix;

    /**
     * @var
     */
    private static $groupNamespace;

    /**
     * @var
     */
    private static $groupMiddlewares;

    /**
     * @var array
     */
    private static $groupValues = [];

    /**
     * @var bool
     */
    private static $isClosure = false;


    /**
     * @return Collection
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * @param $path
     */
    public static function setGroupUriPrefix($path)
    {
        self::$groupUriPrefix = is_null($path) || empty($path) ?
            '' : rtrim($path, '/') . '';
    }

    /**
     * @param $path
     */
    public static function setGroupNamespace($path)
    {
        self::$groupNamespace = is_null($path) || empty($path) ?
            '' : rtrim($path, '\\') . '\\';
    }

    /**
     * @return mixed
     */
    public static function getGroupUriPrefix()
    {
        return self::$groupUriPrefix;
    }

    /**
     * @return mixed
     */
    public static function getGroupNamespace()
    {
        return self::$groupNamespace;
    }

    /**
     * @param array $values
     */
    public static function setGroupValues(array $values)
    {
        self::$groupValues = $values;
    }

    /**
     * @return array
     */
    public static function getGroupValues()
    {
        return self::$groupValues;
    }

    /**
     * @param $key
     * @param $value
     */
    public static function addGroupValue($key, $value)
    {
        self::$groupValues[$key] = $value;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public static function getGroupValue($key)
    {
        return self::$groupValues[$key];
    }

    /**
     * @return mixed
     */
    public static function getGroupMiddlewares()
    {

        return is_array(self::$groupMiddlewares) ?
            self::$groupMiddlewares : [self::$groupMiddlewares];
    }

    /**
     * @param mixed $groupMiddlewares
     */
    public static function setGroupMiddlewares($groupMiddlewares)
    {
        self::$groupMiddlewares = $groupMiddlewares;
    }

    /**
     * @param bool|null $bool
     *
     * @return bool
     */
    public static function isClosure($bool = null)
    {
        if (!is_null($bool)) {
            self::$isClosure = $bool;
        }

        return self::$isClosure;
    }

    /**
     * Routes constructor.
     *
     * @param null $file
     */
    public static function init($file = null)
    {
        Request::init();

        self::$route = new Route();
        self::$routes = new Collection();

        self::$routes->add(new Collection(), 'ALL')
                    ->add(new Collection(), 'POST')
                    ->add(new Collection(), 'GET')
                    ->add(new Collection(), 'PUT')
                    ->add(new Collection(), 'PATCH')
                    ->add(new Collection(), 'DELETE');

        /** @noinspection PhpIncludeInspection */
        !$file || require_once $file;
    }

    public static function dump()
    {
        return get_class_vars(get_called_class());
    }

    /**
     * @param                $options
     * @param \Closure       $callback
     */
    public static function group($options, \Closure $callback)
    {
        if (!is_array($options)) {

            self::setGroupUriPrefix($options);

        } else {

            foreach ($options as $key => $value) {
                if (method_exists(__CLASS__, 'setGroup' . ucfirst($key))) {
                    self::{'setGroup' . ucfirst($key)}($value);
                } else {
                    self::addGroupValue($key, $value);
                }
            }

        }

        $callback();

        self::setGroupUriPrefix(null);
        self::setGroupNamespace(null);
        self::setGroupMiddlewares([]);
    }

    /**
     * @param                $pattern
     * @param array|\Closure $callback
     */
    public static function post($pattern, $callback)
    {
        self::register('POST', $pattern, $callback);
    }

    /**
     * @param                $pattern
     * @param array|\Closure $callback
     */
    public static function get($pattern, $callback)
    {
        self::register('GET', $pattern, $callback);
    }

    /**
     * @param                $pattern
     * @param array|\Closure $callback
     */
    public static function put($pattern, $callback)
    {
        self::register('PUT', $pattern, $callback);
    }

    /**
     * @param                $pattern
     * @param array|\Closure $callback
     */
    public static function patch($pattern, $callback)
    {
        self::register('PATCH', $pattern, $callback);
    }

    /**
     * @param                $pattern
     * @param array|\Closure $callback
     */
    public static function delete($pattern, $callback)
    {
        self::register('DELETE', $pattern, $callback);
    }

    /**
     * @param String         $requestMethod
     * @param String         $uriPattern
     * @param array|\Closure $callback
     */
    private static function register(
        $requestMethod,
        $uriPattern,
        $callback
    ) {
        extract(self::getGroupValues());

        self::isClosure(false);

        if (!empty(self::getGroupUriPrefix())) {
            $uriPattern = !empty($uriPattern) ? '/' . ltrim($uriPattern,
                    '/') : $uriPattern;
        }

        // Direct output
        if (is_callable($callback)) {

            self::isClosure(true);

            // If we have a literal match, we execute the closure, send the
            // response and exit PHP
            if (self::matchLiteral(self::getGroupUriPrefix() . $uriPattern)) {
                Response::send($callback());
                Response::close();
            }

            // If we have a wildcard  match, we pass the wildcard value to
            // the closure, execute it, send the response and exit PHP
            if ($matches = self::matchWildcard(self::getGroupUriPrefix() . $uriPattern)) {
                Response::send(
                    call_user_func_array($callback, $matches)
                );
                Response::close();
            }
        }


        if (is_string($callback)) {
            $callback = self::fetchControllerAndAction($callback);
        }

        if (is_array($callback)) {
            extract($callback);
        }

        unset($callback);

        $vars = compact(array_keys(get_defined_vars()));

        $vars['namespace'] = isset($vars['namespace']) ? $vars['namespace'] : self::getGroupNamespace();
        $vars['middlewares'] = isset($vars['middlewares']) ? $vars['middlewares'] : self::getGroupMiddlewares();
        $vars['uriPrefix'] = isset($vars['uriPrefix']) ? $vars['uriPrefix'] : self::getGroupUriPrefix();
        $vars['isClosure'] = isset($vars['isClosure']) ? $vars['isClosure'] : self::isClosure();

        $route = new Route($vars);

        $uriPattern = !empty(self::getGroupUriPrefix()) ?
            self::getGroupUriPrefix() . $uriPattern : $uriPattern;

        self::$routes->get('ALL')->add($route, strtoupper($requestMethod).'::'.$uriPattern);
        self::$routes->get(strtoupper($requestMethod))->add($route, $uriPattern);
    }

    /**
     * @param $strOrArray
     *
     * @return array
     */
    private static function fetchControllerAndAction($strOrArray)
    {
        $array = [];
        if (!is_array($strOrArray)) {
            list($array['controller'], $array['action']) = explode('@',
                $strOrArray);
        } else {
            $array = $strOrArray;
        }

        return $array;
    }

    /**
     * @param null $method
     *
     * @return mixed
     */
    public static function routes($method = null)
    {
        return self::$routes->get($method);
    }

    /**
     * @param null $uriString
     *
     * @return Route
     * @throws RouteNotFoundException
     */
    private static function fetchRoute($uriString = null)
    {
        // Get the current uri string
        $uri = $uriString ? $uriString : Request::getUriString();

        // Get the registered routes by http request method
        $routes = self::routes(
            Request::getRequestMethod()
        )->getArray();

        // Look for a literal match
        if (isset($routes[$uri])) {
            return $routes[$uri];
        }

        // Look for wild-cards
        foreach ($routes as $key => $options) {
            if ($matches = self::matchWildcard($key)) {
                $routes[$key]->setParams($matches);
                return $routes[$key];
            }
        }

        throw new RouteNotFoundException(
            "Route for '" .Request::getRequestMethod() . ' '
            . $uriString."' not found", self::dump());
    }

    /**
     * @param $uriPattern
     *
     * @return bool
     */
    public static function matchLiteral($uriPattern)
    {
        return $uriPattern == Request::getUriString();
    }

    /**
     * @param $uriPattern
     *
     * @return null
     */
    public static function matchWildcard($uriPattern)
    {
        // Convert wildcards to RegEx
        $str = str_replace(
            ':any', '.+', str_replace(':num', '[0-9]+', $uriPattern)
        );

        if (preg_match('#^' . $str . '$#',
            Request::getUriString(),
            $matches
        )) {
            array_shift($matches);

            return $matches;
        }

        return null;
    }

    /**
     * @param null $uriString
     *
     * @return Route
     */
    public static function getRoute($uriString = null)
    {
        return self::fetchRoute($uriString);
    }

    public static function fetch($closure)
    {
        self::init();

        $closure();

        $route = Router::getRoute();

        Response::setContent(call_user_func_array(
            [IOC::make($route->getController()), $route->getAction()],
            $route->getParams()
        ));

        Response::send();

        exit();
    }

}