<?php

/**
 * CModel is the base class providing the common features needed by data model objects.
 * CModel defines the basic framework for data models that need to be validated.
 */
abstract class CModel extends CComponent implements IteratorAggregate, ArrayAccess
{
	private $_errors=array();	// attribute name => array of errors
	private $_validators;  		// validators
	private $_scenario='';  	// scenario

	
	abstract public function attributeNames();

	/**
	 * Returns the validation rules for attributes.
	 */
	public function rules()
	{
		return array();
	}

	/**
	 * Returns a list of behaviors that this model should behave as.
	 */
	public function behaviors()
	{
		return array();
	}

	/**
	 * Returns the attribute labels.
	 */
	public function attributeLabels()
	{
		return array();
	}

	/**
	 * Performs the validation.
	 */
	public function validate($attributes=null, $clearErrors=true)
	{
		if($clearErrors)
			$this->clearErrors();
		if($this->beforeValidate())
		{
			foreach($this->getValidators() as $validator)
				$validator->validate($this,$attributes);
			$this->afterValidate();
			return !$this->hasErrors();
		}
		else
			return false;
	}

	/**
	 * This method is invoked after a model instance is created by new operator.
	 */
	protected function afterConstruct()
	{
		if($this->hasEventHandler('onAfterConstruct'))
			$this->onAfterConstruct(new CEvent($this));
	}

	/**
	 * This method is invoked before validation starts.
	 */
	protected function beforeValidate()
	{
		$event=new CModelEvent($this);
		$this->onBeforeValidate($event);
		return $event->isValid;
	}

	/**
	 * This method is invoked after validation ends.
	 */
	protected function afterValidate()
	{
		$this->onAfterValidate(new CEvent($this));
	}

	
	public function onAfterConstruct($event)
	{
		$this->raiseEvent('onAfterConstruct',$event);
	}

	/**
	 * This event is raised before the validation is performed.
	 * @param CModelEvent $event the event parameter
	 */
	public function onBeforeValidate($event)
	{
		$this->raiseEvent('onBeforeValidate',$event);
	}

	/**
	 * This event is raised after the validation is performed.
	 * @param CEvent $event the event parameter
	 */
	public function onAfterValidate($event)
	{
		$this->raiseEvent('onAfterValidate',$event);
	}

	
	public function getValidatorList()
	{
		if($this->_validators===null)
			$this->_validators=$this->createValidators();
		return $this->_validators;
	}


	public function getValidators($attribute=null)
	{
		if($this->_validators===null)
			$this->_validators=$this->createValidators();

		$validators=array();
		$scenario=$this->getScenario();
		foreach($this->_validators as $validator)
		{
			if($validator->applyTo($scenario))
			{
				if($attribute===null || in_array($attribute,$validator->attributes,true))
					$validators[]=$validator;
			}
		}
		return $validators;
	}

	
	public function createValidators()
	{
		$validators=new CList;
		foreach($this->rules() as $rule)
		{
			if(isset($rule[0],$rule[1]))  // attributes, validator name
				$validators->add(CValidator::createValidator($rule[1],$this,$rule[0],array_slice($rule,2)));
			else
				throw new CException(Yii::t('yii','{class} has an invalid validation rule. The rule must specify attributes to be validated and the validator name.',
					array('{class}'=>get_class($this))));
		}
		return $validators;
	}

	
	public function isAttributeRequired($attribute)
	{
		foreach($this->getValidators($attribute) as $validator)
		{
			if($validator instanceof CRequiredValidator)
				return true;
		}
		return false;
	}

	
	public function isAttributeSafe($attribute)
	{
		$attributes=$this->getSafeAttributeNames();
		return in_array($attribute,$attributes);
	}

	
	public function getAttributeLabel($attribute)
	{
		$labels=$this->attributeLabels();
		if(isset($labels[$attribute]))
			return $labels[$attribute];
		else
			return $this->generateAttributeLabel($attribute);
	}

	
	public function hasErrors($attribute=null)
	{
		if($attribute===null)
			return $this->_errors!==array();
		else
			return isset($this->_errors[$attribute]);
	}

	
	public function getErrors($attribute=null)
	{
		if($attribute===null)
			return $this->_errors;
		else
			return isset($this->_errors[$attribute]) ? $this->_errors[$attribute] : array();
	}

	
	public function getError($attribute)
	{
		return isset($this->_errors[$attribute]) ? reset($this->_errors[$attribute]) : null;
	}

	
	public function addError($attribute,$error)
	{
		$this->_errors[$attribute][]=$error;
	}

	
	public function addErrors($errors)
	{
		foreach($errors as $attribute=>$error)
		{
			if(is_array($error))
			{
				foreach($error as $e)
					$this->addError($attribute, $e);
			}
			else
				$this->addError($attribute, $error);
		}
	}

	
	public function clearErrors($attribute=null)
	{
		if($attribute===null)
			$this->_errors=array();
		else
			unset($this->_errors[$attribute]);
	}

