<?php

class CAuthAssignment extends CComponent
{
	private $_auth;
	private $_itemName;
	private $_userId;
	private $_bizRule;
	private $_data;


	public function __construct($auth,$itemName,$userId,$bizRule=null,$data=null)
	{
		$this->_auth=$auth;
		$this->_itemName=$itemName;
		$this->_userId=$userId;
		$this->_bizRule=$bizRule;
		$this->_data=$data;
	}

	/**
	 * @return mixed user ID (see {@link IWebUser::getId})
	 */
	public function getUserId()
	{
		return $this->_userId;
	}

	/**
	 * @return string the authorization item name
	 */
	public function getItemName()
	{
		return $this->_itemName;
	}

	/**
	 * @return string the business rule associated with this assignment
	 */
	public function getBizRule()
	{
		return $this->_bizRule;
	}

	/**
	 * @param string $value the business rule associated with this assignment
	 */
	public function setBizRule($value)
	{
		if($this->_bizRule!==$value)
		{
			$this->_bizRule=$value;
			$this->_auth->saveAuthAssignment($this);
		}
	}

	/**
	 * @return mixed additional data for this assignment
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * @param mixed $value additional data for this assignment
	 */
	public function setData($value)
	{
		if($this->_data!==$value)
		{
			$this->_data=$value;
			$this->_auth->saveAuthAssignment($this);
		}
	}
}