<?php

class CEAcceleratorCache extends CCache
{
	
	public function init()
	{
		parent::init();
		if(!function_exists('eaccelerator_get'))
			throw new CException(Yii::t('yii','CEAcceleratorCache requires PHP eAccelerator extension to be loaded, enabled or compiled with the "--with-eaccelerator-shared-memory" option.'));
	}

	
	protected function getValue($key)
	{
		$result = eaccelerator_get($key);
		return $result !== NULL ? $result : false;
	}

	
	protected function setValue($key,$value,$expire)
	{
		return eaccelerator_put($key,$value,$expire);
	}

	protected function addValue($key,$value,$expire)
	{
		return (NULL === eaccelerator_get($key)) ? $this->setValue($key,$value,$expire) : false;
	}

	
	protected function deleteValue($key)
	{
		return eaccelerator_rm($key);
	}

	protected function flushValues()
	{
		// first, remove expired content from cache
		eaccelerator_gc();
		// now, remove leftover cache-keys
		$keys = eaccelerator_list_keys();
		foreach($keys as $key)
			$this->deleteValue(substr($key['name'], 1));
		return true;
	}
}
