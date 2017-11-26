<?php

/**
 * CForm represents a form object that contains form input specifications.
 *
 * The main purpose of introducing the abstraction of form objects is to enhance the
 * reusability of forms.
 */
class CForm extends CFormElement implements ArrayAccess
{
	/**
	 * @var string the title for this form. By default, if this is set, a fieldset may be rendered
	 * around the form body using the title as its legend. Defaults to null.
	 */
	public $title;
	/**
	 * @var string the description of this form.
	 */
	public $description;
	/**
	 * @var string the submission method of this form. Defaults to 'post'.
	 * This property is ignored when this form is a sub-form.
	 */
	public $method='post';
	
	public $action='';
	/**
	 * @var string the name of the class for representing a form input element. Defaults to 'CFormInputElement'.
	 */
	public $inputElementClass='CFormInputElement';
	/**
	 * @var string the name of the class for representing a form button element. Defaults to 'CFormButtonElement'.
	 */
	public $buttonElementClass='CFormButtonElement';
	/**
	 * @var array HTML attribute values for the form tag. When the form is embedded within another form,
	 * this property will be used to render the HTML attribute values for the fieldset enclosing the child form.
	 */
	public $attributes=array();
	/**
	 * @var boolean whether to show error summary. Defaults to false.
	 */
	public $showErrorSummary=false;
	
	public $showErrors;
	/**
	 * @var string|null HTML code to prepend to the list of errors in the error summary. See {@link CActiveForm::errorSummary()}.
	 */
	public $errorSummaryHeader;
	/**
	 * @var string|null HTML code to append to the list of errors in the error summary. See {@link CActiveForm::errorSummary()}.
	 */
	public $errorSummaryFooter;
	
	public $activeForm=array('class'=>'CActiveForm');

	private $_model;
	private $_elements;
	private $_buttons;
	private $_activeForm;

	
	public function __construct($config,$model=null,$parent=null)
	{
		$this->setModel($model);
		if($parent===null)
			$parent=Yii::app()->getController();
		parent::__construct($config,$parent);
		if($this->showErrors===null)
			$this->showErrors=!$this->showErrorSummary;
		$this->init();
	}

	
	protected function init()
	{
	}

	
	public function submitted($buttonName='submit',$loadData=true)
	{
		$ret=$this->clicked($this->getUniqueId()) && $this->clicked($buttonName);
		if($ret && $loadData)
			$this->loadData();
		return $ret;
	}

	
	public function clicked($name)
	{
		if(strcasecmp($this->getRoot()->method,'get'))
			return isset($_POST[$name]);
		else
			return isset($_GET[$name]);
	}


	public function validate()
	{
		$ret=true;
		foreach($this->getModels() as $model)
			$ret=$model->validate() && $ret;
		return $ret;
	}

	
	public function loadData()
	{
		if($this->_model!==null)
		{
			$class=CHtml::modelName($this->_model);
			if(strcasecmp($this->getRoot()->method,'get'))
			{
				if(isset($_POST[$class]))
					$this->_model->setAttributes($_POST[$class]);
			}
			elseif(isset($_GET[$class]))
				$this->_model->setAttributes($_GET[$class]);
		}
		foreach($this->getElements() as $element)
		{
			if($element instanceof self)
				$element->loadData();
		}
	}

	/**
	 * @return CForm the top-level form object
	 */
	public function getRoot()
	{
		$root=$this;
		while($root->getParent() instanceof self)
			$root=$root->getParent();
		return $root;
	}

	/**
	 * @return CActiveForm the active form widget associated with this form.
	 * This method will return the active form widget as specified by {@link activeForm}.
	 * @since 1.1.1
	 */
	public function getActiveFormWidget()
	{
		if($this->_activeForm!==null)
			return $this->_activeForm;
		else
			return $this->getRoot()->_activeForm;
	}

	/**
	 * @return CBaseController the owner of this form. This refers to either a controller or a widget
	 * by which the form is created and rendered.
	 */
	public function getOwner()
	{
		$owner=$this->getParent();
		while($owner instanceof self)
			$owner=$owner->getParent();
		return $owner;
	}

	/**
	 * Returns the model that this form is associated with.
	 * @param boolean $checkParent whether to return parent's model if this form doesn't have model by itself.
	 * @return CModel the model associated with this form. If this form does not have a model,
	 * it will look for a model in its ancestors.
	 */
	public function getModel($checkParent=true)
	{
		if(!$checkParent)
			return $this->_model;
		$form=$this;
		while($form->_model===null && $form->getParent() instanceof self)
			$form=$form->getParent();
		return $form->_model;
	}

	/**
	 * @param CModel $model the model to be associated with this form
	 */
	public function setModel($model)
	{
		$this->_model=$model;
	}

	/**
	 * Returns all models that are associated with this form or its sub-forms.
	 * @return array the models that are associated with this form or its sub-forms.
	 */
	public function getModels()
	{
		$models=array();
		if($this->_model!==null)
			$models[]=$this->_model;
		foreach($this->getElements() as $element)
		{
			if($element instanceof self)
				$models=array_merge($models,$element->getModels());
		}
		return $models;
	}

	public function getElements()
	{
		if($this->_elements===null)
			$this->_elements=new CFormElementCollection($this,false);
		return $this->_elements;
	}

	
	public function setElements($elements)
	{
		$collection=$this->getElements();
		foreach($elements as $name=>$config)
			$collection->add($name,$config);
	}

	
	public function getButtons()
	{
		if($this->_buttons===null)
			$this->_buttons=new CFormElementCollection($this,true);
		return $this->_buttons;
	}

