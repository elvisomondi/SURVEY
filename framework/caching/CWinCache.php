<?php
/**
 * CWinCache implements a cache application component based on {@link http://www.iis.net/expand/wincacheforphp WinCache}.
 */
class CWinCache extends CCache {
	/**
	 * Initializes this application component.
	 */
	public function init()
	{
		parent::init();
		if(!extension_loaded('wincache'))
			throw new CException(Yii::t('yii', 'CWinCache requires PHP wincache extension to be loaded.'));
		if(!ini_get('wincache.ucenabled'))
			throw new CException(Yii::t('yii', 'CWinCache user cache is disabled. Please set wincache.ucenabled to On in your php.ini.'));
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 */
	protected function getValue($key)
	{
		return wincache_ucache_get($key);
	}

	
	protected function getValues($keys)
	{
		return wincache_ucache_get($keys);
	}

	protected function setValue($key,$value,$expire)
	{
		return wincache_ucache_set($key,$value,$expire);
	}

	
	protected function addValue($key,$value,$expire)
	{
		return wincache_ucache_add($key,$value,$expire);
	}

	
	protected function deleteValue($key)
	{
		return wincache_ucache_delete($key);
	}

	
	protected function flushValues()
	{
		return wincache_ucache_clear();
	}
}