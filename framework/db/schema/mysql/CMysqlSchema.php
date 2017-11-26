<?php

class CMysqlSchema extends CDbSchema
{
	
	public $columnTypes=array(
		'pk' => 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		'bigpk' => 'bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		'string' => 'varchar(255)',
		'text' => 'text',
		'integer' => 'int(11)',
		'bigint' => 'bigint(20)',
		'float' => 'float',
		'decimal' => 'decimal',
		'datetime' => 'datetime',
		'timestamp' => 'timestamp',
		'time' => 'time',
		'date' => 'date',
		'binary' => 'blob',
		'boolean' => 'tinyint(1)',
		'money' => 'decimal(19,4)',
	);

	
	public function quoteSimpleTableName($name)
	{
		return '`'.$name.'`';
	}


	public function quoteSimpleColumnName($name)
	{
		return '`'.$name.'`';
	}


	public function compareTableNames($name1,$name2)
	{
		return parent::compareTableNames(strtolower($name1),strtolower($name2));
	}

	
	public function resetSequence($table,$value=null)
	{
		if($table->sequenceName===null)
			return;
		if($value!==null)
			$value=(int)$value;
		else
		{
			$value=(int)$this->getDbConnection()
				->createCommand("SELECT MAX(`{$table->primaryKey}`) FROM {$table->rawName}")
				->queryScalar();
			$value++;
		}
		$this->getDbConnection()
			->createCommand("ALTER TABLE {$table->rawName} AUTO_INCREMENT=$value")
			->execute();
	}


	public function checkIntegrity($check=true,$schema='')
	{
		$this->getDbConnection()->createCommand('SET FOREIGN_KEY_CHECKS='.($check?1:0))->execute();
	}

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return CMysqlTableSchema driver dependent table metadata. Null if the table does not exist.
	 */
	protected function loadTable($name)
	{
		$table=new CMysqlTableSchema;
		$this->resolveTableNames($table,$name);

		if($this->findColumns($table))
		{
			$this->findConstraints($table);
			return $table;
		}
		else
			return null;
	}

	/**
	 * Generates various kinds of table names.
	 * @param CMysqlTableSchema $table the table instance
	 * @param string $name the unquoted table name
	 */
	protected function resolveTableNames($table,$name)
	{
		$parts=explode('.',str_replace(array('`','"'),'',$name));
		if(isset($parts[1]))
		{
			$table->schemaName=$parts[0];
			$table->name=$parts[1];
			$table->rawName=$this->quoteTableName($table->schemaName).'.'.$this->quoteTableName($table->name);
		}
		else
		{
			$table->name=$parts[0];
			$table->rawName=$this->quoteTableName($table->name);
		}
	}

	/**
	 * Collects the table column metadata.
	 * @param CMysqlTableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$sql='SHOW FULL COLUMNS FROM '.$table->rawName;
		try
		{
			$columns=$this->getDbConnection()->createCommand($sql)->queryAll();
		}
		catch(Exception $e)
		{
			return false;
		}
		foreach($columns as $column)
		{
			$c=$this->createColumn($column);
			$table->columns[$c->name]=$c;
			if($c->isPrimaryKey)
			{
				if($table->primaryKey===null)
					$table->primaryKey=$c->name;
				elseif(is_string($table->primaryKey))
					$table->primaryKey=array($table->primaryKey,$c->name);
				else
					$table->primaryKey[]=$c->name;
				if($c->autoIncrement)
					$table->sequenceName='';
			}
		}
		return true;
	}

	/**
	 * Creates a table column.
	 * @param array $column column metadata
	 * @return CDbColumnSchema normalized column metadata
	 */
	protected function createColumn($column)
	{
		$c=new CMysqlColumnSchema;
		$c->name=$column['Field'];
		$c->rawName=$this->quoteColumnName($c->name);
		$c->allowNull=$column['Null']==='YES';
		$c->isPrimaryKey=strpos($column['Key'],'PRI')!==false;
		$c->isForeignKey=false;
		$c->init($column['Type'],$column['Default']);
		$c->autoIncrement=strpos(strtolower($column['Extra']),'auto_increment')!==false;
		if(isset($column['Comment']))
			$c->comment=$column['Comment'];

		return $c;
	}

