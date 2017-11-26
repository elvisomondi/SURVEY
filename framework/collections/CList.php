<?php


/**
 * CList implements an integer-indexed collection class.
 */
class CList extends CComponent implements IteratorAggregate,ArrayAccess,Countable
{
	/**
	 * @var array internal data storage
	 */
	private $_d=array();
	/**
	 * @var integer number of items
	 */
	private $_c=0;
	/**
	 * @var boolean whether this list is read-only
	 */
	private $_r=false;

	
	public function __construct($data=null,$readOnly=false)
	{
		if($data!==null)
			$this->copyFrom($data);
		$this->setReadOnly($readOnly);
	}

	/**
	 * @return boolean whether this list is read-only or not. Defaults to false.
	 */
	public function getReadOnly()
	{
		return $this->_r;
	}

	/**
	 * @param boolean $value whether this list is read-only or not
	 */
	protected function setReadOnly($value)
	{
		$this->_r=$value;
	}

	
	public function getIterator()
	{
		return new CListIterator($this->_d);
	}

	
	public function count()
	{
		return $this->getCount();
	}

	/**
	 * Returns the number of items in the list.
	 * @return integer the number of items in the list
	 */
	public function getCount()
	{
		return $this->_c;
	}


	public function itemAt($index)
	{
		if(isset($this->_d[$index]))
			return $this->_d[$index];
		elseif($index>=0 && $index<$this->_c) // in case the value is null
			return $this->_d[$index];
		else
			throw new CException(Yii::t('yii','List index "{index}" is out of bound.',
				array('{index}'=>$index)));
	}

	
	public function add($item)
	{
		$this->insertAt($this->_c,$item);
		return $this->_c-1;
	}

	
	public function insertAt($index,$item)
	{
		if(!$this->_r)
		{
			if($index===$this->_c)
				$this->_d[$this->_c++]=$item;
			elseif($index>=0 && $index<$this->_c)
			{
				array_splice($this->_d,$index,0,array($item));
				$this->_c++;
			}
			else
				throw new CException(Yii::t('yii','List index "{index}" is out of bound.',
					array('{index}'=>$index)));
		}
		else
			throw new CException(Yii::t('yii','The list is read only.'));
	}


	public function remove($item)
	{
		if(($index=$this->indexOf($item))>=0)
		{
			$this->removeAt($index);
			return $index;
		}
		else
			return false;
	}

	
	public function removeAt($index)
	{
		if(!$this->_r)
		{
			if($index>=0 && $index<$this->_c)
			{
				$this->_c--;
				if($index===$this->_c)
					return array_pop($this->_d);
				else
				{
					$item=$this->_d[$index];
					array_splice($this->_d,$index,1);
					return $item;
				}
			}
			else
				throw new CException(Yii::t('yii','List index "{index}" is out of bound.',
					array('{index}'=>$index)));
		}
		else
			throw new CException(Yii::t('yii','The list is read only.'));
	}

	/**
	 * Removes all items in the list.
	 */
	public function clear()
	{
		for($i=$this->_c-1;$i>=0;--$i)
			$this->removeAt($i);
	}

	/**
	 * @param mixed $item the item
	 * @return boolean whether the list contains the item
	 */
	public function contains($item)
	{
		return $this->indexOf($item)>=0;
	}

	/**
	 * @param mixed $item the item
	 * @return integer the index of the item in the list (0 based), -1 if not found.
	 */
	public function indexOf($item)
	{
		if(($index=array_search($item,$this->_d,true))!==false)
			return $index;
		else
			return -1;
	}

	/**
	 * @return array the list of items in array
	 */
	public function toArray()
	{
		return $this->_d;
	}


	public function copyFrom($data)
	{
		if(is_array($data) || ($data instanceof Traversable))
		{
			if($this->_c>0)
				$this->clear();
			if($data instanceof CList)
				$data=$data->_d;
			foreach($data as $item)
				$this->add($item);
		}
		elseif($data!==null)
			throw new CException(Yii::t('yii','List data must be an array or an object implementing Traversable.'));
	}

	
	public function mergeWith($data)
	{
		if(is_array($data) || ($data instanceof Traversable))
		{
			if($data instanceof CList)
				$data=$data->_d;
			foreach($data as $item)
				$this->add($item);
		}
		elseif($data!==null)
			throw new CException(Yii::t('yii','List data must be an array or an object implementing Traversable.'));
	}

	
	public function offsetExists($offset)
	{
		return ($offset>=0 && $offset<$this->_c);
	}

	
	public function offsetGet($offset)
	{
		return $this->itemAt($offset);
	}

	
	public function offsetSet($offset,$item)
	{
		if($offset===null || $offset===$this->_c)
			$this->insertAt($this->_c,$item);
		else
		{
			$this->removeAt($offset);
			$this->insertAt($offset,$item);
		}
	}


	public function offsetUnset($offset)
	{
		$this->removeAt($offset);
	}
}

