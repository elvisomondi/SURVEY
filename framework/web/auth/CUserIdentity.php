<?php

class CUserIdentity extends CBaseUserIdentity
{
	/**
	 * @var string username
	 */
	public $username;
	/**
	 * @var string password
	 */
	public $password;

	/**
	 * Constructor.
	 * @param string $username username
	 * @param string $password password
	 */
	public function __construct($username,$password)
	{
		$this->username=$username;
		$this->password=$password;
	}

	
	public function authenticate()
	{
		throw new CException(Yii::t('yii','{class}::authenticate() must be implemented.',array('{class}'=>get_class($this))));
	}


	public function getId()
	{
		return $this->username;
	}

	
	public function getName()
	{
		return $this->username;
	}
}
