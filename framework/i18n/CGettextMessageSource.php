<?php
/**
 * CGettextMessageSource represents a message source that is based on GNU Gettext.
 */
class CGettextMessageSource extends CMessageSource
{
	const CACHE_KEY_PREFIX='Yii.CGettextMessageSource.';
	const MO_FILE_EXT='.mo';
	const PO_FILE_EXT='.po';

	/**
	 * @var integer the time in seconds that the messages can remain valid in cache.
	 */
	public $cachingDuration=0;
	/**
	 * @var string the ID of the cache application component that is used to cache the messages.
	 */
	public $cacheID='cache';
	/**
	 * @var string the base path for all translated messages. Defaults to null, meaning
	 */
	public $basePath;
	/**
	 * @var boolean whether to load messages from MO files. Defaults to true.
	 * If false, messages will be loaded from PO files.
	 */
	public $useMoFile=true;
	/**
	 * @var boolean whether to use Big Endian to read and write MO files.
	 * Defaults to false. This property is only used when {@link useMoFile} is true.
	 */
	public $useBigEndian=false;
	/**
	 * @var string the message catalog name. This is the name of the message file (without extension)
	 * that stores the translated messages. Defaults to 'messages'.
	 */
	public $catalog='messages';

	/**
	 * Initializes the application component.
	 */
	public function init()
	{
		parent::init();
		if($this->basePath===null)
			$this->basePath=Yii::getPathOfAlias('application.messages');
	}

	/**
	 * Loads the message translation for the specified language and category.
	 */
	protected function loadMessages($category, $language)
	{
        $messageFile=$this->basePath . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $this->catalog;
        if($this->useMoFile)
        	$messageFile.=self::MO_FILE_EXT;
        else
        	$messageFile.=self::PO_FILE_EXT;

		if ($this->cachingDuration > 0 && $this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null)
		{
			$key = self::CACHE_KEY_PREFIX . $messageFile . "." . $category;
			if (($data=$cache->get($key)) !== false)
				return unserialize($data);
		}

		if (is_file($messageFile))
		{
			if($this->useMoFile)
				$file=new CGettextMoFile($this->useBigEndian);
			else
				$file=new CGettextPoFile();
			$messages=$file->load($messageFile,$category);
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
