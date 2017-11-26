<?php
class CModelBehavior extends CBehavior
{
	
	public function events()
	{
		return array(
			'onAfterConstruct'=>'afterConstruct',
			'onBeforeValidate'=>'beforeValidate',
			'onAfterValidate'=>'afterValidate',
		);
	}

	protected function afterConstruct($event)
	{
	}

	protected function beforeValidate($event)
	{
	}

	protected function afterValidate($event)
	{
	}
}
