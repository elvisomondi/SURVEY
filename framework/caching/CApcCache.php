<?php

class CApcCache extends CCache
{
	
	public $useApcu=false;


	
	public function init()
	{
		parent::init();
		$extension=$this->useApcu ? 'apcu' : 'apc';
		if(!extension_loaded($extension))
			throw new CException(Yii::t('yii',"CApcCache requires PHP {extension} extension to be loaded.",
				array('{extension}'=>$extension)));
	}


	protected function getValue($key)
	{
		return $this->useApcu ? apcu_fetch($key) : apc_fetch($key);
	}

	
	protected function getValues($keys)
	{
		return $this->useApcu ? apcu_fetch($keys) : apc_fetch($keys);
	}

	
	protected function setValue($key,$value,$expire)
	{
		return $this->useApcu ? apcu_store($key,$value,$expire) : apc_store($key,$value,$expire);
	}

	
	protected function addValue($key,$value,$expire)
	{
		return $this->useApcu ? apcu_add($key,$value,$expire) : apc_add($key,$value,$expire);
	}

	
	protected function deleteValue($key)
	{
		return $this->useApcu ? apcu_delete($key) : apc_delete($key);
	}

	
	protected function flushValues()
	{
		return $this->useApcu ? apcu_clear_cache() : apc_clear_cache('user');
	}
}
