<?php

class LSYii_CompareInsensitiveValidator extends CValidator
{
	/**
	 * @var string the constant value to be compared with
	 */
	public $compareValue;
	
	public $allowEmpty=false;
	
	public $operator='=';

	
	protected function validateAttribute($object,$attribute)
	{
		$value=strtolower($object->$attribute);
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		if($this->compareValue!==null)
		{
			$compareTo=$this->compareValue;
			$compareValue=strtolower($compareTo);
		}
		else
		{
				throw new CException('compareValue must be set when using LSYii_CompareInsensitiveValidator');
		}
		switch($this->operator)
		{
			case '=':
			case '==':
				if($value!=$compareValue)
					$message=$this->message!==null? $this->message : sprintf(gT('%s must be case-insensitive equal to %s'),$attribute,$compareTo);
				break;
			case '!=':
				if($value==$compareValue)
					$message=$this->message!==null? $this->message : sprintf(gT('%s must not be case-insensitive equal to %s'),$attribute,$compareTo);
				break;
			default:
				throw new CException(Yii::t('yii','Invalid operator "{operator}".',array('{operator}'=>$this->operator)));
		}
		if(!empty($message))
		{
			$this->addError($object,$attribute,$message,array('{compareAttribute}'=>$compareTo,'{compareValue}'=>$compareValue));
		}
	}
}
