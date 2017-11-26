<?php


/**
 * CQueue implements a queue.
 */
class CQueue extends CComponent implements IteratorAggregate,Countable
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

	/**
	 * Constructor.
	 * Initializes the queue with an array or an iterable object.
	 */
	public function __construct($data=null)
	{
		if($data!==null)
			$this->copyFrom($data);
	}

	/**
	 * @return array the list of items in queue
	 */
	public function toArray()
	{
		return $this->_d;
	}

	/**
	 * Copies iterable data into the queue.
	 */
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
			throw new CException(Yii::t('yii','Queue data must be an array or an object implementing Traversable.'));
	}

	/**
	 * Removes all items in the queue.
	 */
	public function clear()
	{
		$this->_c=0;
		$this->_d=array();
	}

	/**
	 * @param mixed $item the item
	 * @return boolean whether the queue contains the item
	 */
	public function contains($item)
	{
		return array_search($item,$this->_d,true)!==false;
	}

	/**
	 * Returns the item at the top of the queue.
	 * @return mixed item at the top of the queue
	 * @throws CException if the queue is empty
	 */
	public function peek()
	{
		if($this->_c===0)
			throw new CException(Yii::t('yii','The queue is empty.'));
		else
			return $this->_d[0];
	}

	/**
	 * Removes and returns the object at the beginning of the queue.
	 * @return mixed the item at the beginning of the queue
	 * @throws CException if the queue is empty
	 */
	public function dequeue()
	{
		if($this->_c===0)
			throw new CException(Yii::t('yii','The queue is empty.'));
		else
		{
			--$this->_c;
			return array_shift($this->_d);
		}
	}

	/**
	 * Adds an object to the end of the queue.
	 * @param mixed $item the item to be appended into the queue
	 */
	public function enqueue($item)
	{
		++$this->_c;
		$this->_d[]=$item;
	}

	/**
	 * Returns an iterator for traversing the items in the queue.
	 * This method is required by the interface IteratorAggregate.
	 * @return Iterator an iterator for traversing the items in the queue.
	 */
	public function getIterator()
	{
		return new CQueueIterator($this->_d);
	}

	/**
	 * Returns the number of items in the queue.
	 * @return integer the number of items in the queue
	 */
	public function getCount()
	{
		return $this->_c;
	}

	/**
	 * Returns the number of items in the queue.
	 * This method is required by Countable interface.
	 * @return integer number of items in the queue.
	 */
	public function count()
	{
		return $this->getCount();
	}
}
