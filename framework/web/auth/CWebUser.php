<?php

class CWebUser extends CApplicationComponent implements IWebUser
{
	const FLASH_KEY_PREFIX='Yii.CWebUser.flash.';
	const FLASH_COUNTERS='Yii.CWebUser.flashcounters';
	const STATES_VAR='__states';
	const AUTH_TIMEOUT_VAR='__timeout';
	const AUTH_ABSOLUTE_TIMEOUT_VAR='__absolute_timeout';

	/**
	 * @var boolean whether to enable cookie-based login. Defaults to false.
	 */
	public $allowAutoLogin=false;
	/**
	 * @var string the name for a guest user. Defaults to 'Guest'.
	 * This is used by {@link getName} when the current user is a guest (not authenticated).
	 */
	public $guestName='Guest';
	
	public $loginUrl=array('/site/login');
	
	public $identityCookie;
	
	public $authTimeout;
	
	public $absoluteAuthTimeout;

	public $autoRenewCookie=false;
	
	public $autoUpdateFlash=true;

	public $loginRequiredAjaxResponse;

	private $_keyPrefix;
	private $_access=array();

	/**
	 * PHP magic method.
	 * This method is overriden so that persistent states can be accessed like properties.
	 * @param string $name property name
	 * @return mixed property value
	 */
	public function __get($name)
	{
		if($this->hasState($name))
			return $this->getState($name);
		else
			return parent::__get($name);
	}

	/**
	 * PHP magic method.
	 * This method is overriden so that persistent states can be set like properties.
	 * @param string $name property name
	 * @param mixed $value property value
	 */
	public function __set($name,$value)
	{
		if($this->hasState($name))
			$this->setState($name,$value);
		else
			parent::__set($name,$value);
	}

	/**
	 * PHP magic method.
	 * This method is overriden so that persistent states can also be checked for null value.
	 * @param string $name property name
	 * @return boolean
	 */
	public function __isset($name)
	{
		if($this->hasState($name))
			return $this->getState($name)!==null;
		else
			return parent::__isset($name);
	}

	/**
	 * PHP magic method.
	 * This method is overriden so that persistent states can also be unset.
	 * @param string $name property name
	 * @throws CException if the property is read only.
	 */
	public function __unset($name)
	{
		if($this->hasState($name))
			$this->setState($name,null);
		else
			parent::__unset($name);
	}

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by starting session,
	 * performing cookie-based authentication if enabled, and updating the flash variables.
	 */
	public function init()
	{
		parent::init();
		Yii::app()->getSession()->open();
		if($this->getIsGuest() && $this->allowAutoLogin)
			$this->restoreFromCookie();
		elseif($this->autoRenewCookie && $this->allowAutoLogin)
			$this->renewCookie();
		if($this->autoUpdateFlash)
			$this->updateFlash();

		$this->updateAuthStatus();
	}

	
	public function login($identity,$duration=0)
	{
		$id=$identity->getId();
		$states=$identity->getPersistentStates();
		if($this->beforeLogin($id,$states,false))
		{
			$this->changeIdentity($id,$identity->getName(),$states);

			if($duration>0)
			{
				if($this->allowAutoLogin)
					$this->saveToCookie($duration);
				else
					throw new CException(Yii::t('yii','{class}.allowAutoLogin must be set true in order to use cookie-based authentication.',
						array('{class}'=>get_class($this))));
			}

			if ($this->absoluteAuthTimeout)
				$this->setState(self::AUTH_ABSOLUTE_TIMEOUT_VAR, time()+$this->absoluteAuthTimeout);
			$this->afterLogin(false);
		}
		return !$this->getIsGuest();
	}

	
	public function logout($destroySession=true)
	{
		if($this->beforeLogout())
		{
			if($this->allowAutoLogin)
			{
				Yii::app()->getRequest()->getCookies()->remove($this->getStateKeyPrefix());
				if($this->identityCookie!==null)
				{
					$cookie=$this->createIdentityCookie($this->getStateKeyPrefix());
					$cookie->value=null;
					$cookie->expire=0;
					Yii::app()->getRequest()->getCookies()->add($cookie->name,$cookie);
				}
			}
			if($destroySession)
				Yii::app()->getSession()->destroy();
			else
				$this->clearStates();
			$this->_access=array();
			$this->afterLogout();
		}
	}

