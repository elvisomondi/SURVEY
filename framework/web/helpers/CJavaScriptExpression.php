<?php
/**
 * CJavaScriptExpression represents a JavaScript expression that does not need escaping.
 */
class CJavaScriptExpression
{
	/**
	 * @var string the javascript expression wrapped by this object
	 */
	public $code;

	/**
	 * @param string $code a javascript expression that is to be wrapped by this object
	 * @throws CException if argument is not a string
	 */
	public function __construct($code)
	{
		if(!is_string($code))
			throw new CException('Value passed to CJavaScriptExpression should be a string.');
		if(strpos($code, 'js:')===0)
			$code=substr($code,3);
		$this->code=$code;
	}

	/**
	 * String magic method
	 * @return string the javascript expression wrapped by this object
	 */
	public function __toString()
	{
		return $this->code;
	}
}