	/**
	 * @return float server version.
	 */
	protected function getServerVersion()
	{
		$version=$this->getDbConnection()->getAttribute(PDO::ATTR_SERVER_VERSION);
		$digits=array();
		preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $digits);
		return floatval($digits[1].'.'.$digits[2].$digits[3]);
	}

	/**
	 * Collects the foreign key column details for the given table.
	 * @param CMysqlTableSchema $table the table metadata
	 */
	protected function findConstraints($table)
	{
		$row=$this->getDbConnection()->createCommand('SHOW CREATE TABLE '.$table->rawName)->queryRow();
		$matches=array();
		$regexp='/FOREIGN KEY\s+\(([^\)]+)\)\s+REFERENCES\s+([^\(^\s]+)\s*\(([^\)]+)\)/mi';
		foreach($row as $sql)
		{
			if(preg_match_all($regexp,$sql,$matches,PREG_SET_ORDER))
				break;
		}
		foreach($matches as $match)
		{
			$keys=array_map('trim',explode(',',str_replace(array('`','"'),'',$match[1])));
			$fks=array_map('trim',explode(',',str_replace(array('`','"'),'',$match[3])));
			foreach($keys as $k=>$name)
			{
				$table->foreignKeys[$name]=array(str_replace(array('`','"'),'',$match[2]),$fks[$k]);
				if(isset($table->columns[$name]))
					$table->columns[$name]->isForeignKey=true;
			}
		}
	}

	
	protected function findTableNames($schema='')
	{
		if($schema==='')
			return $this->getDbConnection()->createCommand('SHOW TABLES')->queryColumn();
		$names=$this->getDbConnection()->createCommand('SHOW TABLES FROM '.$this->quoteTableName($schema))->queryColumn();
		foreach($names as &$name)
			$name=$schema.'.'.$name;
		return $names;
	}

	
	protected function createCommandBuilder()
	{
		return new CMysqlCommandBuilder($this);
	}

	
	public function renameColumn($table, $name, $newName)
	{
		$db=$this->getDbConnection();
		$row=$db->createCommand('SHOW CREATE TABLE '.$db->quoteTableName($table))->queryRow();
		if($row===false)
			throw new CDbException(Yii::t('yii','Unable to find "{column}" in table "{table}".',array('{column}'=>$name,'{table}'=>$table)));
		if(isset($row['Create Table']))
			$sql=$row['Create Table'];
		else
		{
			$row=array_values($row);
			$sql=$row[1];
		}
		if(preg_match_all('/^\s*[`"](.*?)[`"]\s+(.*?),?$/m',$sql,$matches))
		{
			foreach($matches[1] as $i=>$c)
			{
				if($c===$name)
				{
					return "ALTER TABLE ".$db->quoteTableName($table)
						. " CHANGE ".$db->quoteColumnName($name)
						. ' '.$db->quoteColumnName($newName).' '.$matches[2][$i];
				}
			}
		}

		// try to give back a SQL anyway
		return "ALTER TABLE ".$db->quoteTableName($table)
			. " CHANGE ".$db->quoteColumnName($name).' '.$newName;
	}

	
	public function dropForeignKey($name, $table)
	{
		return 'ALTER TABLE '.$this->quoteTableName($table)
			.' DROP FOREIGN KEY '.$this->quoteColumnName($name);
	}


	
	public function dropPrimaryKey($name,$table)
	{
		return 'ALTER TABLE ' . $this->quoteTableName($table) . ' DROP PRIMARY KEY';

	}
	
	
	public function addPrimaryKey($name,$table,$columns)
	{
		if(is_string($columns))
			$columns=preg_split('/\s*,\s*/',$columns,-1,PREG_SPLIT_NO_EMPTY);
		foreach($columns as $i=>$col)
			$columns[$i]=$this->quoteColumnName($col);
		return 'ALTER TABLE ' . $this->quoteTableName($table) . ' ADD PRIMARY KEY ('
			. implode(', ', $columns). ' )';
	}
}
