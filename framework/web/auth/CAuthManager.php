<?php

abstract class CAuthManager extends CApplicationComponent implements IAuthManager
{
	
	public $showErrors = false;

	
	public $defaultRoles=array();

	
	public function createRole($name,$description='',$bizRule=null,$data=null)
	{
		return $this->createAuthItem($name,CAuthItem::TYPE_ROLE,$description,$bizRule,$data);
	}

	public function createTask($name,$description='',$bizRule=null,$data=null)
	{
		return $this->createAuthItem($name,CAuthItem::TYPE_TASK,$description,$bizRule,$data);
	}

	
	public function createOperation($name,$description='',$bizRule=null,$data=null)
	{
		return $this->createAuthItem($name,CAuthItem::TYPE_OPERATION,$description,$bizRule,$data);
	}

	public function getRoles($userId=null)
	{
		return $this->getAuthItems(CAuthItem::TYPE_ROLE,$userId);
	}

	
	public function getTasks($userId=null)
	{
		return $this->getAuthItems(CAuthItem::TYPE_TASK,$userId);
	}

	
	public function getOperations($userId=null)
	{
		return $this->getAuthItems(CAuthItem::TYPE_OPERATION,$userId);
	}

	
	public function executeBizRule($bizRule,$params,$data)
	{
		if($bizRule==='' || $bizRule===null)
			return true;
		if ($this->showErrors)
			return eval($bizRule)!=0;
		else
		{
			try
			{
				return @eval($bizRule)!=0;
			}
			catch (ParseError $e)
			{
				return false;
			}
		}
	}

	/**
	 * Checks the item types to make sure a child can be added to a parent.
	 * @param integer $parentType parent item type
	 * @param integer $childType child item type
	 * @throws CException if the item cannot be added as a child due to its incompatible type.
	 */
	protected function checkItemChildType($parentType,$childType)
	{
		static $types=array('operation','task','role');
		if($parentType < $childType)
			throw new CException(Yii::t('yii','Cannot add an item of type "{child}" to an item of type "{parent}".',
				array('{child}'=>$types[$childType], '{parent}'=>$types[$parentType])));
	}
}
