<?php

class CWebService extends CComponent
{
	const SOAP_ERROR=1001;
	/**
	 * @var string|object the web service provider class or object.
	 * If specified as a class name, it can be a path alias.
	 */
	public $provider;
	/**
	 * @var string the URL for WSDL. This is required by {@link run()}.
	 */
	public $wsdlUrl;
	/**
	 * @var string the URL for the Web service. This is required by {@link generateWsdl()} and {@link renderWsdl()}.
	 */
	public $serviceUrl;
	/**
	 * @var integer number of seconds that the generated WSDL can remain valid in cache. Defaults to 0, meaning no caching.
	 */
	public $wsdlCacheDuration=0;
	
	public $cacheID='cache';
	/**
	 * @var string encoding of the Web service. Defaults to 'UTF-8'.
	 */
	public $encoding='UTF-8';
	
	public $classMap=array();
	/**
	 * @var string actor of the SOAP service. Defaults to null, meaning not set.
	 */
	public $actor;
	
	public $soapVersion;
	
	public $persistence;
	
	public $generatorConfig='CWsdlGenerator';

	private $_method;



	public function __construct($provider,$wsdlUrl,$serviceUrl)
	{
		$this->provider=$provider;
		$this->wsdlUrl=$wsdlUrl;
		$this->serviceUrl=$serviceUrl;
	}

	/**
	 * The PHP error handler.
	 * @param CErrorEvent $event the PHP error event
	 */
	public function handleError($event)
	{
		$event->handled=true;
		$message=$event->message;
		if(YII_DEBUG)
		{
			$trace=debug_backtrace();
			if(isset($trace[2]) && isset($trace[2]['file']) && isset($trace[2]['line']))
				$message.=' ('.$trace[2]['file'].':'.$trace[2]['line'].')';
		}
		throw new CException($message,self::SOAP_ERROR);
	}

	/**
	 * Generates and displays the WSDL as defined by the provider.
	 * @see generateWsdl
	 */
	public function renderWsdl()
	{
		$wsdl=$this->generateWsdl();
		header('Content-Type: text/xml;charset='.$this->encoding);
		header('Content-Length: '.(function_exists('mb_strlen') ? mb_strlen($wsdl,'8bit') : strlen($wsdl)));
		echo $wsdl;
	}

	/**
	 * Generates the WSDL as defined by the provider.
	 * The cached version may be used if the WSDL is found valid in cache.
	 * @return string the generated WSDL
	 * @see wsdlCacheDuration
	 */
	public function generateWsdl()
	{
		$providerClass=is_object($this->provider) ? get_class($this->provider) : Yii::import($this->provider,true);
		if($this->wsdlCacheDuration>0 && $this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null)
		{
			$key='Yii.CWebService.'.$providerClass.$this->serviceUrl.$this->encoding;
			if(($wsdl=$cache->get($key))!==false)
				return $wsdl;
		}
		$generator=Yii::createComponent($this->generatorConfig);
		$wsdl=$generator->generateWsdl($providerClass,$this->serviceUrl,$this->encoding);
		if(isset($key))
			$cache->set($key,$wsdl,$this->wsdlCacheDuration);
		return $wsdl;
	}

	/**
	 * Handles the web service request.
	 */
	public function run()
	{
		header('Content-Type: text/xml;charset='.$this->encoding);
		if(YII_DEBUG)
			ini_set("soap.wsdl_cache_enabled",0);
		$server=new SoapServer($this->wsdlUrl,$this->getOptions());
		Yii::app()->attachEventHandler('onError',array($this,'handleError'));
		try
		{
			if($this->persistence!==null)
				$server->setPersistence($this->persistence);
			if(is_string($this->provider))
				$provider=Yii::createComponent($this->provider);
			else
				$provider=$this->provider;

			if(method_exists($server,'setObject'))
			{
				if (is_array($this->generatorConfig) && isset($this->generatorConfig['bindingStyle'])
					&& $this->generatorConfig['bindingStyle']==='document')
				{
					$server->setObject(new CDocumentSoapObjectWrapper($provider));
				}
				else
				{
					$server->setObject($provider);
				}
			}
			else
			{
				if (is_array($this->generatorConfig) && isset($this->generatorConfig['bindingStyle'])
					&& $this->generatorConfig['bindingStyle']==='document')
				{
					$server->setClass('CDocumentSoapObjectWrapper',$provider);
				}
				else
				{
					$server->setClass('CSoapObjectWrapper',$provider);
				}
			}

			if($provider instanceof IWebServiceProvider)
			{
				if($provider->beforeWebMethod($this))
				{
					$server->handle();
					$provider->afterWebMethod($this);
				}
			}
			else
				$server->handle();
		}
		catch(Exception $e)
		{
			if($e->getCode()!==self::SOAP_ERROR) // non-PHP error
			{
				// only log for non-PHP-error case because application's error handler already logs it
				// php <5.2 doesn't support string conversion auto-magically
				Yii::log($e->__toString(),CLogger::LEVEL_ERROR,'application');
			}
			$message=$e->getMessage();
			if(YII_DEBUG)
				$message.=' ('.$e->getFile().':'.$e->getLine().")\n".$e->getTraceAsString();

			// We need to end application explicitly because of
			// http://bugs.php.net/bug.php?id=49513
			Yii::app()->onEndRequest(new CEvent($this));
			$server->fault(get_class($e),$message);
			exit(1);
		}
	}

