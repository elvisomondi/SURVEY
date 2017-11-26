<?php

class CDummyCache extends CApplicationComponent implements ICache, ArrayAccess
{
	
	public $keyPrefix;

	
	public function init()
	{
		parent::init();
		if($this->keyPrefix===null)
			$this->keyPrefix=Yii::app()->getId();
	}

	
	public function get($id)
	{
		return false;
	}

	
	public function mget($ids)
	{
		$results=array();
		foreach($ids as $id)
			$results[$id]=false;
		return $results;
	}

	
	public function set($id,$value,$expire=0,$dependency=null)
	{
		return true;
	}

	
	public function add($id,$value,$expire=0,$dependency=null)
	{
		return true;
	}

	
	public function delete($id)
	{
		return true;
	}

	
	public function flush()
	{
		return true;
	}

	
	public function offsetExists($id)
	{
		return false;
	}

	
	public function offsetGet($id)
	{
		return false;
	}


	public function offsetSet($id, $value)
	{
	}

	
	public function offsetUnset($id)
	{
	}
}
