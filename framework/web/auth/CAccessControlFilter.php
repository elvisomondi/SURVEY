<?php

class CAccessControlFilter extends CFilter
{
	
	public $message;

	private $_rules=array();

	/**
	 * @return array list of access rules.
	 */
	public function getRules()
	{
		return $this->_rules;
	}

	/**
	 * @param array $rules list of access rules.
	 */
	public function setRules($rules)
	{
		foreach($rules as $rule)
		{
			if(is_array($rule) && isset($rule[0]))
			{
				$r=new CAccessRule;
				$r->allow=$rule[0]==='allow';
				foreach(array_slice($rule,1) as $name=>$value)
				{
					if($name==='expression' || $name==='roles' || $name==='message' || $name==='deniedCallback')
						$r->$name=$value;
					else
						$r->$name=array_map('strtolower',$value);
				}
				$this->_rules[]=$r;
			}
		}
	}


	protected function preFilter($filterChain)
	{
		$app=Yii::app();
		$request=$app->getRequest();
		$user=$app->getUser();
		$verb=$request->getRequestType();
		$ip=$request->getUserHostAddress();

		foreach($this->getRules() as $rule)
		{
			if(($allow=$rule->isUserAllowed($user,$filterChain->controller,$filterChain->action,$ip,$verb))>0) // allowed
				break;
			elseif($allow<0) // denied
			{
				if(isset($rule->deniedCallback))
					call_user_func($rule->deniedCallback, $rule);
				else
					$this->accessDenied($user,$this->resolveErrorMessage($rule));
				return false;
			}
		}

		return true;
	}

	
	protected function resolveErrorMessage($rule)
	{
		if($rule->message!==null)
			return $rule->message;
		elseif($this->message!==null)
			return $this->message;
		else
			return Yii::t('yii','You are not authorized to perform this action.');
	}

	protected function accessDenied($user,$message)
	{
		if($user->getIsGuest())
			$user->loginRequired();
		else
			throw new CHttpException(403,$message);
	}
}


class CAccessRule extends CComponent
{
	/**
	 * @var boolean whether this is an 'allow' rule or 'deny' rule.
	 */
	public $allow;
	/**
	 * @var array list of action IDs that this rule applies to. The comparison is case-insensitive.
	 * If no actions are specified, rule applies to all actions.
	 */
	public $actions;
	/**
	 * @var array list of controller IDs that this rule applies to. The comparison is case-insensitive.
	 */
	public $controllers;
	/**
	 * @var array list of user names that this rule applies to. The comparison is case-insensitive.
	 * If no user names are specified, rule applies to all users.
	 */
	public $users;
	
	public $roles;
	/**
	 * @var array IP patterns.
	 */
	public $ips;
	/**
	 * @var array list of request types (e.g. GET, POST) that this rule applies to.
	 */
	public $verbs;
	
	public $expression;
	
	public $message;
	
	public $deniedCallback;


	
	public function isUserAllowed($user,$controller,$action,$ip,$verb)
	{
		if($this->isActionMatched($action)
			&& $this->isUserMatched($user)
			&& $this->isRoleMatched($user)
			&& $this->isIpMatched($ip)
			&& $this->isVerbMatched($verb)
			&& $this->isControllerMatched($controller)
			&& $this->isExpressionMatched($user))
			return $this->allow ? 1 : -1;
		else
			return 0;
	}

	/**
	 * @param CAction $action the action
	 * @return boolean whether the rule applies to the action
	 */
	protected function isActionMatched($action)
	{
		return empty($this->actions) || in_array(strtolower($action->getId()),$this->actions);
	}

	/**
	 * @param CController $controller the controller
	 * @return boolean whether the rule applies to the controller
	 */
	protected function isControllerMatched($controller)
	{
		return empty($this->controllers) || in_array(strtolower($controller->getUniqueId()),$this->controllers);
	}

	/**
	 * @param IWebUser $user the user
	 * @return boolean whether the rule applies to the user
	 */
	protected function isUserMatched($user)
	{
		if(empty($this->users))
			return true;
		foreach($this->users as $u)
		{
			if($u==='*')
				return true;
			elseif($u==='?' && $user->getIsGuest())
				return true;
			elseif($u==='@' && !$user->getIsGuest())
				return true;
			elseif(!strcasecmp($u,$user->getName()))
				return true;
		}
		return false;
	}

	/**
	 * @param IWebUser $user the user object
	 * @return boolean whether the rule applies to the role
	 */
	protected function isRoleMatched($user)
	{
		if(empty($this->roles))
			return true;
		foreach($this->roles as $key=>$role)
		{
			if(is_numeric($key))
			{
				if($user->checkAccess($role))
					return true;
			}
			else
			{
				if($user->checkAccess($key,$role))
					return true;
			}
		}
		return false;
	}

	/**
	 * @param string $ip the IP address
	 * @return boolean whether the rule applies to the IP address
	 */
	protected function isIpMatched($ip)
	{
		if(empty($this->ips))
			return true;
		foreach($this->ips as $rule)
		{
			if($rule==='*' || $rule===$ip || (($pos=strpos($rule,'*'))!==false && !strncmp($ip,$rule,$pos)))
				return true;
		}
		return false;
	}

	/**
	 * @param string $verb the request method
	 * @return boolean whether the rule applies to the request
	 */
	protected function isVerbMatched($verb)
	{
		return empty($this->verbs) || in_array(strtolower($verb),$this->verbs);
	}

	/**
	 * @param IWebUser $user the user
	 * @return boolean the expression value. True if the expression is not specified.
	 */
	protected function isExpressionMatched($user)
	{
		if($this->expression===null)
			return true;
		else
			return $this->evaluateExpression($this->expression, array('user'=>$user));
	}
}
