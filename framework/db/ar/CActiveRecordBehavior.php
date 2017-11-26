<?php

class CActiveRecordBehavior extends CModelBehavior
{
	/**
	 * Declares events and the corresponding event handler methods.
	 */
	public function events()
	{
		return array_merge(parent::events(), array(
			'onBeforeSave'=>'beforeSave',
			'onAfterSave'=>'afterSave',
			'onBeforeDelete'=>'beforeDelete',
			'onAfterDelete'=>'afterDelete',
			'onBeforeFind'=>'beforeFind',
			'onAfterFind'=>'afterFind',
			'onBeforeCount'=>'beforeCount',
		));
	}

	/**
	 * Responds to {@link CActiveRecord::onBeforeSave} event.
	 */
	protected function beforeSave($event)
	{
	}

	/**
	 * Responds to {@link CActiveRecord::onAfterSave} event.
	 */
	protected function afterSave($event)
	{
	}

	protected function beforeDelete($event)
	{
	}

	
	protected function afterDelete($event)
	{
	}

	
	protected function beforeFind($event)
	{
	}

	/**
	 * Responds to {@link CActiveRecord::onAfterFind} event.
	 * Override this method and make it public if you want to handle the corresponding event
	 * of the {@link CBehavior::owner owner}.
	 * @param CEvent $event event parameter
	 */
	protected function afterFind($event)
	{
	}

	
	protected function beforeCount($event)
	{
	}
}