	/**
	 * @return string the currently requested method name. Empty if no method is being requested.
	 */
	public function getMethodName()
	{
		if($this->_method===null)
		{
			// before PHP 5.6 php://input could be read only once
			// since PHP 5.6 $HTTP_RAW_POST_DATA is deprecated
			if(version_compare(PHP_VERSION, '5.6.0', '<') && isset($HTTP_RAW_POST_DATA))
				$request=$HTTP_RAW_POST_DATA;
			else
				$request=file_get_contents('php://input');
			if(preg_match('/<.*?:Body[^>]*>\s*<.*?:(\w+)/mi',$request,$matches))
				$this->_method=$matches[1];
			else
				$this->_method='';
		}
		return $this->_method;
	}

	/**
	 * @return array options for creating SoapServer instance
	 * @see http://www.php.net/manual/en/soapserver.soapserver.php
	 */
	protected function getOptions()
	{
		$options=array();
		if($this->soapVersion==='1.1')
			$options['soap_version']=SOAP_1_1;
		elseif($this->soapVersion==='1.2')
			$options['soap_version']=SOAP_1_2;
		if($this->actor!==null)
			$options['actor']=$this->actor;
		$options['encoding']=$this->encoding;
		foreach($this->classMap as $type=>$className)
		{
			$className=Yii::import($className,true);
			if(is_int($type))
				$type=$className;
			$options['classmap'][$type]=$className;
		}
		return $options;
	}
}


/**
 * CSoapObjectWrapper is a wrapper class internally used when SoapServer::setObject() is not defined.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.services
 */
class CSoapObjectWrapper
{
	/**
	 * @var object the service provider
	 */
	public $object=null;

	/**
	 * Constructor.
	 * @param object $object the service provider
	 */
	public function __construct($object)
	{
		$this->object=$object;
	}

	/**
	 * PHP __call magic method.
	 * This method calls the service provider to execute the actual logic.
	 * @param string $name method name
	 * @param array $arguments method arguments
	 * @return mixed method return value
	 */
	public function __call($name,$arguments)
	{
		return call_user_func_array(array($this->object,$name),$arguments);
	}
}

/**
 * CDocumentSoapObjectWrapper is a wrapper class internally used
 * when generatorConfig contains bindingStyle key set to document value.
 *
 * @author Jan Was <jwas@nets.com.pl>
 * @package system.web.services
 */
class CDocumentSoapObjectWrapper
{
	/**
	 * @var object the service provider
	 */
	public $object=null;

	/**
	 * Constructor.
	 * @param object $object the service provider
	 */
	public function __construct($object)
	{
		$this->object=$object;
	}

	/**
	 * PHP __call magic method.
	 * This method calls the service provider to execute the actual logic.
	 * @param string $name method name
	 * @param array $arguments method arguments
	 * @return mixed method return value
	 */
	public function __call($name,$arguments)
	{
		if (is_array($arguments) && isset($arguments[0]))
		{
			$result = call_user_func_array(array($this->object, $name), (array)$arguments[0]);
		}
		else
		{
			$result = call_user_func_array(array($this->object, $name), $arguments);
		}
		return $result === null ? $result : array($name . 'Result' => $result); 
	}
}

