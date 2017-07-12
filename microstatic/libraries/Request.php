<?php namespace MicroStatic;

/**
 * Class Request
 *
 * @package MicroStatic
 */
class Request
{
	/**
     * The current uri string
     *
	 * @var
	 */
	private static $uriString;

	/**
     * The current http method
     *
	 * @var
	 */
	private static $requestMethod;

	/**
     * Holds all the uri segments until ? or #
     *
	 * @var array
	 */
	private static $segments = [];

	/**
     * Setter $uriString
     *
	 * @param $str
	 */
	private static function setUriString($str)
	{
		self::$uriString = $str;
	}

	/**
     * Getter $uriString
     *
	 * @return mixed
	 */
	public static function getUriString()
	{
		return self::$uriString;
	}

	/**
     * Setter $requestMethod
     *
	 * @param $str
	 */
	public static function setRequestMethod($str)
	{
		self::$requestMethod = $str;
	}

	/**
     * Getter $requestMethod
     *
	 * @return mixed
	 */
	public static function getRequestMethod()
	{
		return self::$requestMethod;
	}

	/**
     * Setter $segments
     *
	 * @param array $segments
	 */
	public static function setSegments(array $segments)
	{
		self::$segments = $segments;
	}

	/**
     * Getter $segments
     *
	 * @return array
	 */
	public static function getSegments()
	{
		return self::$segments;
	}

	/**
     * Request constructor
     * sets $requestMethod
     * sets $uriString
     * sets $segments
	 */
	public static function init()
	{
		self::fetchRequestMethod();
		self::fetchUriString();
		self::explodeSegments();


	}

	public static function dump()
    {
        return get_class_vars(get_called_class());
    }

	/**
	 * Determined the http method
	 */
	public static function fetchRequestMethod()
	{
        if (php_sapi_name() == 'cli' or defined('STDIN')) {
            self::setRequestMethod('CLI');
            return;
        }

        if (isset($_POST['_method'])) {
			if (
				strtoupper($_POST['_method']) == 'PUT' ||
                strtoupper($_POST['_method']) == 'PATCH' ||
                strtoupper($_POST['_method']) == 'DELETE'
			) {
				self::setRequestMethod(strtoupper($_POST['_method']));
				return;
			}
		}

		self::setRequestMethod($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * Fetches the REQUEST_URI and sets $uriString
	 */
	public static function fetchUriString()
	{
		if (php_sapi_name() == 'cli' or defined('STDIN')) {
			self::setUriString(self::parseCliArgs());
			return;
		}

        // Fetch request string (apache)
		$uri = $_SERVER['REQUEST_URI'];
        $uri = parse_url($uri)['path'];

        // Further cleaning of the uri
		$uri = str_replace(array('//', '../'), '/', trim($uri, '/'));

        if (defined('HTTP_BASE') && php_sapi_name() != 'cli' && !defined('STDIN')) {
            $uri = trim(str_replace(HTTP_BASE, '', '/' . $uri), '/');
        }

        $uri = empty($uri) ? '/' : $uri;

        self::setUriString($uri);
	}

	/**
     * Formats cli args like a uri
     *
	 * @return string
	 */
	private static function parseCliArgs()
	{
		$args = array_slice($_SERVER['argv'], 1);

		return $args ? '/' . implode('/', $args) : '';
	}

	/**
     * Filter or replace bad chars from uri
     *
	 * @param $uri
	 *
	 * @return mixed
	 */
	public static function filterUri($uri)
	{
		$bad = array('$', '(', ')', '%28', '%29');
		$good = array('&#36;', '&#40;', '&#41;', '&#40;', '&#41;');

		return str_replace($bad, $good, $uri);
	}

	/**
	 * Explodes the uri string
	 */
	public static function explodeSegments()
	{
		foreach (
			explode("/", preg_replace("|/*(.+?)/*$|", "\\1", self::$uriString))
			as $val
		) {
			$val = trim(self::filterUri($val));

			if ($val != '') {
				self::$segments[] = $val;
			}
		}
	}

	public static function segment($n)
    {
        if (isset(self::getSegments()[$n])) {
            return self::getSegments()[$n];
        }

        return null;
    }
}