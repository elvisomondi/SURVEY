<?php


Yii::import('system.gii.CCodeGenerator');
Yii::import('system.gii.CCodeModel');
Yii::import('system.gii.CCodeFile');
Yii::import('system.gii.CCodeForm');

/**
 * GiiModule is a module that provides Web-based code generation capabilities.
 */
class GiiModule extends CWebModule
{
	
	public $password;
	
	public $ipFilters=array('127.0.0.1','::1');
	
	public $generatorPaths=array('application.gii');
	
	public $newFileMode=0666;
	
	public $newDirMode=0777;

	private $_assetsUrl;

	/**
	 * Initializes the gii module.
	 */
	public function init()
	{
		parent::init();
		Yii::setPathOfAlias('gii',dirname(__FILE__));
		Yii::app()->setComponents(array(
			'errorHandler'=>array(
				'class'=>'CErrorHandler',
				'errorAction'=>$this->getId().'/default/error',
			),
			'user'=>array(
				'class'=>'CWebUser',
				'stateKeyPrefix'=>'gii',
				'loginUrl'=>Yii::app()->createUrl($this->getId().'/default/login'),
			),
			'widgetFactory' => array(
				'class'=>'CWidgetFactory',
				'widgets' => array()
			)
		), false);
		$this->generatorPaths[]='gii.generators';
		$this->controllerMap=$this->findGenerators();
	}

	/**
	 * @return string the base URL that contains all published asset files of gii.
	 */
	public function getAssetsUrl()
	{
		if($this->_assetsUrl===null)
			$this->_assetsUrl=Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('gii.assets'));
		return $this->_assetsUrl;
	}

	/**
	 * @param string $value the base URL that contains all published asset files of gii.
	 */
	public function setAssetsUrl($value)
	{
		$this->_assetsUrl=$value;
	}


	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			$route=$controller->id.'/'.$action->id;
			if(!$this->allowIp(Yii::app()->request->userHostAddress) && $route!=='default/error')
				throw new CHttpException(403,"You are not allowed to access this page.");

			$publicPages=array(
				'default/login',
				'default/error',
			);
			if($this->password!==false && Yii::app()->user->isGuest && !in_array($route,$publicPages))
				Yii::app()->user->loginRequired();
			else
				return true;
		}
		return false;
	}

	/**
	 * Checks to see if the user IP is allowed by {@link ipFilters}.
	 * @param string $ip the user IP
	 * @return boolean whether the user IP is allowed by {@link ipFilters}.
	 */
	protected function allowIp($ip)
	{
		if(empty($this->ipFilters))
			return true;
		foreach($this->ipFilters as $filter)
		{
			if($filter==='*' || $filter===$ip || (($pos=strpos($filter,'*'))!==false && !strncmp($ip,$filter,$pos)))
				return true;
		}
		return false;
	}

	/**
	 * Finds all available code generators and their code templates.
	 * @return array
	 */
	protected function findGenerators()
	{
		$generators=array();
		$n=count($this->generatorPaths);
		for($i=$n-1;$i>=0;--$i)
		{
			$alias=$this->generatorPaths[$i];
			$path=Yii::getPathOfAlias($alias);
			if($path===false || !is_dir($path))
				continue;

			$names=scandir($path);
			foreach($names as $name)
			{
				if($name[0]!=='.' && is_dir($path.'/'.$name))
				{
					$className=ucfirst($name).'Generator';
					if(is_file("$path/$name/$className.php"))
					{
						$generators[$name]=array(
							'class'=>"$alias.$name.$className",
						);
					}

					if(isset($generators[$name]) && is_dir("$path/$name/templates"))
					{
						$templatePath="$path/$name/templates";
						$dirs=scandir($templatePath);
						foreach($dirs as $dir)
						{
							if($dir[0]!=='.' && is_dir($templatePath.'/'.$dir))
								$generators[$name]['templates'][$dir]=strtr($templatePath.'/'.$dir,array('/'=>DIRECTORY_SEPARATOR,'\\'=>DIRECTORY_SEPARATOR));
						}
					}
				}
			}
		}
		return $generators;
	}
}