	/**
	 * Returns a value indicating whether the user is a guest (not authenticated).
	 * @return boolean whether the current application user is a guest.
	 */
	public function getIsGuest()
	{
		return $this->getState('__id')===null;
	}

	/**
	 * Returns a value that uniquely represents the user.
	 * @return mixed the unique identifier for the user. If null, it means the user is a guest.
	 */
	public function getId()
	{
		return $this->getState('__id');
	}

	/**
	 * @param mixed $value the unique identifier for the user. If null, it means the user is a guest.
	 */
	public function setId($value)
	{
		$this->setState('__id',$value);
	}

	/**
	 * Returns the unique identifier for the user (e.g. username).
	 * This is the unique identifier that is mainly used for display purpose.
	 * @return string the user name. If the user is not logged in, this will be {@link guestName}.
	 */
	public function getName()
	{
		if(($name=$this->getState('__name'))!==null)
			return $name;
		else
			return $this->guestName;
	}

	/**
	 * Sets the unique identifier for the user (e.g. username).
	 * @param string $value the user name.
	 * @see getName
	 */
	public function setName($value)
	{
		$this->setState('__name',$value);
	}

	
	public function getReturnUrl($defaultUrl=null)
	{
		if($defaultUrl===null)
		{
			$defaultReturnUrl=Yii::app()->getUrlManager()->showScriptName ? Yii::app()->getRequest()->getScriptUrl() : Yii::app()->getRequest()->getBaseUrl().'/';
		}
		else
		{
			$defaultReturnUrl=CHtml::normalizeUrl($defaultUrl);
		}
		return $this->getState('__returnUrl',$defaultReturnUrl);
	}

	/**
	 * @param string $value the URL that the user should be redirected to after login.
	 */
	public function setReturnUrl($value)
	{
		$this->setState('__returnUrl',$value);
	}

	
	public function loginRequired()
	{
		$app=Yii::app();
		$request=$app->getRequest();

		if(!$request->getIsAjaxRequest())
		{
			$this->setReturnUrl($request->getUrl());
			if(($url=$this->loginUrl)!==null)
			{
				if(is_array($url))
				{
					$route=isset($url[0]) ? $url[0] : $app->defaultController;
					$url=$app->createUrl($route,array_splice($url,1));
				}
				$request->redirect($url);
			}
		}
		elseif(isset($this->loginRequiredAjaxResponse))
		{
			echo $this->loginRequiredAjaxResponse;
			Yii::app()->end();
		}

		throw new CHttpException(403,Yii::t('yii','Login Required'));
	}


	protected function beforeLogin($id,$states,$fromCookie)
	{
		return true;
	}

	protected function afterLogin($fromCookie)
	{
	}


	protected function beforeLogout()
	{
		return true;
	}

	
	protected function afterLogout()
	{
	}

	
	protected function restoreFromCookie()
	{
		$app=Yii::app();
		$request=$app->getRequest();
		$cookie=$request->getCookies()->itemAt($this->getStateKeyPrefix());
		if($cookie && !empty($cookie->value) && is_string($cookie->value) && ($data=$app->getSecurityManager()->validateData($cookie->value))!==false)
		{
			$data=@unserialize($data);
			if(is_array($data) && isset($data[0],$data[1],$data[2],$data[3]))
			{
				list($id,$name,$duration,$states)=$data;
				if($this->beforeLogin($id,$states,true))
				{
					$this->changeIdentity($id,$name,$states);
					if($this->autoRenewCookie)
					{
						$this->saveToCookie($duration);
					}
					$this->afterLogin(true);
				}
			}
		}
	}


	protected function renewCookie()
	{
		$request=Yii::app()->getRequest();
		$cookies=$request->getCookies();
		$cookie=$cookies->itemAt($this->getStateKeyPrefix());
		if($cookie && !empty($cookie->value) && ($data=Yii::app()->getSecurityManager()->validateData($cookie->value))!==false)
		{
			$data=@unserialize($data);
			if(is_array($data) && isset($data[0],$data[1],$data[2],$data[3]))
			{
				$this->saveToCookie($data[2]);
			}
		}
	}

	protected function saveToCookie($duration)
	{
		$app=Yii::app();
		$cookie=$this->createIdentityCookie($this->getStateKeyPrefix());
		$cookie->expire=time()+$duration;
		$data=array(
			$this->getId(),
			$this->getName(),
			$duration,
			$this->saveIdentityStates(),
		);
		$cookie->value=$app->getSecurityManager()->hashData(serialize($data));
		$app->getRequest()->getCookies()->add($cookie->name,$cookie);
	}

