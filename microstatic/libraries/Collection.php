<?php namespace MicroStatic;

use MicroStatic\Exception as InvalidKeyException;
use MicroStatic\Exception as KeyInUseException;

/**
 * Class Collection
 *
 * @package MicroStatic
 */
class Collection
{
	/**
	 * @var array
	 */
	private $items = array();

	/**
	 * @param      $obj
	 * @param null $key
	 *
	 * @return Collection
	 * @throws KeyInUseException
	 */
	public function add($obj, $key = null)
	{
		if ($key == null) {
			$this->items[] = $obj;
		} else {
			if (isset($this->items[$key])) {
				throw new KeyInUseException("Collection key '".$key."' is already in use.", $this);
			} else {
				$this->items[$key] = $obj;
			}
		}

		return $this;
	}

    /**
     * @param $key
     *
     * @throws InvalidKeyException
     */
	public function delete($key)
	{
		if (isset($this->items[$key])) {
			unset($this->items[$key]);
		} else {
			throw new InvalidKeyException("Collection key '".$key."' does not exist.", $this);
		}
	}

    /**
     * @param $key
     *
     * @return mixed
     * @throws InvalidKeyException
     */
	public function get($key)
	{
		if (isset($this->items[$key])) {
			return $this->items[$key];
		} else {
			throw new InvalidKeyException("Collection key '" . $key . "' does not exist.",
                $this);
		}
	}

	/**
	 * @param null $key
	 *
	 * @return int
	 */
	public function count($key = null)
	{
		if (!is_null($key) && isset($this->items[$key])) {
			return count($this->items[$key]);
		} else {
			return count($this->items);
		}
	}

	/**
	 * @return array
	 */
	public function getArray()
	{
		return $this->items;
	}

}