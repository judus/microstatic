<?php namespace MicroStatic;

/**
 * Class Response
 *
 * @package MicroStatic
 */
class Response
{
	/**
     * Holds the response content
     *
	 * @var mixed
	 */
	private static $content;

	/**
     * Config option json encode if $content is array
     *
	 * @var bool
	 */
	private static $jsonEncodeArray = true;

	/**
     * Config option json encode if $content is object
     *
     * @var bool
	 */
	private static $jsonEncodeObject = true;

    /**
     * @param $content
     */
    public static function setContent($content)
    {
        self::$content = $content;
    }

    /**
     * @return mixed
     */
    public static function getContent()
    {
        return self::$content;
    }

    /**
     * @return mixed
     */
    public static function getJsonEncodeArray()
    {
        return self::$jsonEncodeArray;
    }

    /**
     * @param mixed $jsonEncodeArray
     */
    public static function setJsonEncodeArray($jsonEncodeArray)
    {
        self::$jsonEncodeArray = $jsonEncodeArray;
    }

    /**
     * @return mixed
     */
    public static function getJsonEncodeObject()
    {
        return self::$jsonEncodeObject;
    }

    /**
     * @param mixed $jsonEncodeObject
     */
    public static function setJsonEncodeObject($jsonEncodeObject)
    {
        self::$jsonEncodeObject = $jsonEncodeObject;
    }

    /**
     * Send a http header
     *
     * @param $str
     */
	public static function header($str)
	{
		header($str);
    }

    /**
     * @param null $content
     */
    public static function prepare($content = null)
    {
        is_null($content) || self::setContent($content);

        $content = self::getContent();

        $content = self::arrayToJson($content);

        $content = self::objectToJson($content);

        $content = self::printRecursiveNonAlphaNum($content);

        self::setContent($content);
    }

    /**
     * Prepares and send the response to the client
     *
     * @param null $content
     */
    public static function send($content = null)
    {
        self::prepare($content);
        self::sendPrepared();
    }

    /**
     * Send the response to the client
     */
    public static function sendPrepared()
    {
        echo self::getContent();
        self::close();
    }

    /**
     * Encode array to json if configured
     *
     * @param $content
     *
     * @return string
     */public static function arrayToJson($content = null)
    {
        if (self::getJsonEncodeArray() && is_array($content)) {
            self::header('Content-Type: application/json');
            return json_encode($content);
        }

        return $content;
    }

    /**
     * Encode object to json if configured
     *
     * @param $content
     *
     * @return string
     */
    public static function objectToJson($content = null)
    {
        if (self::getJsonEncodeObject() && is_object($content)) {
            self::header('Content-Type: application/json');
            return json_encode($content);
        }

        return $content;
    }

    /**
     * Does a print_r withobjects and array recursive
     *
     * @param $content
     *
     * @return string
     */
    public static function printRecursiveNonAlphaNum($content = null)
    {
        if (is_array($content) || is_object($content)) {
            ob_start();
            show($content);
            $content = ob_get_contents();
            ob_end_clean();
        }

        return $content;
    }

    /**
     * Redirect location
     *
     * @param $url
     */
    public static function redirect($url)
    {
        header('Location: ' . $url);
    }

    public static function status404()
    {
        header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
        echo "<h1>404 Not Found</h1>";
        echo "The page that you have requested could not be found.";
        self::close();
    }

    /**
	 * Exit PHP
	 */
	public static function close()
	{
		exit();
	}
}