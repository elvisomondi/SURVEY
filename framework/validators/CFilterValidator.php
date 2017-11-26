<?php

/**
 * CFilterValidator transforms the data being validated based on a filter.
 * CFilterValidator is actually not a validator but a data processor.
 */
class CFilterValidator extends CValidator
{
	/**
	 * @var callback the filter method
	 */
	public $filter;

	
	protected function validateAttribute($object,$attribute)
	{
		if($this->filter===null || !is_callable($this->filter))
			throw new CException(Yii::t('yii','The "filter" property must be specified with a valid callback.'));
		$object->$attribute=call_user_func_array($this->filter,array($object->$attribute));
	}
}