	/**
	 * Creates a cookie to store identity information.
	 * @param string $name the cookie name
	 * @return CHttpCookie the cookie used to store identity information
	 */
	protected function createIdentityCookie($name)
	{
		$cookie=new CHttpCookie($name,'');
		if(is_array($this->identityCookie))
		{
			foreach($this->identityCookie as $name=>$value)
				$cookie->$name=$value;
		}
		return $cookie;
	}

	/**
	 * @return string a prefix for the name of the session variables storing user session data.
	 */
	public function getStateKeyPrefix()
	{
		if($this->_keyPrefix!==null)
			return $this->_keyPrefix;
		else
			return $this->_keyPrefix=md5('Yii.'.get_class($this).'.'.Yii::app()->getId());
	}

	/**
	 * @param string $value a prefix for the name of the session variables storing user session data.
	 */
	public function setStateKeyPrefix($value)
	{
		$this->_keyPrefix=$value;
	}

	
	public function getState($key,$defaultValue=null)
	{
		$key=$this->getStateKeyPrefix().$key;
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
	}

	
	public function setState($key,$value,$defaultValue=null)
	{
		$key=$this->getStateKeyPrefix().$key;
		if($value===$defaultValue)
			unset($_SESSION[$key]);
		else
			$_SESSION[$key]=$value;
	}

	/**
	 * Returns a value indicating whether there is a state of the specified name.
	 * @param string $key state name
	 * @return boolean whether there is a state of the specified name.
	 */
	public function hasState($key)
	{
		$key=$this->getStateKeyPrefix().$key;
		return isset($_SESSION[$key]);
	}

	/**
	 * Clears all user identity information from persistent storage.
	 * This will remove the data stored via {@link setState}.
	 */
	public function clearStates()
	{
		$keys=array_keys($_SESSION);
		$prefix=$this->getStateKeyPrefix();
		$n=strlen($prefix);
		foreach($keys as $key)
		{
			if(!strncmp($key,$prefix,$n))
				unset($_SESSION[$key]);
		}
	}

	/**
	 * Returns all flash messages.
	 * This method is similar to {@link getFlash} except that it returns all
	 * currently available flash messages.
	 * @param boolean $delete whether to delete the flash messages after calling this method.
	 * @return array flash messages (key => message).
	 * @since 1.1.3
	 */
	public function getFlashes($delete=true)
	{
		$flashes=array();
		$prefix=$this->getStateKeyPrefix().self::FLASH_KEY_PREFIX;
		$keys=array_keys($_SESSION);
		$n=strlen($prefix);
		foreach($keys as $key)
		{
			if(!strncmp($key,$prefix,$n))
			{
				$flashes[substr($key,$n)]=$_SESSION[$key];
				if($delete)
					unset($_SESSION[$key]);
			}
		}
		if($delete)
			$this->setState(self::FLASH_COUNTERS,array());
		return $flashes;
	}

	/**
	 * Returns a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string $key key identifying the flash message
	 * @param mixed $defaultValue value to be returned if the flash message is not available.
	 * @param boolean $delete whether to delete this flash message after accessing it.
	 * Defaults to true.
	 * @return mixed the message message
	 */
	public function getFlash($key,$defaultValue=null,$delete=true)
	{
		$value=$this->getState(self::FLASH_KEY_PREFIX.$key,$defaultValue);
		if($delete)
			$this->setFlash($key,null);
		return $value;
	}

	/**
	 * Stores a flash message.
	 * A flash message is available only in the current and the next requests.
	 * @param string $key key identifying the flash message
	 * @param mixed $value flash message
	 * @param mixed $defaultValue if this value is the same as the flash message, the flash message
	 * will be removed. (Therefore, you can use setFlash('key',null) to remove a flash message.)
	 */
	public function setFlash($key,$value,$defaultValue=null)
	{
		$this->setState(self::FLASH_KEY_PREFIX.$key,$value,$defaultValue);
		$counters=$this->getState(self::FLASH_COUNTERS,array());
		if($value===$defaultValue)
			unset($counters[$key]);
		else
			$counters[$key]=0;
		$this->setState(self::FLASH_COUNTERS,$counters,array());
	}

