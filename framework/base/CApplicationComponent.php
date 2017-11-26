<?php

/**
 * CApplicationComponent is the base class for application component classes.
 */
abstract class CApplicationComponent extends CComponent implements IApplicationComponent
{
	/**
	 * @var array the behaviors that should be attached to this component.
	 */
	public $behaviors=array();

	private $_initialized=false;

	/**
	 * Initializes the application component.
	 */
	public function init()
	{
		$this->attachBehaviors($this->behaviors);
		$this->_initialized=true;
	}

	/**
	 * Checks if this application component has been initialized.
	 */
	public function getIsInitialized()
	{
		return $this->_initialized;
	}
}
