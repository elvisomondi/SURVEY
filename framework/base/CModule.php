<?php

abstract class CModule extends CComponent
{
	/**
	 * @var array the IDs of the application components that should be preloaded.
	 */
	public $preload=array();
	
	public $behaviors=array();

	private $_id;
	private $_parentModule;
	private $_basePath;
	private $_modulePath;
	private $_params;
	private $_modules=array();
	private $_moduleConfig=array();
	private $_components=array();
	private $_componentConfig=array();


	
	public function __construct($id,$parent,$config=null)
	{
		$this->_id=$id;
		$this->_parentModule=$parent;

		// set basePath at early as possible to avoid trouble
		if(is_string($config))
			$config=require($config);
		if(isset($config['basePath']))
		{
			$this->setBasePath($config['basePath']);
			unset($config['basePath']);
		}
		Yii::setPathOfAlias($id,$this->getBasePath());

		$this->preinit();

		$this->configure($config);
		$this->attachBehaviors($this->behaviors);
		$this->preloadComponents();

		$this->init();
	}

	/**
	 * Getter magic method.
	 */
	public function __get($name)
	{
		if($this->hasComponent($name))
			return $this->getComponent($name);
		else
			return parent::__get($name);
	}

	/**
	 * Checks if a property value is null.
	 */
	public function __isset($name)
	{
		if($this->hasComponent($name))
			return $this->getComponent($name)!==null;
		else
			return parent::__isset($name);
	}

	/**
	 * Returns the module ID.
	 * @return string the module ID.
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * Sets the module ID.
	 * @param string $id the module ID
	 */
	public function setId($id)
	{
		$this->_id=$id;
	}

	/**
	 * Returns the root directory of the module.
	 * @return string the root directory of the module. Defaults to the directory containing the module class.
	 */
	public function getBasePath()
	{
		if($this->_basePath===null)
		{
			$class=new ReflectionClass(get_class($this));
			$this->_basePath=dirname($class->getFileName());
		}
		return $this->_basePath;
	}

