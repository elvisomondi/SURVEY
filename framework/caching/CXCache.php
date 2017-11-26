<?php

class CXCache extends CCache
{

	public function init()
	{
		parent::init();
		if(!function_exists('xcache_isset'))
			throw new CException(Yii::t('yii','CXCache requires PHP XCache extension to be loaded.'));
	}

	
	protected function getValue($key)
	{
		return xcache_isset($key) ? xcache_get($key) : false;
	}

	
	protected function setValue($key,$value,$expire)
	{
		return xcache_set($key,$value,$expire);
	}

	
	protected function addValue($key,$value,$expire)
	{
		return !xcache_isset($key) ? $this->setValue($key,$value,$expire) : false;
	}

	
	protected function deleteValue($key)
	{
		return xcache_unset($key);
	}

	protected function flushValues()
	{
		for($i=0, $max=xcache_count(XC_TYPE_VAR); $i<$max; $i++)
		{
			if(xcache_clear_cache(XC_TYPE_VAR, $i)===false)
				return false;
		}
		return true;
	}
}

