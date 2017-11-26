<?php

abstract class CFormElement extends CComponent
{
	/**
	 * @var array list of attributes (name=>value) for the HTML element represented by this object.
	 */
	public $attributes=array();

	private $_parent;
	private $_visible;

	/**
	 * Renders this element.
	 * @return string the rendering result
	 */
	abstract function render();


	public function __construct($config,$parent)
	{
		$this->configure($config);
		$this->_parent=$parent;
	}

	
	public function __toString()
	{
		return $this->render();
	}

	
	public function __get($name)
	{
		$getter='get'.$name;
		if(method_exists($this,$getter))
			return $this->$getter();
		elseif(isset($this->attributes[$name]))
			return $this->attributes[$name];
		else
			throw new CException(Yii::t('yii','Property "{class}.{property}" is not defined.',
				array('{class}'=>get_class($this), '{property}'=>$name)));
	}

	
	public function __isset($name)
	{
		$getter='get'.$name;
		if(method_exists($this,$getter))
			return $this->$getter()!==null;
		elseif(isset($this->attributes[$name]))
			return isset($this->attributes[$name]);
		else
			return false;
	}

	public function __set($name,$value)
	{
		$setter='set'.$name;
		if(method_exists($this,$setter))
			$this->$setter($value);
		else
			$this->attributes[$name]=$value;
	}

	
	public function configure($config)
	{
		if(is_string($config))
			$config=require(Yii::getPathOfAlias($config).'.php');
		if(is_array($config))
		{
			foreach($config as $name=>$value)
				$this->$name=$value;
		}
	}

	
	public function getVisible()
	{
		if($this->_visible===null)
			$this->_visible=$this->evaluateVisible();
		return $this->_visible;
	}

	/**
	 * @param boolean $value whether this element is visible and should be rendered.
	 */
	public function setVisible($value)
	{
		$this->_visible=$value;
	}

	/**
	 * @return mixed the direct parent of this element. This could be either a {@link CForm} object or a {@link CBaseController} object
	 * (a controller or a widget).
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	
	protected function evaluateVisible()
	{
		return true;
	}
}
