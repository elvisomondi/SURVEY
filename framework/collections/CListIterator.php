<?php

class CListIterator implements Iterator
{
	/**
	 * @var array the data to be iterated through
	 */
	private $_d;
	/**
	 * @var integer index of the current item
	 */
	private $_i;

	/**
	 * Constructor.
	 * @param array $data the data to be iterated through
	 */
	public function __construct(&$data)
	{
		$this->_d=&$data;
		$this->_i=0;
	}

	/**
	 * Rewinds internal array pointer.
	 * This method is required by the interface Iterator.
	 */
	public function rewind()
	{
		$this->_i=0;
	}

	
	public function key()
	{
		return $this->_i;
	}

	
	public function current()
	{
		return $this->_d[$this->_i];
	}

	
	public function next()
	{
		$this->_i++;
	}

	
	public function valid()
	{
		return $this->_i<count($this->_d);
	}
}
