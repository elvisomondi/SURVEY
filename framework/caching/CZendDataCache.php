<?php

class CZendDataCache extends CCache
{
	
	public function init()
	{
		parent::init();
		if(!function_exists('zend_shm_cache_store'))
			throw new CException(Yii::t('yii','CZendDataCache requires PHP Zend Data Cache extension to be loaded.'));
	}

	
	protected function getValue($key)
	{
		$result = zend_shm_cache_fetch($key);
		return $result !== NULL ? $result : false;
	}

	protected function setValue($key,$value,$expire)
	{
		return zend_shm_cache_store($key,$value,$expire);
	}

	protected function addValue($key,$value,$expire)
	{
		return (NULL === zend_shm_cache_fetch($key)) ? $this->setValue($key,$value,$expire) : false;
	}

	protected function deleteValue($key)
	{
		return zend_shm_cache_delete($key);
	}

	
	protected function flushValues()
	{
		return zend_shm_cache_clear();
	}
}