	/**
	 * @param string $key key identifying the flash message
	 * @return boolean whether the specified flash message exists
	 */
	public function hasFlash($key)
	{
		return $this->getFlash($key, null, false)!==null;
	}

	/**
	 * Changes the current user with the specified identity information.
	 * This method is called by {@link login} and {@link restoreFromCookie}
	 * when the current user needs to be populated with the corresponding
	 * identity information. Derived classes may override this method
	 * by retrieving additional user-related information. Make sure the
	 * parent implementation is called first.
	 * @param mixed $id a unique identifier for the user
	 * @param string $name the display name for the user
	 * @param array $states identity states
	 */
	protected function changeIdentity($id,$name,$states)
	{
		Yii::app()->getSession()->regenerateID(true);
		$this->setId($id);
		$this->setName($name);
		$this->loadIdentityStates($states);
	}

	/**
	 * Retrieves identity states from persistent storage and saves them as an array.
	 * @return array the identity states
	 */
	protected function saveIdentityStates()
	{
		$states=array();
		foreach($this->getState(self::STATES_VAR,array()) as $name=>$dummy)
			$states[$name]=$this->getState($name);
		return $states;
	}

	/**
	 * Loads identity states from an array and saves them to persistent storage.
	 * @param array $states the identity states
	 */
	protected function loadIdentityStates($states)
	{
		$names=array();
		if(is_array($states))
		{
			foreach($states as $name=>$value)
			{
				$this->setState($name,$value);
				$names[$name]=true;
			}
		}
		$this->setState(self::STATES_VAR,$names);
	}

	/**
	 * Updates the internal counters for flash messages.
	 * This method is internally used by {@link CWebApplication}
	 * to maintain the availability of flash messages.
	 */
	protected function updateFlash()
	{
		$counters=$this->getState(self::FLASH_COUNTERS);
		if(!is_array($counters))
			return;
		foreach($counters as $key=>$count)
		{
			if($count)
			{
				unset($counters[$key]);
				$this->setState(self::FLASH_KEY_PREFIX.$key,null);
			}
			else
				$counters[$key]++;
		}
		$this->setState(self::FLASH_COUNTERS,$counters,array());
	}

	/**
	 * Updates the authentication status according to {@link authTimeout}.
	 * If the user has been inactive for {@link authTimeout} seconds, or {link absoluteAuthTimeout} has passed,
	 * he will be automatically logged out.
	 * @since 1.1.7
	 */
	protected function updateAuthStatus()
	{
		if(($this->authTimeout!==null || $this->absoluteAuthTimeout!==null) && !$this->getIsGuest())
		{
			$expires=$this->getState(self::AUTH_TIMEOUT_VAR);
			$expiresAbsolute=$this->getState(self::AUTH_ABSOLUTE_TIMEOUT_VAR);

			if ($expires!==null && $expires < time() || $expiresAbsolute!==null && $expiresAbsolute < time())
				$this->logout(false);
			else
				$this->setState(self::AUTH_TIMEOUT_VAR,time()+$this->authTimeout);
		}
	}

	/**
	 * Performs access check for this user.
	 * @param string $operation the name of the operation that need access check.
	 * @param array $params name-value pairs that would be passed to business rules associated
	 * with the tasks and roles assigned to the user.
	 * Since version 1.1.11 a param with name 'userId' is added to this array, which holds the value of
	 * {@link getId()} when {@link CDbAuthManager} or {@link CPhpAuthManager} is used.
	 * @param boolean $allowCaching whether to allow caching the result of access check.
	 * When this parameter
	 * is true (default), if the access check of an operation was performed before,
	 * its result will be directly returned when calling this method to check the same operation.
	 * If this parameter is false, this method will always call {@link CAuthManager::checkAccess}
	 * to obtain the up-to-date access result. Note that this caching is effective
	 * only within the same request and only works when <code>$params=array()</code>.
	 * @return boolean whether the operations can be performed by this user.
	 */
	public function checkAccess($operation,$params=array(),$allowCaching=true)
	{
		if($allowCaching && $params===array() && isset($this->_access[$operation]))
			return $this->_access[$operation];

		$access=Yii::app()->getAuthManager()->checkAccess($operation,$this->getId(),$params);
		if($allowCaching && $params===array())
			$this->_access[$operation]=$access;

		return $access;
	}
}