	public function generateAttributeLabel($name)
	{
		return ucwords(trim(strtolower(str_replace(array('-','_','.'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)))));
	}

	
	public function getAttributes($names=null)
	{
		$values=array();
		foreach($this->attributeNames() as $name)
			$values[$name]=$this->$name;

		if(is_array($names))
		{
			$values2=array();
			foreach($names as $name)
				$values2[$name]=isset($values[$name]) ? $values[$name] : null;
			return $values2;
		}
		else
			return $values;
	}

	
	public function setAttributes($values,$safeOnly=true)
	{
		if(!is_array($values))
			return;
		$attributes=array_flip($safeOnly ? $this->getSafeAttributeNames() : $this->attributeNames());
		foreach($values as $name=>$value)
		{
			if(isset($attributes[$name]))
				$this->$name=$value;
			elseif($safeOnly)
				$this->onUnsafeAttribute($name,$value);
		}
	}

	/**
	 * Sets the attributes to be null.
	 * @param array $names list of attributes to be set null. If this parameter is not given,
	 * all attributes as specified by {@link attributeNames} will have their values unset.
	 * @since 1.1.3
	 */
	public function unsetAttributes($names=null)
	{
		if($names===null)
			$names=$this->attributeNames();
		foreach($names as $name)
			$this->$name=null;
	}

	/**
	 * This method is invoked when an unsafe attribute is being massively assigned.
	 */
	public function onUnsafeAttribute($name,$value)
	{
		if(YII_DEBUG)
			Yii::log(Yii::t('yii','Failed to set unsafe attribute "{attribute}" of "{class}".',array('{attribute}'=>$name, '{class}'=>get_class($this))),CLogger::LEVEL_WARNING);
	}

	
	public function getScenario()
	{
		return $this->_scenario;
	}

	/**
	 * Sets the scenario for the model.
	 * @param string $value the scenario that this model is in.
	 * @see getScenario
	 */
	public function setScenario($value)
	{
		$this->_scenario=$value;
	}

	/**
	 * Returns the attribute names that are safe to be massively assigned.
	 * A safe attribute is one that is associated with a validation rule in the current {@link scenario}.
	 * @return array safe attribute names
	 */
	public function getSafeAttributeNames()
	{
		$attributes=array();
		$unsafe=array();
		foreach($this->getValidators() as $validator)
		{
			if(!$validator->safe)
			{
				foreach($validator->attributes as $name)
					$unsafe[]=$name;
			}
			else
			{
				foreach($validator->attributes as $name)
					$attributes[$name]=true;
			}
		}

		foreach($unsafe as $name)
			unset($attributes[$name]);
		return array_keys($attributes);
	}

	/**
	 * Returns an iterator for traversing the attributes in the model.
	 * This method is required by the interface IteratorAggregate.
	 * @return CMapIterator an iterator for traversing the items in the list.
	 */
	public function getIterator()
	{
		$attributes=$this->getAttributes();
		return new CMapIterator($attributes);
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 */
	public function offsetExists($offset)
	{
		return property_exists($this,$offset);
	}

	/**
	 * Returns the element at the specified offset.
	 */
	public function offsetGet($offset)
	{
		return $this->$offset;
	}

	/**
	 * Sets the element at the specified offset.
	 */
	public function offsetSet($offset,$item)
	{
		$this->$offset=$item;
	}

	/**
	 * Unsets the element at the specified offset.
	 */
	public function offsetUnset($offset)
	{
		unset($this->$offset);
	}
}
