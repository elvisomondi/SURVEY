<?php
/**
 * CRequiredValidator validates that the specified attribute does not have null or empty value.
 */
class CRequiredValidator extends CValidator
{
	
	public $requiredValue;
	
	public $strict=false;
	
	public $trim=true;
	
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->requiredValue!==null)
		{
			if(!$this->strict && $value!=$this->requiredValue || $this->strict && $value!==$this->requiredValue)
			{
				$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be {value}.',
					array('{value}'=>$this->requiredValue));
				$this->addError($object,$attribute,$message);
			}
		}
		elseif($this->isEmpty($value,$this->trim))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} cannot be blank.');
			$this->addError($object,$attribute,$message);
		}
	}

	
	public function clientValidateAttribute($object,$attribute)
	{
		$message=$this->message;
		if($this->requiredValue!==null)
		{
			if($message===null)
				$message=Yii::t('yii','{attribute} must be {value}.');
			$message=strtr($message, array(
				'{value}'=>$this->requiredValue,
				'{attribute}'=>$object->getAttributeLabel($attribute),
			));
			return "
if(value!=" . CJSON::encode($this->requiredValue) . ") {
	messages.push(".CJSON::encode($message).");
}
";
		}
		else
		{
			if($message===null)
				$message=Yii::t('yii','{attribute} cannot be blank.');
			$message=strtr($message, array(
				'{attribute}'=>$object->getAttributeLabel($attribute),
			));
			if($this->trim)
				$emptyCondition = "jQuery.trim(value)==''";
			else
				$emptyCondition = "value==''";
			return "
if({$emptyCondition}) {
	messages.push(".CJSON::encode($message).");
}
";
		}
	}
}
