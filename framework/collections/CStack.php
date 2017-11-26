<?php

class CStack extends CComponent implements IteratorAggregate,Countable
{
	/**
	 * internal data storage
	 * @var array
	 */
	private $_d=array();
	/**
	 * number of items
	 * @var integer
	 */
	private $_c=0;

	
	public function __construct($data=null)
	{
		if($data!==null)
			$this->copyFrom($data);
	}

	/**
	 * @return array the list of items in stack
	 */
	public function toArray()
	{
		return $this->_d;
	}

	public function copyFrom($data)
	{
		if(is_array($data) || ($data instanceof Traversable))
		{
			$this->clear();
			foreach($data as $item)
			{
				$this->_d[]=$item;
				++$this->_c;
			}
		}
		elseif($data!==null)
			throw new CException(Yii::t('yii','Stack data must be an array or an object implementing Traversable.'));
	}

	/**
	 * Removes all items in the stack.
	 */
	public function clear()
	{
		$this->_c=0;
		$this->_d=array();
	}

	/**
	 * @param mixed $item the item
	 * @return boolean whether the stack contains the item
	 */
	public function contains($item)
	{
		return array_search($item,$this->_d,true)!==false;
	}

	public function peek()
	{
		if($this->_c)
			return $this->_d[$this->_c-1];
		else
			throw new CException(Yii::t('yii','The stack is empty.'));
	}

	public function pop()
	{
		if($this->_c)
		{
			--$this->_c;
			return array_pop($this->_d);
		}
		else
			throw new CException(Yii::t('yii','The stack is empty.'));
	}

	/**
	 * Pushes an item into the stack.
	 * @param mixed $item the item to be pushed into the stack
	 */
	public function push($item)
	{
		++$this->_c;
		$this->_d[]=$item;
	}

	public function getIterator()
	{
		return new CStackIterator($this->_d);
	}

	/**
	 * Returns the number of items in the stack.
	 * @return integer the number of items in the stack
	 */
	public function getCount()
	{
		return $this->_c;
	}

	public function count()
	{
		return $this->getCount();
	}
}
