<?php

/**
 * CDbException represents an exception that is caused by some DB-related operations.

 */
class CDbException extends CException
{
	/**
	 * @var mixed the error info provided by a PDO exception. This is the same as returned
	 */
	public $errorInfo;

	public function __construct($message,$code=0,$errorInfo=null)
	{
		$this->errorInfo=$errorInfo;
		parent::__construct($message,$code);
	}
}