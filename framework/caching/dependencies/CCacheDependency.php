<?php

/**
 * CCacheDependency is the base class for cache dependency classes.
 */
class CCacheDependency extends CComponent implements ICacheDependency
{
	
	public $reuseDependentData=false;

	
	private static $_reusableData=array();

	private $_hash;
	private $_data;

	
	public function evaluateDependency()
	{
		if ($this->reuseDependentData)
		{
			$hash=$this->getHash();
			if(!isset(self::$_reusableData[$hash]['dependentData']))
				self::$_reusableData[$hash]['dependentData']=$this->generateDependentData();
			$this->_data=self::$_reusableData[$hash]['dependentData'];
		}
		else
			$this->_data=$this->generateDependentData();
	}

	/**
	 * @return boolean whether the dependency has changed.
	 */
	public function getHasChanged()
	{
		if ($this->reuseDependentData)
		{
			$hash=$this->getHash();
			if(!isset(self::$_reusableData[$hash]['dependentData']))
				self::$_reusableData[$hash]['dependentData']=$this->generateDependentData();
			return self::$_reusableData[$hash]['dependentData']!=$this->_data;
		}
		else
			return $this->generateDependentData()!=$this->_data;
	}

	
	public function getDependentData()
	{
		return $this->_data;
	}


	public static function resetReusableData()
	{
		self::$_reusableData=array();
	}


	protected function generateDependentData()
	{
		return null;
	}
	/**
	 * Generates a unique hash that identifies this cache dependency.
	 * @return string the hash for this cache dependency
	 */
	private function getHash()
	{
		if($this->_hash===null)
			$this->_hash=sha1(serialize($this));
		return $this->_hash;
	}
}