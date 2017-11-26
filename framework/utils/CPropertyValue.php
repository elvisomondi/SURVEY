<?php
/**
 * CPropertyValue is a helper class that provides static methods to convert component property values to specific types.
 */
class CPropertyValue
{
	/**
	 * Converts a value to boolean type.
	 */
	public static function ensureBoolean($value)
	{
		if (is_string($value))
			return !strcasecmp($value,'true') || $value!=0;
		else
			return (boolean)$value;
	}

	/**
	 * Converts a value to string type.
	 */
	public static function ensureString($value)
	{
		if (is_bool($value))
			return $value?'true':'false';
		else
			return (string)$value;
	}

	/**
	 * Converts a value to integer type.
	 * @param mixed $value the value to be converted.
	 * @return integer
	 */
	public static function ensureInteger($value)
	{
		return (integer)$value;
	}

	/**
	 * Converts a value to float type.
	 * @param mixed $value the value to be converted.
	 * @return float
	 */
	public static function ensureFloat($value)
	{
		return (float)$value;
	}

	
	public static function ensureArray($value)
	{
		if(is_string($value))
		{
			$value = trim($value);
			$len = strlen($value);
			if ($len >= 2 && $value[0] == '(' && $value[$len-1] == ')')
			{
				eval('$array=array'.$value.';');
				return $array;
			}
			else
				return $len>0?array($value):array();
		}
		else
			return (array)$value;
	}

	/**
	 * Converts a value to object type.
	 * @param mixed $value the value to be converted.
	 * @return object
	 */
	public static function ensureObject($value)
	{
		return (object)$value;
	}

	public static function ensureEnum($value,$enumType)
	{
		static $types=array();
		if(!isset($types[$enumType]))
			$types[$enumType]=new ReflectionClass($enumType);
		if($types[$enumType]->hasConstant($value))
			return $value;
		else
			throw new CException(Yii::t('yii','Invalid enumerable value "{value}". Please make sure it is among ({enum}).',
				array('{value}'=>$value, '{enum}'=>implode(', ',$types[$enumType]->getConstants()))));
	}
}
