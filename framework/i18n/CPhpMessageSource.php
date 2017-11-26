<?php
/**
 * CPhpMessageSource represents a message source that stores translated messages in PHP scripts.
 */
class CPhpMessageSource extends CMessageSource
{
	const CACHE_KEY_PREFIX='Yii.CPhpMessageSource.';

	
	public $cachingDuration=0;
	
	public $cacheID='cache';
	/**
	 * @var string the base path for all translated messages. Defaults to null, meaning
	 * the "messages" subdirectory of the application directory (e.g. "protected/messages").
	 */
	public $basePath;
	
	public $extensionPaths=array();

	private $_files=array();

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by preprocessing
	 * the user request data.
	 */
	public function init()
	{
		parent::init();
		if($this->basePath===null)
			$this->basePath=Yii::getPathOfAlias('application.messages');
	}

	
	protected function getMessageFile($category,$language)
	{
		if(!isset($this->_files[$category][$language]))
		{
			if(($pos=strpos($category,'.'))!==false)
			{
				$extensionClass=substr($category,0,$pos);
				$extensionCategory=substr($category,$pos+1);
				// First check if there's an extension registered for this class.
				if(isset($this->extensionPaths[$extensionClass]))
					$this->_files[$category][$language]=Yii::getPathOfAlias($this->extensionPaths[$extensionClass]).DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$extensionCategory.'.php';
				else
				{
					// No extension registered, need to find it.
					$class=new ReflectionClass($extensionClass);
					$this->_files[$category][$language]=dirname($class->getFileName()).DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$extensionCategory.'.php';
				}
			}
			else
				$this->_files[$category][$language]=$this->basePath.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$category.'.php';
		}
		return $this->_files[$category][$language];
	}

	
	protected function loadMessages($category,$language)
	{
		$messageFile=$this->getMessageFile($category,$language);

		if($this->cachingDuration>0 && $this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null)
		{
			$key=self::CACHE_KEY_PREFIX . $messageFile;
			if(($data=$cache->get($key))!==false)
				return unserialize($data);
		}

		if(is_file($messageFile))
		{
			$messages=include($messageFile);
			if(!is_array($messages))
				$messages=array();
			if(isset($cache))
			{
				$dependency=new CFileCacheDependency($messageFile);
				$cache->set($key,serialize($messages),$this->cachingDuration,$dependency);
			}
			return $messages;
		}
		else
			return array();
	}
}