	/**
	 * Configures the buttons of this form.
	 * The configuration must be an array of button configuration array indexed by button name.
	 * Each button configuration array consists of name-value pairs that are used to initialize
	 * a {@link CFormButtonElement} object.
	 * @param array $buttons the button configurations
	 */
	public function setButtons($buttons)
	{
		$collection=$this->getButtons();
		foreach($buttons as $name=>$config)
			$collection->add($name,$config);
	}

	/**
	 * Renders the form.
	 * The default implementation simply calls {@link renderBegin}, {@link renderBody} and {@link renderEnd}.
	 * @return string the rendering result
	 */
	public function render()
	{
		return $this->renderBegin() . $this->renderBody() . $this->renderEnd();
	}

	/**
	 * Renders the open tag of the form.
	 * The default implementation will render the open form tag.
	 * @return string the rendering result
	 */
	public function renderBegin()
	{
		if($this->getParent() instanceof self)
			return '';
		else
		{
			$options=$this->activeForm;
			if(isset($options['class']))
			{
				$class=$options['class'];
				unset($options['class']);
			}
			else
				$class='CActiveForm';
			$options['action']=$this->action;
			$options['method']=$this->method;
			if(isset($options['htmlOptions']))
			{
				foreach($this->attributes as $name=>$value)
					$options['htmlOptions'][$name]=$value;
			}
			else
				$options['htmlOptions']=$this->attributes;
			ob_start();
			$this->_activeForm=$this->getOwner()->beginWidget($class, $options);
			return ob_get_clean() . "<div style=\"display:none\">".CHtml::hiddenField($this->getUniqueID(),1)."</div>\n";
		}
	}

	/**
	 * Renders the close tag of the form.
	 * @return string the rendering result
	 */
	public function renderEnd()
	{
		if($this->getParent() instanceof self)
			return '';
		else
		{
			ob_start();
			$this->getOwner()->endWidget();
			return ob_get_clean();
		}
	}

	
	public function renderBody()
	{
		$output='';
		if($this->title!==null)
		{
			if($this->getParent() instanceof self)
			{
				$attributes=$this->attributes;
				unset($attributes['name'],$attributes['type']);
				$output=CHtml::openTag('fieldset', $attributes)."<legend>".$this->title."</legend>\n";
			}
			else
				$output="<fieldset>\n<legend>".$this->title."</legend>\n";
		}

		if($this->description!==null)
			$output.="<div class=\"description\">\n".$this->description."</div>\n";

		if($this->showErrorSummary && ($model=$this->getModel(false))!==null)
			$output.=$this->getActiveFormWidget()->errorSummary($model,$this->errorSummaryHeader,$this->errorSummaryFooter)."\n";

		$output.=$this->renderElements()."\n".$this->renderButtons()."\n";

		if($this->title!==null)
			$output.="</fieldset>\n";

		return $output;
	}

	/**
	 * Renders the {@link elements} in this form.
	 * @return string the rendering result
	 */
	public function renderElements()
	{
		$output='';
		foreach($this->getElements() as $element)
			$output.=$this->renderElement($element);
		return $output;
	}

	/**
	 * Renders the {@link buttons} in this form.
	 * @return string the rendering result
	 */
	public function renderButtons()
	{
		$output='';
		foreach($this->getButtons() as $button)
			$output.=$this->renderElement($button);
		return $output!=='' ? "<div class=\"row buttons\">".$output."</div>\n" : '';
	}

	/**
	 * Renders a single element which could be an input element, a sub-form, a string, or a button.
	 * @param mixed $element the form element to be rendered. This can be either a {@link CFormElement} instance
	 * or a string representing the name of the form element.
	 * @return string the rendering result
	 */
	public function renderElement($element)
	{
		if(is_string($element))
		{
			if(($e=$this[$element])===null && ($e=$this->getButtons()->itemAt($element))===null)
				return $element;
			else
				$element=$e;
		}
		if($element->getVisible())
		{
			if($element instanceof CFormInputElement)
			{
				if($element->type==='hidden')
					return "<div style=\"display:none\">\n".$element->render()."</div>\n";
				else
					return "<div class=\"row field_{$element->name}\">\n".$element->render()."</div>\n";
			}
			elseif($element instanceof CFormButtonElement)
				return $element->render()."\n";
			else
				return $element->render();
		}
		return '';
	}

	
	public function addedElement($name,$element,$forButtons)
	{
	}

	
	public function removedElement($name,$element,$forButtons)
	{
	}

	
	protected function evaluateVisible()
	{
		foreach($this->getElements() as $element)
			if($element->getVisible())
				return true;
		return false;
	}

	/**
	 * Returns a unique ID that identifies this form in the current page.
	 * @return string the unique ID identifying this form
	 */
	protected function getUniqueId()
	{
		if(isset($this->attributes['id']))
			return 'yform_'.$this->attributes['id'];
		else
			return 'yform_'.sprintf('%x',crc32(serialize(array_keys($this->getElements()->toArray()))));
	}

	
	public function offsetExists($offset)
	{
		return $this->getElements()->contains($offset);
	}

	
	public function offsetGet($offset)
	{
		return $this->getElements()->itemAt($offset);
	}

	
	public function offsetSet($offset,$item)
	{
		$this->getElements()->add($offset,$item);
	}

	
	public function offsetUnset($offset)
	{
		$this->getElements()->remove($offset);
	}
}