	/**
	 * Sets the root directory of the module.
	 */
	public function setBasePath($path)
	{
		if(($this->_basePath=realpath($path))===false || !is_dir($this->_basePath))
			throw new CException(Yii::t('yii','Base path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * Returns user-defined parameters.
	 * @return CAttributeCollection the list of user-defined parameters
	 */
	public function getParams()
	{
		if($this->_params!==null)
			return $this->_params;
		else
		{
			$this->_params=new CAttributeCollection;
			$this->_params->caseSensitive=true;
			return $this->_params;
		}
	}

	/**
	 * Sets user-defined parameters.
	 * @param array $value user-defined parameters. This should be in name-value pairs.
	 */
	public function setParams($value)
	{
		$params=$this->getParams();
		foreach($value as $k=>$v)
			$params->add($k,$v);
	}

	/**
	 * Returns the directory that contains the application modules.
	 * @return string the directory that contains the application modules. Defaults to the 'modules' subdirectory of {@link basePath}.
	 */
	public function getModulePath()
	{
		if($this->_modulePath!==null)
			return $this->_modulePath;
		else
			return $this->_modulePath=$this->getBasePath().DIRECTORY_SEPARATOR.'modules';
	}

	/**
	 * Sets the directory that contains the application modules.
	 * @param string $value the directory that contains the application modules.
	 * @throws CException if the directory is invalid
	 */
	public function setModulePath($value)
	{
		if(($this->_modulePath=realpath($value))===false || !is_dir($this->_modulePath))
			throw new CException(Yii::t('yii','The module path "{path}" is not a valid directory.',
				array('{path}'=>$value)));
	}

	/**
	 * Sets the aliases that are used in the module.
	 * @param array $aliases list of aliases to be imported
	 */
	public function setImport($aliases)
	{
		foreach($aliases as $alias)
			Yii::import($alias);
	}

	/**
	 * Defines the root aliases.
	 */
	public function setAliases($mappings)
	{
		foreach($mappings as $name=>$alias)
		{
			if(($path=Yii::getPathOfAlias($alias))!==false)
				Yii::setPathOfAlias($name,$path);
			else
				Yii::setPathOfAlias($name,$alias);
		}
	}

	/**
	 * Returns the parent module.
	 * @return CModule the parent module. Null if this module does not have a parent.
	 */
	public function getParentModule()
	{
		return $this->_parentModule;
	}

	/**
	 * Retrieves the named application module.
	 */
	public function getModule($id)
	{
		if(isset($this->_modules[$id]) || array_key_exists($id,$this->_modules))
			return $this->_modules[$id];
		elseif(isset($this->_moduleConfig[$id]))
		{
			$config=$this->_moduleConfig[$id];
			if(!isset($config['enabled']) || $config['enabled'])
			{
				Yii::trace("Loading \"$id\" module",'system.base.CModule');
				$class=$config['class'];
				unset($config['class'], $config['enabled']);
				if($this===Yii::app())
					$module=Yii::createComponent($class,$id,null,$config);
				else
					$module=Yii::createComponent($class,$this->getId().'/'.$id,$this,$config);
				return $this->_modules[$id]=$module;
			}
		}
	}

	/**
	 * Returns a value indicating whether the specified module is installed.
	 * @param string $id the module ID
	 * @return boolean whether the specified module is installed.
	 * @since 1.1.2
	 */
	public function hasModule($id)
	{
		return isset($this->_moduleConfig[$id]) || isset($this->_modules[$id]);
	}

	/**
	 * Returns the configuration of the currently installed modules.
	 * @return array the configuration of the currently installed modules (module ID => configuration)
	 */
	public function getModules()
	{
		return $this->_moduleConfig;
	}

	public function setModules($modules,$merge=true)
	{
		foreach($modules as $id=>$module)
		{
			if(is_int($id))
			{
				$id=$module;
				$module=array();
			}
			if(isset($this->_moduleConfig[$id]) && $merge)
				$this->_moduleConfig[$id]=CMap::mergeArray($this->_moduleConfig[$id],$module);
			else
			{
				if(!isset($module['class']))
				{
					if (Yii::getPathOfAlias($id)===false)
						Yii::setPathOfAlias($id,$this->getModulePath().DIRECTORY_SEPARATOR.$id);
					$module['class']=$id.'.'.ucfirst($id).'Module';
				}
				$this->_moduleConfig[$id]=$module;
			}
		}
	}

	public function hasComponent($id)
	{
		return isset($this->_components[$id]) || isset($this->_componentConfig[$id]);
	}

	public function getComponent($id,$createIfNull=true)
	{
		if(isset($this->_components[$id]))
			return $this->_components[$id];
		elseif(isset($this->_componentConfig[$id]) && $createIfNull)
		{
			$config=$this->_componentConfig[$id];
			if(!isset($config['enabled']) || $config['enabled'])
			{
				Yii::trace("Loading \"$id\" application component",'system.CModule');
				unset($config['enabled']);
				$component=Yii::createComponent($config);
				$component->init();
				return $this->_components[$id]=$component;
			}
		}
	}

	public function setComponent($id,$component,$merge=true)
	{
		if($component===null)
		{
			unset($this->_components[$id]);
			return;
		}
		elseif($component instanceof IApplicationComponent)
		{
			$this->_components[$id]=$component;

			if(!$component->getIsInitialized())
				$component->init();

			return;
		}
		elseif(isset($this->_components[$id]))
		{
			if(isset($component['class']) && get_class($this->_components[$id])!==$component['class'])
			{
				unset($this->_components[$id]);
				$this->_componentConfig[$id]=$component; //we should ignore merge here
				return;
			}

			foreach($component as $key=>$value)
			{
				if($key!=='class')
					$this->_components[$id]->$key=$value;
			}
		}
		elseif(isset($this->_componentConfig[$id]['class'],$component['class'])
			&& $this->_componentConfig[$id]['class']!==$component['class'])
		{
			$this->_componentConfig[$id]=$component; //we should ignore merge here
			return;
		}

		if(isset($this->_componentConfig[$id]) && $merge)
			$this->_componentConfig[$id]=CMap::mergeArray($this->_componentConfig[$id],$component);
		else
			$this->_componentConfig[$id]=$component;
	}

	public function getComponents($loadedOnly=true)
	{
		if($loadedOnly)
			return $this->_components;
		else
			return array_merge($this->_componentConfig, $this->_components);
	}

	public function setComponents($components,$merge=true)
	{
		foreach($components as $id=>$component)
			$this->setComponent($id,$component,$merge);
	}

	/**
	 * Configures the module with the specified configuration.
	 * @param array $config the configuration array
	 */
	public function configure($config)
	{
		if(is_array($config))
		{
			foreach($config as $key=>$value)
				$this->$key=$value;
		}
	}

	/**
	 * Loads static application components.
	 */
	protected function preloadComponents()
	{
		foreach($this->preload as $id)
			$this->getComponent($id);
	}

	protected function preinit()
	{
	}

	protected function init()
	{
	}
}
