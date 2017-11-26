<?php

/**
 * CLogRoute is the base class for all log route classes.
 *
 * A log route object retrieves log messages from a logger and sends it
 * somewhere
 */
abstract class CLogRoute extends CComponent
{
	/**
	 * @var boolean whether to enable this log route. 
	 */
	public $enabled=true;
	
	public $levels='';
	
	public $categories=array();
	
	public $except=array();
	
	public $filter;
	
	public $logs=array();


	
	public function init()
	{
	}

	
	protected function formatLogMessage($message,$level,$category,$time)
	{
		return @date('Y/m/d H:i:s',$time)." [$level] [$category] $message\n";
	}

	
	public function collectLogs($logger, $processLogs=false)
	{
		$logs=$logger->getLogs($this->levels,$this->categories,$this->except);
		$this->logs=empty($this->logs) ? $logs : array_merge($this->logs,$logs);
		if($processLogs && !empty($this->logs))
		{
			if($this->filter!==null)
				Yii::createComponent($this->filter)->filter($this->logs);
			if($this->logs!==array())
				$this->processLogs($this->logs);
			$this->logs=array();
		}
	}


	abstract protected function processLogs($logs);
}
