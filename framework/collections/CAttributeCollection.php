<?php

/**
 * CAttributeCollection implements a collection for storing attribute names and values.
 */
class CAttributeCollection extends CMap
{
	/**
	 * @var boolean whether the keys are case-sensitive. Defaults to false.
	 */
	public $caseSensitive=false;

	/**
	 * Returns a property value or an event handler list by property or event name.
	 */
	public function __get($name)
	{
		if($this->contains($name))
			return $this->itemAt($name);
		else
			return parent::__get($name);
	}

	public function __set($name,$value)
	{
		$this->add($name,$value);
	}

	
	public function __isset($name)
	{
		if($this->contains($name))
			return $this->itemAt($name)!==null;
		else
			return parent::__isset($name);
	}

	
	public function __unset($name)
	{
		$this->remove($name);
	}

	
	public function itemAt($key)
	{
		if($this->caseSensitive)
			return parent::itemAt($key);
		else
			return parent::itemAt(strtolower($key));
	}

	
	public function add($key,$value)
	{
		if($this->caseSensitive)
			parent::add($key,$value);
		else
			parent::add(strtolower($key),$value);
	}

	
	public function remove($key)
	{
		if($this->caseSensitive)
			return parent::remove($key);
		else
			return parent::remove(strtolower($key));
	}

	
	public function contains($key)
	{
		if($this->caseSensitive)
			return parent::contains($key);
		else
			return parent::contains(strtolower($key));
	}

	
	public function hasProperty($name)
	{
		return $this->contains($name) || parent::hasProperty($name);
	}


	public function canGetProperty($name)
	{
		return $this->contains($name) || parent::canGetProperty($name);
	}

	
	public function canSetProperty($name)
	{
		return true;
	}

	/**
	 * Merges iterable data into the map.
	 */
	public function mergeWith($data,$recursive=true)
	{
		if(!$this->caseSensitive && (is_array($data) || $data instanceof Traversable))
		{
			$d=array();
			foreach($data as $key=>$value)
				$d[strtolower($key)]=$value;
			return parent::mergeWith($d,$recursive);
		}
		parent::mergeWith($data,$recursive);
	}
}
