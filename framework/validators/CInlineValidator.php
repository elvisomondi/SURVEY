<?php

class CInlineValidator extends CValidator
{
	/**
	 * @var string the name of the validation method defined in the active record class
	 */
	public $method;
	/**
	 * @var array additional parameters that are passed to the validation method
	 */
	public $params;
	/**
	 * @var string the name of the method that returns the client validation code (See {@link clientValidateAttribute}).
	 */
	public $clientValidate;

	
	protected function validateAttribute($object,$attribute)
	{
		$method=$this->method;
		$object->$method($attribute,$this->params);
	}

	public function clientValidateAttribute($object,$attribute)
	{
		if($this->clientValidate!==null)
		{
			$method=$this->clientValidate;
			return $object->$method($attribute,$this->params);
		}
	}
}
