<?php

/**
 * CLogRouter manages log routes that record log messages in different media.
 */
class CLogRouter extends CApplicationComponent
{
	private $_routes=array();

	/**
	 * Initializes this application component.
	 * This method is required by the IApplicationComponent interface.
	 */
	public function init()
	{
		parent::init();
		foreach($this->_routes as $name=>$route)
		{
			$route=Yii::createComponent($route);
			$route->init();
			$this->_routes[$name]=$route;
		}
		Yii::getLogger()->attachEventHandler('onFlush',array($this,'collectLogs'));
		Yii::app()->attachEventHandler('onEndRequest',array($this,'processLogs'));
	}

	/**
	 * @return array the currently initialized routes
	 */
	public function getRoutes()
	{
		return new CMap($this->_routes);
	}

	
	public function setRoutes($config)
	{
		foreach($config as $name=>$route)
			$this->_routes[$name]=$route;
	}

	
	public function collectLogs($event)
	{
		$logger=Yii::getLogger();
		$dumpLogs=isset($event->params['dumpLogs']) && $event->params['dumpLogs'];
		foreach($this->_routes as $route)
		{
			/* @var $route CLogRoute */
			if($route->enabled)
				$route->collectLogs($logger,$dumpLogs);
		}
	}

	
	public function processLogs()
	{
		$logger=Yii::getLogger();
		$logger->flush(true);
	}
}
