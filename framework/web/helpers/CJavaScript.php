<?php

class CJavaScript
{
	/**
	 * Quotes a javascript string.
	 */
	public static function quote($js,$forUrl=false)
	{
		if($forUrl)
			return strtr($js,array('%'=>'%25',"\t"=>'\t',"\n"=>'\n',"\r"=>'\r','"'=>'\"','\''=>'\\\'','\\'=>'\\\\','</'=>'<\/'));
		else
			return strtr($js,array("\t"=>'\t',"\n"=>'\n',"\r"=>'\r','"'=>'\"','\''=>'\\\'','\\'=>'\\\\','</'=>'<\/'));
	}

	/**
	 * Encodes a PHP variable into javascript representation.
	 */
	public static function encode($value,$safe=false)
	{
		if(is_string($value))
		{
			if(strpos($value,'js:')===0 && $safe===false)
				return substr($value,3);
			else
				return "'".self::quote($value)."'";
		}
		elseif($value===null)
			return 'null';
		elseif(is_bool($value))
			return $value?'true':'false';
		elseif(is_integer($value))
			return "$value";
		elseif(is_float($value))
		{
			if($value===-INF)
				return 'Number.NEGATIVE_INFINITY';
			elseif($value===INF)
				return 'Number.POSITIVE_INFINITY';
			else
				return str_replace(',','.',(float)$value);  // locale-independent representation
		}
		elseif($value instanceof CJavaScriptExpression)
			return $value->__toString();
		elseif(is_object($value))
			return self::encode(get_object_vars($value),$safe);
		elseif(is_array($value))
		{
			$es=array();
			if(($n=count($value))>0 && array_keys($value)!==range(0,$n-1))
			{
				foreach($value as $k=>$v)
					$es[]="'".self::quote($k)."':".self::encode($v,$safe);
				return '{'.implode(',',$es).'}';
			}
			else
			{
				foreach($value as $v)
					$es[]=self::encode($v,$safe);
				return '['.implode(',',$es).']';
			}
		}
		else
			return '';
	}

	/**
	 * Returns the JSON representation of the PHP data.
	 * @param mixed $data the data to be encoded
	 * @return string the JSON representation of the PHP data.
	 */
	public static function jsonEncode($data)
	{
		return CJSON::encode($data);
	}

	/**
	 * Decodes a JSON string.
	 * @param string $data the data to be decoded
	 * @param boolean $useArray whether to use associative array to represent object data
	 * @return mixed the decoded PHP data
	 */
	public static function jsonDecode($data,$useArray=true)
	{
		return CJSON::decode($data,$useArray);
	}
}
