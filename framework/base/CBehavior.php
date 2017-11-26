<?php

/**
 * CBehavior is a convenient base class for behavior classes.
 */
class CBehavior extends CComponent implements IBehavior
{
	private $_enabled=false;
	private $_owner;


	public function events()
	{
		return array();
	}

	
	public function attach($owner)
	{
		$this->_enabled=true;
		$this->_owner=$owner;
		$this->_attachEventHandlers();
	}

	/**
	 * Detaches the behavior object from the component.
	 */
	public function detach($owner)
	{
		foreach($this->events() as $event=>$handler)
			$owner->detachEventHandler($event,array($this,$handler));
		$this->_owner=null;
		$this->_enabled=false;
	}

	/**
	 * @return CComponent the owner component that this behavior is attached to.
	 */
	public function getOwner()
	{
		return $this->_owner;
	}

	/**
	 * @return boolean whether this behavior is enabled
	 */
	public function getEnabled()
	{
		return $this->_enabled;
	}

	/**
	 * @param boolean $value whether this behavior is enabled
	 */
	public function setEnabled($value)
	{
		$value=(bool)$value;
		if($this->_enabled!=$value && $this->_owner)
		{
			if($value)
				$this->_attachEventHandlers();
			else
			{
				foreach($this->events() as $event=>$handler)
					$this->_owner->detachEventHandler($event,array($this,$handler));
			}
		}
		$this->_enabled=$value;
	}

	private function _attachEventHandlers()
	{
		$class=new ReflectionClass($this);
		foreach($this->events() as $event=>$handler)
		{
			if($class->getMethod($handler)->isPublic())
				$this->_owner->attachEventHandler($event,array($this,$handler));
		}
	}
}
