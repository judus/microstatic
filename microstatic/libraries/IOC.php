<?php namespace MicroStatic;

use MicroStatic\Exception as ClassDoesNotExistException;
use MicroStatic\Exception as IocNotResolvableException;
use MicroStatic\Exception as UnresolvedDependenciesException;

/**
 * Class IOC
 *
 * @package MicroStatic
 */
class IOC
{
    /**
     * @var string
     */
    private static $namespace = "MicroStatic\\";

    /**
     * @var array
     */
    public static $registry = [];

    /**
     * @var array
     */
    public static $singletons = [];

    /**
     * @var array
     */
    public static $bindings = [];

    /**
     * @var array
     */
    public static $providers = [];

    /**
     * @var array
     */
    public static $config = [];


    public static function config(string $key, array $array = null)
    {
        if (is_array($array) && !isset(static::$config[$key])) {
            static::$config[$key] = $array;
        } else {
            return static::$config[$key];
        }
    }

    /**
     * @return string
     */
    public static function getNamespace(): string
    {
        return self::$namespace;
    }

    /**
     * @param string $namespace
     */
    public static function setNamespace(string $namespace)
    {
        self::$namespace = $namespace;
    }


    /**
     * @param          $name
     * @param \Closure $class
     */
    public static function register($name, \Closure $class)
    {
        static::$registry[$name] = $class;
    }

    /**
     * @param          $name
     * @param \Closure $singleton
     */
    public static function singleton($name, \Closure $singleton)
    {
        static::$registry[$name] = $singleton();
    }

    /**
     * @param $name
     * @param $binding
     */
    public static function bind($name, $binding)
    {
        static::$bindings[$name] = $binding;
    }

    /**
     * @param          $name
     * @param \Closure $provider
     */
    public static function provide($name, \Closure $provider)
    {
        static::$registry[$name] = $provider;
    }

    /**
     * @param      $name
     * @param null $params
     *
     * @return mixed
     * @throws IocNotResolvableException
     */
    public static function resolve($name, $params = null)
    {
        if ($name = static::registered($name)) {
            $name = static::$registry[$name];
            return $name()->resolve($params);
        }

        throw new IocNotResolvableException(null, [
            'name' => $name,
            'params' => $params
        ]);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public static function registered($name)
    {
        if (array_key_exists($name, static::$registry)) {
            return $name;
        }

        $alias = str_replace(self::$namespace, '', $name);

        if (array_key_exists($alias, static::$registry)) {
            return $alias;
        }

        return null;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public static function bound($name)
    {
        return array_key_exists($name, static::$bindings);
    }

    /**
     * @param $class
     *
     * @return \ReflectionClass
     * @throws ClassDoesNotExistException
     */
    public static function reflect($class)
    {
        try {
            return new \ReflectionClass($class);
        } catch (\Exception $e) {
            throw new ClassDoesNotExistException('Class '. $class.' does not exist');
        }
    }

    /**
     * @param \ReflectionClass $reflected
     *
     * @return array
     */
    public static function getDependencies(\ReflectionClass $reflected)
    {
        $dependencies = [];

        if ($constructor = $reflected->getConstructor()) {
            $parameters = $constructor->getParameters();
            foreach ($parameters as $parameter) {
                $dependencies[] = self::getDependency($parameter);
            }
        }
        return $dependencies;
    }

    /**
     * @param \ReflectionParameter $parameter
     *
     * @return mixed|null
     */
    public static function getDependency(\ReflectionParameter $parameter)
    {
        if ($parameter->isArray() || !$parameter->getClass()) {
            return null;
        }

        $class = $parameter->getClass()->name;

        if ($class == 'Closure') {
            return null;
        }

        $reflected = new \ReflectionClass($class);

        if (self::bound($reflected->name)) {
            return self::$bindings[$reflected->name];
        } else {
            return $reflected->name;
        }
    }

    /**
     * @param array $dependencies
     *
     * @return array
     */
    public static function resolveDependencies(array $dependencies)
    {
        foreach ($dependencies as &$dependency) {
            if (is_null($dependency)) {
                $dependency =  null;
            } else {
                if (IOC::registered($dependency)) {
                    $dependency = IOC::resolve($dependency);
                } else {
                    // just try unregistered
                    $dependency = new $dependency();
                }
            }
        }
        return $dependencies;
    }

    /**
     * @param            $class
     * @param array|null $params
     *
     * @return object
     * @throws UnresolvedDependenciesException
     */
    public static function make($class, array $params = null)
    {
        $reflected = self::reflect($class);

        if (empty($reflected->getConstructor())) {
            return $reflected->newInstance();
        }

        $dependencies = self::getDependencies($reflected);

        $instanceArgs = self::resolveDependencies($dependencies);

        if (is_array($params)) {
            foreach($params as $param) {
                foreach ($instanceArgs as &$instanceArg) {
                    if (is_null($instanceArg)) {
                        $instanceArg = $param;
                        break;
                    }
                }
            }
        }

        // TODO: Improve this. A lot.
        if (count($dependencies) != count($instanceArgs)) {
            throw new UnresolvedDependenciesException(
                'Could not resolve all dependencies', [
                'Required' => $dependencies,
                'Resolved' => $instanceArgs
            ]);
        }

        if (is_array($params)) {
            $instanceArgs = array_merge($instanceArgs, $params);
        }

        return $reflected->newInstanceArgs($instanceArgs);
    }

}
