<?php
/**
 * CFileLogRoute records log messages in files.
 */
class CFileLogRoute extends CLogRoute
{
	
	private $_maxFileSize=1024; // in KB
	
	private $_maxLogFiles=5;

	private $_logPath;
	
	private $_logFile='application.log';
	
	public $rotateByCopy=false;

	
	public function init()
	{
		parent::init();
		if($this->getLogPath()===null)
			$this->setLogPath(Yii::app()->getRuntimePath());
	}

	
	public function getLogPath()
	{
		return $this->_logPath;
	}

	/**
	 * @param string $value directory for storing log files.
	 */
	public function setLogPath($value)
	{
		$this->_logPath=realpath($value);
		if($this->_logPath===false || !is_dir($this->_logPath) || !is_writable($this->_logPath))
			throw new CException(Yii::t('yii','CFileLogRoute.logPath "{path}" does not point to a valid directory. Make sure the directory exists and is writable by the Web server process.',
				array('{path}'=>$value)));
	}

	/**
	 * @return string log file name. Defaults to 'application.log'.
	 */
	public function getLogFile()
	{
		return $this->_logFile;
	}

	/**
	 * @param string $value log file name
	 */
	public function setLogFile($value)
	{
		$this->_logFile=$value;
	}

	/**
	 * @return integer maximum log file size in kilo-bytes (KB). Defaults to 1024 (1MB).
	 */
	public function getMaxFileSize()
	{
		return $this->_maxFileSize;
	}

	/**
	 * @param integer $value maximum log file size in kilo-bytes (KB).
	 */
	public function setMaxFileSize($value)
	{
		if(($this->_maxFileSize=(int)$value)<1)
			$this->_maxFileSize=1;
	}

	/**
	 * @return integer number of files used for rotation. Defaults to 5.
	 */
	public function getMaxLogFiles()
	{
		return $this->_maxLogFiles;
	}

	/**
	 * @param integer $value number of files used for rotation.
	 */
	public function setMaxLogFiles($value)
	{
		if(($this->_maxLogFiles=(int)$value)<1)
			$this->_maxLogFiles=1;
	}

	/**
	 * Saves log messages in files.
	 * @param array $logs list of log messages
	 */
	protected function processLogs($logs)
	{
		$text='';
		foreach($logs as $log)
			$text.=$this->formatLogMessage($log[0],$log[1],$log[2],$log[3]);

		$logFile=$this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();
		$fp=@fopen($logFile,'a');
		@flock($fp,LOCK_EX);
		if(@filesize($logFile)>$this->getMaxFileSize()*1024)
		{
			$this->rotateFiles();
			@flock($fp,LOCK_UN);
			@fclose($fp);
			@file_put_contents($logFile,$text,FILE_APPEND|LOCK_EX);
		}
		else
		{
			@fwrite($fp,$text);
			@flock($fp,LOCK_UN);
			@fclose($fp);
		}
	}

	/**
	 * Rotates log files.
	 */
	protected function rotateFiles()
	{
		$file=$this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();
		$max=$this->getMaxLogFiles();
		for($i=$max;$i>0;--$i)
		{
			$rotateFile=$file.'.'.$i;
			if(is_file($rotateFile))
			{
				// suppress errors because it's possible multiple processes enter into this section
				if($i===$max)
					@unlink($rotateFile);
				else
					@rename($rotateFile,$file.'.'.($i+1));
			}
		}
		if(is_file($file))
		{
			// suppress errors because it's possible multiple processes enter into this section
			if($this->rotateByCopy)
			{
				@copy($file,$file.'.1');
				if($fp=@fopen($file,'a'))
				{
					@ftruncate($fp,0);
					@fclose($fp);
				}
			}
			else
				@rename($file,$file.'.1');
		}
		// clear stat cache after moving files so later file size check is not cached
		clearstatcache();
	}
}
