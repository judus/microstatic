<?php namespace MicroStatic;

/**
 * Class Exception
 *
 * @package MicroStatic\Exceptions
 */
class Exception extends \Exception
{
    /**
     * @var
     */
    protected $myCode;

    /**
     * @var
     */
    protected $myTitle;

    /**
     * @var string
     */
    protected $myMessage = 'Unknown exception';

    /**
     * @var
     */
    protected $myFile;

    /**
     * @var
     */
    protected $myLine;

    /**
     * @var
     */
    protected $myData;

    /**
     * @var
     */
    protected $myTraces;

    /**
     * @var
     */
    protected $myFooter;

    /**
     * @var
     */
    protected $prettyMessage;

    /**
     * @return int
     */
    public function getMyCode()
    {
        return $this->myCode;
    }

    /**
     * @param mixed $myCode
     */
    public function setMyCode($myCode)
    {
        $this->myCode = $myCode;
    }

    /**
     * @return mixed
     */
    public function getMyTitle()
    {
        $reflected = new \ReflectionClass($this);

        return $reflected->getShortName();
    }

    /**
     * @return string
     */
    public function getMyMessage()
    {
        if ($this->isMessageObject()) {
            return $this->MyMessage->getMessage();
        }

        return $this->myMessage;
    }

    /**
     * @param string $message
     */
    public function setMyMessage($message)
    {
        $message = $message ? $message :
            'Undocumented error at line '
            . debug_backtrace()[0]['line'] . ' in '
            . debug_backtrace()[0]['file'];

        $this->myMessage = $message;
    }

    /**
     * @return mixed
     */
    public function getMyFile()
    {
        if ($this->isMessageObject()) {
            return $this->myMessage->getFile();
        }

        return debug_backtrace()[2]['file'];
    }

    /**
     * @param mixed $file
     */
    public function setMyFile($file)
    {
        $this->myFile = $file;
    }

    /**
     * @return mixed
     */
    public function getMyLine()
    {
        if ($this->isMessageObject()) {
            return $this->myMessage->getLine();
        }

        return debug_backtrace()[2]['line'];
    }

    /**
     * @param mixed $line
     */
    public function setMyLine($line)
    {
        $this->myLine = $line;
    }

    /**
     * @return mixed
     */
    public function getMyData()
    {
        return $this->myData ? $this->myData : debug_backtrace();
    }

    /**
     * @param mixed $data
     */
    public function setMyData($data)
    {
        $this->myData = $data;
    }

    /**
     * @return mixed
     */
    public function getMyTraces()
    {
        if ($this->isMessageObject()) {
            return $this->MyMessage->getTrace();
        }

        return $this->myTraces;
    }

    /**
     * @param mixed $traces
     */
    public function setMyTraces($traces)
    {
        $this->myTraces = $traces;
    }

    /**
     * @return mixed
     */
    public function getMyFooter()
    {
        return get_class($this);
    }

    /**
     * @param mixed $myFooter
     */
    public function setMyFooter($myFooter)
    {
        $this->myFooter = $myFooter;
    }

    /**
     * @return mixed
     */
    public function getPrettyMessage()
    {
        return $this->prettyMessage;
    }

    /**
     * @param mixed $prettyMessage
     */
    public function setPrettyMessage($prettyMessage)
    {
        $this->prettyMessage = $prettyMessage;
    }

    /**
     * MinimalException constructor.
     *
     * @param null $message
     * @param null $data
     */
    public function __construct($message = null, $data = null)
    {
        if (!$message) {
            throw new $this('Unknown ' . get_class($this));
        }

        $this->handleError(
            $this->prettyMessage($message, $data, get_class($this)),
            $message,
            get_class($this)
        );
    }

    /**
     * @param $prettyMessage
     * @param $message
     */
    protected function handleError($prettyMessage, $message)
    {
        $this->dienow($prettyMessage);
    }

    /**
     * @param null $exitMessage
     */
    protected function close($exitMessage = null)
    {
        close($exitMessage);
    }

    /**
     * @param null $exitMessage
     */
    protected function dienow($exitMessage = null)
    {
        die($exitMessage);
    }

    protected function isMessageObject()
    {
        if (!is_string($this->myMessage) &&
            !is_array($this->myMessage)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param null $message
     * @param null $data
     *
     * @return mixed|string
     */
    public function prettyMessage($message = null, $data = null)
    {
        $this->setMyMessage($message);
        $this->setMyData($data);

        return $this->template([
            'title' => $this->getMyTitle(),
            'message' => $this->getMyMessage(),
            'file' => $this->getMyFile(),
            'line' => $this->getMyLine(),
            'data' => $this->getMyData(),
            'traces' => $this->getMyTraces(),
            'footer' => $this->getMyFooter()
        ]);
    }


    public function template(array $vars)
    {
        extract($vars);

        $title = isset($title) ? $title : null;
        $message = isset($message) ? $message : null;
        $file = isset($file) ? $file : null;
        $line = isset($line) ? $line : null;
        $traces = isset($traces) ? $traces : [];
        $data = isset($data) ? $data : null;
        $footer = isset($footer) ? $footer : null;

        ob_start();
        ?>
		<html>
	<body>
	<div class="debug_show">
		<h1><?= $title ?></h1>
		<hr>
		<p class="bold"><?= $message ?></p>
		<hr>
		<p class="small"><span class="bold">File: </span><?= $file ?></p>
		<p class="small"><span class="bold">Line: </span><?= $line ?></p>
        <?php if ($data) : ?>
			<hr>
			<pre><?= htmlentities(print_r($data)) ?></pre>
        <?php endif ?>
        <?php foreach ($traces as $item) { ?>
			<hr>
			<pre><?= htmlentities(print_r($item)) ?></pre>
        <?php } ?>
		<hr>
		<p class="small"><?= $footer ?></p>
	</div>
	<style>
		* {
			margin: 0;
			padding: 0;
		}

		body {
			margin: 0;
			padding: 2em;
			background-color: #ddd;
			font-family: verdana, Helvetica;
			color: #204D74;
		}

		h1 {
			font-size: 1.6em;
			font-weight: bold;
			color: #D43F3A;
		}

		p {
			font-size: 1.2em;
			margin: 0;
			padding: 0
		}

		.bold {
			font-weight: bold;
		}

		.small {
			font-size: 0.8em;
			color: #333333;
		}

		pre {
			padding: 2em;
			font-size: 0.8em;
			line-height: 1.8em;
			background: #eee;
			color: #333333;
			overflow: auto;
		}

		.debug_show hr {
			display: block;
			font-size: 1.2em;
			color: transparent;
			height: 0px;
			margin: -1px 0 1em;
			padding: 1em 0 0em;
			border-bottom: 1px dashed #333333;
		}
	</style>
	</body></html><?php

        $content = ob_get_contents();
        ob_end_clean();
        $replace = dirname(dirname($_SERVER['DOCUMENT_ROOT']));
        $content = str_replace($replace, '...', $content);
        $content = str_replace('1</pre>', '</pre>', $content);

        return $content;

    }

    /**
     * @return string
     */
    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
            . "{$this->getTraceAsString()}";
    }
}