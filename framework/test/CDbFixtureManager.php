<?php

/**
 * CDbFixtureManager manages database fixtures during tests.
 */
class CDbFixtureManager extends CApplicationComponent
{
	/** name of the initialization script that would be executed
	 */
	public $initScript='init.php';
	
	public $initScriptSuffix='.init.php';
	
	public $basePath;
	
	public $connectionID='db';
	
	public $schemas=array('');

	private $_db;
	private $_fixtures;
	private $_rows;				// fixture name, row alias => row
	private $_records;			// fixture name, row alias => record (or class name)


	/**
	 * Initializes this application component.
	 */
	public function init()
	{
		parent::init();
		if($this->basePath===null)
			$this->basePath=Yii::getPathOfAlias('application.tests.fixtures');
		$this->prepare();
	}

	/**
	 * Returns the database connection used to load fixtures.
	 * @throws CException if {@link connectionID} application component is invalid
	 * @return CDbConnection the database connection
	 */
	public function getDbConnection()
	{
		if($this->_db===null)
		{
			$this->_db=Yii::app()->getComponent($this->connectionID);
			if(!$this->_db instanceof CDbConnection)
				throw new CException(Yii::t('yii','CDbTestFixture.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.',
					array('{id}'=>$this->connectionID)));
		}
		return $this->_db;
	}

	/**
	 * Prepares the fixtures for the whole test.
	 * This method is invoked in {@link init}. It executes the database init script
	 * if it exists. Otherwise, it will load all available fixtures.
	 */
	public function prepare()
	{
		$initFile=$this->basePath . DIRECTORY_SEPARATOR . $this->initScript;

		$this->checkIntegrity(false);

		if(is_file($initFile))
			require($initFile);
		else
		{
			foreach($this->getFixtures() as $tableName=>$fixturePath)
			{
				$this->resetTable($tableName);
				$this->loadFixture($tableName);
			}
		}
		$this->checkIntegrity(true);
	}

	
	public function resetTable($tableName)
	{
		$initFile=$this->basePath . DIRECTORY_SEPARATOR . $tableName . $this->initScriptSuffix;
		if(is_file($initFile))
			require($initFile);
		else
			$this->truncateTable($tableName);
	}

	
	public function loadFixture($tableName)
	{
		$fileName=$this->basePath.DIRECTORY_SEPARATOR.$tableName.'.php';
		if(!is_file($fileName))
			return false;

		$rows=array();
		$schema=$this->getDbConnection()->getSchema();
		$builder=$schema->getCommandBuilder();
		$table=$schema->getTable($tableName);

		foreach(require($fileName) as $alias=>$row)
		{
			$builder->createInsertCommand($table,$row)->execute();
			$primaryKey=$table->primaryKey;
			if($table->sequenceName!==null)
			{
				if(is_string($primaryKey) && !isset($row[$primaryKey]))
					$row[$primaryKey]=$builder->getLastInsertID($table);
				elseif(is_array($primaryKey))
				{
					foreach($primaryKey as $pk)
					{
						if(!isset($row[$pk]))
						{
							$row[$pk]=$builder->getLastInsertID($table);
							break;
						}
					}
				}
			}
			$rows[$alias]=$row;
		}
		return $rows;
	}

	
	public function getFixtures()
	{
		if($this->_fixtures===null)
		{
			$this->_fixtures=array();
			$schema=$this->getDbConnection()->getSchema();
			$folder=opendir($this->basePath);
			$suffixLen=strlen($this->initScriptSuffix);
			while($file=readdir($folder))
			{
				if($file==='.' || $file==='..' || $file===$this->initScript)
					continue;
				$path=$this->basePath.DIRECTORY_SEPARATOR.$file;
				if(substr($file,-4)==='.php' && is_file($path) && substr($file,-$suffixLen)!==$this->initScriptSuffix)
				{
					$tableName=substr($file,0,-4);
					if($schema->getTable($tableName)!==null)
						$this->_fixtures[$tableName]=$path;
				}
			}
			closedir($folder);
		}
		return $this->_fixtures;
	}

	/**
	 * Enables or disables database integrity check.
	 */
	public function checkIntegrity($check)
	{
		foreach($this->schemas as $schema)
			$this->getDbConnection()->getSchema()->checkIntegrity($check,$schema);
	}

	/**
	 * Removes all rows from the specified table and resets its primary key sequence, if any.
	 */
	public function truncateTable($tableName)
	{
		$db=$this->getDbConnection();
		$schema=$db->getSchema();
		if(($table=$schema->getTable($tableName))!==null)
		{
			$db->createCommand('DELETE FROM '.$table->rawName)->execute();
			$schema->resetSequence($table,1);
		}
		else
			throw new CException("Table '$tableName' does not exist.");
	}

	/**
	 * Truncates all tables in the specified schema.
	 * You may need to call {@link checkIntegrity} to turn off integrity check temporarily
	 * before you call this method.
	 * @param string $schema the schema name. Defaults to empty string, meaning the default database schema.
	 * @see truncateTable
	 */
	public function truncateTables($schema='')
	{
		$tableNames=$this->getDbConnection()->getSchema()->getTableNames($schema);
		foreach($tableNames as $tableName)
			$this->truncateTable($tableName);
	}

	/**
	 * Loads the specified fixtures.
	 * For each fixture, the corresponding table will be reset first by calling
	 * {@link resetTable} and then be populated with the fixture data.
	 */
	public function load($fixtures)
	{
		$schema=$this->getDbConnection()->getSchema();
		$schema->checkIntegrity(false);

		$this->_rows=array();
		$this->_records=array();
		foreach($fixtures as $fixtureName=>$tableName)
		{
			if($tableName[0]===':')
			{
				$tableName=substr($tableName,1);
				unset($modelClass);
			}
			else
			{
				$modelClass=Yii::import($tableName,true);
				$tableName=CActiveRecord::model($modelClass)->tableName();
			}
			if(($prefix=$this->getDbConnection()->tablePrefix)!==null)
				$tableName=preg_replace('/{{(.*?)}}/',$prefix.'\1',$tableName);
			$this->resetTable($tableName);
			$rows=$this->loadFixture($tableName);
			if(is_array($rows) && is_string($fixtureName))
			{
				$this->_rows[$fixtureName]=$rows;
				if(isset($modelClass))
				{
					foreach(array_keys($rows) as $alias)
						$this->_records[$fixtureName][$alias]=$modelClass;
				}
			}
		}

		$schema->checkIntegrity(true);
	}

	/**
	 * Returns the fixture data rows.
	 * The rows will have updated primary key values if the primary key is auto-incremental.
	 * @param string $name the fixture name
	 * @return array the fixture data rows. False is returned if there is no such fixture data.
	 */
	public function getRows($name)
	{
		if(isset($this->_rows[$name]))
			return $this->_rows[$name];
		else
			return false;
	}

	/**
	 * Returns the specified ActiveRecord instance in the fixture data.
	 */
	public function getRecord($name,$alias)
	{
		if(isset($this->_records[$name][$alias]))
		{
			if(is_string($this->_records[$name][$alias]))
			{
				$row=$this->_rows[$name][$alias];
				$model=CActiveRecord::model($this->_records[$name][$alias]);
				$key=$model->getTableSchema()->primaryKey;
				if(is_string($key))
					$pk=$row[$key];
				else
				{
					foreach($key as $k)
						$pk[$k]=$row[$k];
				}
				$this->_records[$name][$alias]=$model->findByPk($pk);
			}
			return $this->_records[$name][$alias];
		}
		else
			return false;
	}
}