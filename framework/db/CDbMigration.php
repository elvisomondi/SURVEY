<?php
/**
 * CDbMigration is the base class for representing a database migration.
 
 */
abstract class CDbMigration extends CComponent
{
	private $_db;

	/**
	 * This method contains the logic to be executed when applying this migration.
	 * Child classes may implement this method to provide actual migration logic.
	 */
	public function up()
	{
		$transaction=$this->getDbConnection()->beginTransaction();
		try
		{
			if($this->safeUp()===false)
			{
				$transaction->rollback();
				return false;
			}
			$transaction->commit();
		}
		catch(Exception $e)
		{
			echo "Exception: ".$e->getMessage().' ('.$e->getFile().':'.$e->getLine().")\n";
			echo $e->getTraceAsString()."\n";
			$transaction->rollback();
			return false;
		}
	}

	/**
	 * This method contains the logic to be executed when removing this migration.
	 * Child classes may override this method if the corresponding migrations can be removed.
	 * @return boolean Returning false means, the migration will not be applied.
	 */
	public function down()
	{
		$transaction=$this->getDbConnection()->beginTransaction();
		try
		{
			if($this->safeDown()===false)
			{
				$transaction->rollback();
				return false;
			}
			$transaction->commit();
		}
		catch(Exception $e)
		{
			echo "Exception: ".$e->getMessage().' ('.$e->getFile().':'.$e->getLine().")\n";
			echo $e->getTraceAsString()."\n";
			$transaction->rollback();
			return false;
		}
	}

	/**
	 * This method contains the logic to be executed when applying this migration.
	 */
	public function safeUp()
	{
	}

	/**
	 * This method contains the logic to be executed when removing this migration.
	 */
	public function safeDown()
	{
	}

	/**
	 * Returns the currently active database connection.
	 */
	public function getDbConnection()
	{
		if($this->_db===null)
		{
			$this->_db=Yii::app()->getComponent('db');
			if(!$this->_db instanceof CDbConnection)
				throw new CException(Yii::t('yii', 'The "db" application component must be configured to be a CDbConnection object.'));
		}
		return $this->_db;
	}

	/**
	 * Sets the currently active database connection.
	 * The database connection will be used by the methods such as {@link insert}, {@link createTable}.
	 * @param CDbConnection $db the database connection component
	 */
	public function setDbConnection($db)
	{
		$this->_db=$db;
	}

	/**
	 * Executes a SQL statement.
	 */
	public function execute($sql, $params=array())
	{
		echo "    > execute SQL: $sql ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand($sql)->execute($params);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Creates and executes an INSERT SQL statement.
	 */
	public function insert($table, $columns)
	{
		echo "    > insert into $table ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->insert($table, $columns);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Creates and executes an INSERT SQL statement with multiple data.
	 */
	public function insertMultiple($table, $data)
	{
		echo "    > insert into $table ...";
		$time=microtime(true);
		$builder=$this->getDbConnection()->getSchema()->getCommandBuilder();
		$command=$builder->createMultipleInsertCommand($table,$data);
		$command->execute();
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Creates and executes an UPDATE SQL statement.
	 * The method will properly escape the column names and bind the values to be updated.
	 */
	public function update($table, $columns, $conditions='', $params=array())
	{
		echo "    > update $table ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->update($table, $columns, $conditions, $params);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Creates and executes a DELETE SQL statement.
	 * @param string $table the table where the data will be deleted from.

	 */
	public function delete($table, $conditions='', $params=array())
	{
		echo "    > delete from $table ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->delete($table, $conditions, $params);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Builds and executes a SQL statement for creating a new DB table.
	 */
	public function createTable($table, $columns, $options=null)
	{
		echo "    > create table $table ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->createTable($table, $columns, $options);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Builds and executes a SQL statement for renaming a DB table.
	 */
	public function renameTable($table, $newName)
	{
		echo "    > rename table $table to $newName ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->renameTable($table, $newName);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Builds and executes a SQL statement for dropping a DB table.
	 * @param string $table the table to be dropped. The name will be properly quoted by the method.
	 */
	public function dropTable($table)
	{
		echo "    > drop table $table ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->dropTable($table);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Builds and executes a SQL statement for truncating a DB table.
	 * @param string $table the table to be truncated. The name will be properly quoted by the method.
	 */
	public function truncateTable($table)
	{
		echo "    > truncate table $table ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->truncateTable($table);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Builds and executes a SQL statement for adding a new DB column.
	 */
	public function addColumn($table, $column, $type)
	{
		echo "    > add column $column $type to table $table ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->addColumn($table, $column, $type);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Builds and executes a SQL statement for dropping a DB column.
	 */
	public function dropColumn($table, $column)
	{
		echo "    > drop column $column from table $table ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->dropColumn($table, $column);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Builds and executes a SQL statement for renaming a column.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $name the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 */
	public function renameColumn($table, $name, $newName)
	{
		echo "    > rename column $name in table $table to $newName ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->renameColumn($table, $name, $newName);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Builds and executes a SQL statement for changing the definition of a column.
	 */
	public function alterColumn($table, $column, $type)
	{
		echo "    > alter column $column in table $table to $type ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->alterColumn($table, $column, $type);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Builds a SQL statement for adding a foreign key constraint to an existing table.
	 * The method will properly quote the table and column names.

	 */
	public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete=null, $update=null)
	{
		echo "    > add foreign key $name: $table (".(is_array($columns) ? implode(',', $columns) : $columns).
			 ") references $refTable (".(is_array($refColumns) ? implode(',', $refColumns) : $refColumns).") ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Builds a SQL statement for dropping a foreign key constraint.
	 * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
	 */
	public function dropForeignKey($name, $table)
	{
		echo "    > drop foreign key $name from table $table ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->dropForeignKey($name, $table);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Builds and executes a SQL statement for creating a new index.
	 */
	public function createIndex($name, $table, $columns, $unique=false)
	{
		echo "    > create".($unique ? ' unique':'')." index $name on $table (".(is_array($columns) ? implode(',', $columns) : $columns).") ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->createIndex($name, $table, $columns, $unique);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Builds and executes a SQL statement for dropping an index.
	 * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
	 */
	public function dropIndex($name, $table)
	{
		echo "    > drop index $name ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->dropIndex($name, $table);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	
	public function refreshTableSchema($table)
	{
		echo "    > refresh table $table schema cache ...";
		$time=microtime(true);
		$this->getDbConnection()->getSchema()->getTable($table,true);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	/**
	 * Builds and executes a SQL statement for creating a primary key, supports composite primary keys.
	 */
	public function addPrimaryKey($name,$table,$columns)
	{
		echo "    > alter table $table add constraint $name primary key (".(is_array($columns) ? implode(',', $columns) : $columns).") ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->addPrimaryKey($name,$table,$columns);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}

	
	public function dropPrimaryKey($name,$table)
	{
		echo "    > alter table $table drop primary key $name ...";
		$time=microtime(true);
		$this->getDbConnection()->createCommand()->dropPrimaryKey($name,$table);
		echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
	}
}
