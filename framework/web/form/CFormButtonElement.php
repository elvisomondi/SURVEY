<?php


/**
 * CFormButtonElement represents a form button element.
 *
 * CFormButtonElement can represent the following types of button based on {@link type} property:
 
 */
class CFormButtonElement extends CFormElement
{
	/**
	 * @var array Core button types (alias=>CHtml method name)
	 */
	public static $coreTypes=array(
		'htmlButton'=>'htmlButton',
		'htmlSubmit'=>'htmlButton',
		'htmlReset'=>'htmlButton',
		'button'=>'button',
		'submit'=>'submitButton',
		'reset'=>'resetButton',
		'image'=>'imageButton',
		'link'=>'linkButton',
	);

	/**
	 * @var string the type of this button. This can be a class name, a path alias of a class name,
	 * or a button type alias (submit, button, image, reset, link, htmlButton, htmlSubmit, htmlReset).
	 */
	public $type;
	/**
	 * @var string name of this button
	 */
	public $name;
	/**
	 * @var string the label of this button. This property is ignored when a widget is used to generate the button.
	 */
	public $label;

	private $_on;


	public function getOn()
	{
		return $this->_on;
	}

	/**
	 * @param string $value scenario names separated by commas.
	 */
	public function setOn($value)
	{
		$this->_on=preg_split('/[\s,]+/',$value,-1,PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Returns this button.
	 * @return string the rendering result
	 */
	public function render()
	{
		$attributes=$this->attributes;
		if(isset(self::$coreTypes[$this->type]))
		{
			$method=self::$coreTypes[$this->type];
			if($method==='linkButton')
			{
				if(!isset($attributes['params'][$this->name]))
					$attributes['params'][$this->name]=1;
			}
			elseif($method==='htmlButton')
			{
				$attributes['type']=$this->type==='htmlSubmit' ? 'submit' : ($this->type==='htmlReset' ? 'reset' : 'button');
				$attributes['name']=$this->name;
			}
			else
				$attributes['name']=$this->name;
			if($method==='imageButton')
				return CHtml::imageButton(isset($attributes['src']) ? $attributes['src'] : '',$attributes);
			else
				return CHtml::$method($this->label,$attributes);
		}
		else
		{
			$attributes['name']=$this->name;
			ob_start();
			$this->getParent()->getOwner()->widget($this->type, $attributes);
			return ob_get_clean();
		}
	}

	/**
	 * Evaluates the visibility of this element.
	 * This method will check the {@link on} property to see if
	 * the model is in a scenario that should have this string displayed.
	 * @return boolean whether this element is visible.
	 */
	protected function evaluateVisible()
	{
		return empty($this->_on) || in_array($this->getParent()->getModel()->getScenario(),$this->_on);
	}
}
