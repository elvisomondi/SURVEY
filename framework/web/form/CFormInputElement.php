<?php
class CFormInputElement extends CFormElement
{
	/**
	 * @var array Core input types (alias=>CHtml method name)
	 */
	public static $coreTypes=array(
		'text'=>'activeTextField',
		'hidden'=>'activeHiddenField',
		'password'=>'activePasswordField',
		'textarea'=>'activeTextArea',
		'file'=>'activeFileField',
		'radio'=>'activeRadioButton',
		'checkbox'=>'activeCheckBox',
		'listbox'=>'activeListBox',
		'dropdownlist'=>'activeDropDownList',
		'checkboxlist'=>'activeCheckBoxList',
		'radiolist'=>'activeRadioButtonList',
		'url'=>'activeUrlField',
		'email'=>'activeEmailField',
		'number'=>'activeNumberField',
		'range'=>'activeRangeField',
		'date'=>'activeDateField',
		'time'=>'activeTimeField',
		'datetime'=>'activeDateTimeField',
		'datetimelocal'=>'activeDateTimeLocalField',
		'week'=>'activeWeekField',
		'color'=>'activeColorField',
		'tel'=>'activeTelField',
		'search'=>'activeSearchField',
	);

	/**
	 * @var string the type of this input. This can be a widget class name, a path alias of a widget class name,
	 * or an input type alias (text, hidden, password, textarea, file, radio, checkbox, listbox, dropdownlist, checkboxlist, or radiolist).
	 * If a widget class, it must extend from {@link CInputWidget} or (@link CJuiInputWidget).
	 */
	public $type;
	/**
	 * @var string name of this input
	 */
	public $name;
	/**
	 * @var string hint text of this input
	 */
	public $hint;
	/**
	 * @var array the options for this input when it is a list box, drop-down list, check box list, or radio button list.
	 * Please see {@link CHtml::listData} for details of generating this property value.
	 */
	public $items=array();
	
	public $errorOptions=array();
	
	public $enableAjaxValidation=true;
	
	public $enableClientValidation=true;
	/**
	 * @var string the layout used to render label, input, hint and error. They correspond to the placeholders
	 * "{label}", "{input}", "{hint}" and "{error}".
	 */
	public $layout="{label}\n{input}\n{hint}\n{error}";

	private $_label;
	private $_required;


	public function getRequired()
	{
		if($this->_required!==null)
			return $this->_required;
		else
			return $this->getParent()->getModel()->isAttributeRequired($this->name);
	}

	/**
	 * @param boolean $value whether this input is required.
	 */
	public function setRequired($value)
	{
		$this->_required=$value;
	}

	/**
	 * @return string the label for this input. If the label is not manually set,
	 * this method will call {@link CModel::getAttributeLabel} to determine the label.
	 */
	public function getLabel()
	{
		if($this->_label!==null)
			return $this->_label;
		else
			return $this->getParent()->getModel()->getAttributeLabel($this->name);
	}

	/**
	 * @param string $value the label for this input
	 */
	public function setLabel($value)
	{
		$this->_label=$value;
	}

	
	public function render()
	{
		if($this->type==='hidden')
			return $this->renderInput();
		$output=array(
			'{label}'=>$this->renderLabel(),
			'{input}'=>$this->renderInput(),
			'{hint}'=>$this->renderHint(),
			'{error}'=>!$this->getParent()->showErrors ? '' : $this->renderError(),
		);
		return strtr($this->layout,$output);
	}

	public function renderLabel()
	{
		$options = array(
			'label'=>$this->getLabel(),
			'required'=>$this->getRequired()
		);

		if(!empty($this->attributes['id']))
			$options['for']=$this->attributes['id'];

		return CHtml::activeLabel($this->getParent()->getModel(), $this->name, $options);
	}

	
	public function renderInput()
	{
		if(isset(self::$coreTypes[$this->type]))
		{
			$method=self::$coreTypes[$this->type];
			if(strpos($method,'List')!==false)
				return CHtml::$method($this->getParent()->getModel(), $this->name, $this->items, $this->attributes);
			else
				return CHtml::$method($this->getParent()->getModel(), $this->name, $this->attributes);
		}
		else
		{
			$attributes=$this->attributes;
			$attributes['model']=$this->getParent()->getModel();
			$attributes['attribute']=$this->name;
			ob_start();
			$this->getParent()->getOwner()->widget($this->type, $attributes);
			return ob_get_clean();
		}
	}

	
	public function renderError()
	{
		$parent=$this->getParent();
		return $parent->getActiveFormWidget()->error($parent->getModel(), $this->name, $this->errorOptions, $this->enableAjaxValidation, $this->enableClientValidation);
	}

	
	public function renderHint()
	{
		return $this->hint===null ? '' : '<div class="hint">'.$this->hint.'</div>';
	}


	protected function evaluateVisible()
	{
		return $this->getParent()->getModel()->isAttributeSafe($this->name);
	}
}
