<?php
/**
 * CBooleanValidator validates that the attribute value is either {@link trueValue}  or {@link falseValue}.
 */
class CBooleanValidator extends CValidator
{
	/**
	 * @var mixed the value representing true status.
	 */
	public $trueValue='1';
	/**
	 * @var mixed the value representing false status.
	 */
	public $falseValue='0';
	public $strict=false;
	
	public $allowEmpty=true;

	
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;

		if(!$this->validateValue($value))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be either {true} or {false}.');
			$this->addError($object,$attribute,$message,array(
				'{true}'=>$this->trueValue,
				'{false}'=>$this->falseValue,
			));
		}
	}
	
	
	public function validateValue($value)
	{
		if ($this->strict)
			return $value===$this->trueValue || $value===$this->falseValue;
		else
			return $value==$this->trueValue || $value==$this->falseValue;
	}

	
	public function clientValidateAttribute($object,$attribute)
	{
		$message=$this->message!==null ? $this->message : Yii::t('yii','{attribute} must be either {true} or {false}.');
		$message=strtr($message, array(
			'{attribute}'=>$object->getAttributeLabel($attribute),
			'{true}'=>$this->trueValue,
			'{false}'=>$this->falseValue,
		));
		return "
if(".($this->allowEmpty ? "jQuery.trim(value)!='' && " : '')."value!=".CJSON::encode($this->trueValue)." && value!=".CJSON::encode($this->falseValue).") {
	messages.push(".CJSON::encode($message).");
}
";
	}
}
