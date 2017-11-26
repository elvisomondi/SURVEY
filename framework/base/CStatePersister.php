<?php
/**
 * CStatePersister implements a file-based persistent data storage.
 *
 * It can be used to keep data available through multiple requests and sessions.
 */
class CStatePersister extends CApplicationComponent implements IStatePersister
{
	
	public $stateFile;
	
	public $cacheID='cache';

	/**
	 * Initializes the component.
	 */
	public function init()
	{
		parent::init();
		if($this->stateFile===null)
			$this->stateFile=Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'state.bin';
		$dir=dirname($this->stateFile);
		if(!is_dir($dir) || !is_writable($dir))
			throw new CException(Yii::t('yii','Unable to create application state file "{file}". Make sure the directory containing the file exists and is writable by the Web server process.',
				array('{file}'=>$this->stateFile)));
	}

	
	public function load()
	{
		$stateFile=$this->stateFile;
		if($this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null)
		{
			$cacheKey='Yii.CStatePersister.'.$stateFile;
			if(($value=$cache->get($cacheKey))!==false)
				return unserialize($value);
			else
			{
				if(($content=$this->getContent($stateFile))!==false)
				{
					$unserialized_content=unserialize($content);
					// If it can't be unserialized, don't cache it:
					if ($unserialized_content!==false || $content=="") 
						$cache->set($cacheKey,$content,0,new CFileCacheDependency($stateFile));
					return $unserialized_content;
				}
				else
					return null;
			}
		}
		elseif(($content=$this->getContent($stateFile))!==false)
			return unserialize($content);
		else
			return null;
	}
	
	
	protected function getContent($filename)
	{
		$file=@fopen($filename,"r");
		if($file && flock($file,LOCK_SH))
		{
			$contents=@file_get_contents($filename);
			flock($file,LOCK_UN);
			fclose($file);
			return $contents;
		}
		return false;
	}
	

	public function save($state)
	{
		file_put_contents($this->stateFile,serialize($state),LOCK_EX);
	}
}
