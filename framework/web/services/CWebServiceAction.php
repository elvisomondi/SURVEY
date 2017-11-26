<?php

class CWebServiceAction extends CAction
{
	
	public $provider;
	
	public $serviceUrl;
	/**
	 * @var string the URL for WSDL. Defaults to null, meaning
	 * the URL for this action is used to serve WSDL document.
	 */
	public $wsdlUrl;
	
	public $serviceVar='ws';
	
	public $classMap;
	
	public $serviceOptions=array();

	private $_service;


	/**
	 * Runs the action.
	 * If the GET parameter {@link serviceVar} exists, the action handle the remote method invocation.
	 * If not, the action will serve WSDL content;
	 */
	public function run()
	{
		$hostInfo=Yii::app()->getRequest()->getHostInfo();
		$controller=$this->getController();
		if(($serviceUrl=$this->serviceUrl)===null)
			$serviceUrl=$hostInfo.$controller->createUrl($this->getId(),array($this->serviceVar=>1));
		if(($wsdlUrl=$this->wsdlUrl)===null)
			$wsdlUrl=$hostInfo.$controller->createUrl($this->getId());
		if(($provider=$this->provider)===null)
			$provider=$controller;

		$this->_service=$this->createWebService($provider,$wsdlUrl,$serviceUrl);

		if(is_array($this->classMap))
			$this->_service->classMap=$this->classMap;

		foreach($this->serviceOptions as $name=>$value)
			$this->_service->$name=$value;

		if(isset($_GET[$this->serviceVar]))
			$this->_service->run();
		else
			$this->_service->renderWsdl();

		Yii::app()->end();
	}

	/**
	 * Returns the Web service instance currently being used.
	 * @return CWebService the Web service instance
	 */
	public function getService()
	{
		return $this->_service;
	}

	/**
	 * Creates a {@link CWebService} instance.
	 * You may override this method to customize the created instance.
	 * @param mixed $provider the web service provider class name or object
	 * @param string $wsdlUrl the URL for WSDL.
	 * @param string $serviceUrl the URL for the Web service.
	 * @return CWebService the Web service instance
	 */
	protected function createWebService($provider,$wsdlUrl,$serviceUrl)
	{
		return new CWebService($provider,$wsdlUrl,$serviceUrl);
	}
}