<?php


/**
 * CDbDataReader represents a forward-only stream of rows from a query result set.
 */
class CDbDataReader extends CComponent implements Iterator, Countable
{
	private $_statement;
	private $_closed=false;
	private $_row;
	private $_index=-1;

	/**
	 * Constructor.
	 * @param CDbCommand $command the command generating the query result
	 */
	public function __construct(CDbCommand $command)
	{
		$this->_statement=$command->getPdoStatement();
		$this->_statement->setFetchMode(PDO::FETCH_ASSOC);
	}

	/**
	 * Binds a column to a PHP variable.
	 */
	public function bindColumn($column, &$value, $dataType=null)
	{
		if($dataType===null)
			$this->_statement->bindColumn($column,$value);
		else
			$this->_statement->bindColumn($column,$value,$dataType);
	}

	/**
	 * Set the default fetch mode for this statement
	 */
	public function setFetchMode($mode)
	{
		$params=func_get_args();
		call_user_func_array(array($this->_statement,'setFetchMode'),$params);
	}

	/**
	 * Advances the reader to the next row in a result set.
	 * @return array|false the current row, false if no more row available
	 */
	public function read()
	{
		return $this->_statement->fetch();
	}

	
	public function readColumn($columnIndex)
	{
		return $this->_statement->fetchColumn($columnIndex);
	}

	public function readObject($className,$fields)
	{
		return $this->_statement->fetchObject($className,$fields);
	}

	public function readAll()
	{
		return $this->_statement->fetchAll();
	}

	/**
	 * Advances the reader to the next result when reading the results of a batch of statements.
	 * This method is only useful when there are multiple result sets
	 * returned by the query. Not all DBMS support this feature.
	 * @return boolean Returns true on success or false on failure.
	 */
	public function nextResult()
	{
		if(($result=$this->_statement->nextRowset())!==false)
			$this->_index=-1;
		return $result;
	}

	/**
	 * Closes the reader.
	 * This frees up the resources allocated for executing this SQL statement.
	 * Read attempts after this method call are unpredictable.
	 */
	public function close()
	{
		$this->_statement->closeCursor();
		$this->_closed=true;
	}

	/**
	 * whether the reader is closed or not.
	 * @return boolean whether the reader is closed or not.
	 */
	public function getIsClosed()
	{
		return $this->_closed;
	}

	/**
	 * Returns the number of rows in the result set.
	 * Note, most DBMS may not give a meaningful count.
	 * In this case, use "SELECT COUNT(*) FROM tableName" to obtain the number of rows.
	 * @return integer number of rows contained in the result.
	 */
	public function getRowCount()
	{
		return $this->_statement->rowCount();
	}

	/**
	 * Returns the number of rows in the result set.
	 * This method is required by the Countable interface.
	 * Note, most DBMS may not give a meaningful count.
	 * In this case, use "SELECT COUNT(*) FROM tableName" to obtain the number of rows.
	 * @return integer number of rows contained in the result.
	 */
	public function count()
	{
		return $this->getRowCount();
	}

	/**
	 * Returns the number of columns in the result set.
	 * Note, even there's no row in the reader, this still gives correct column number.
	 * @return integer the number of columns in the result set.
	 */
	public function getColumnCount()
	{
		return $this->_statement->columnCount();
	}

	/**
	 * Resets the iterator to the initial state.
	 * This method is required by the interface Iterator.
	 * @throws CException if this method is invoked twice
	 */
	public function rewind()
	{
		if($this->_index<0)
		{
			$this->_row=$this->_statement->fetch();
			$this->_index=0;
		}
		else
			throw new CDbException(Yii::t('yii','CDbDataReader cannot rewind. It is a forward-only reader.'));
	}

	/**
	 * Returns the index of the current row.
	 * This method is required by the interface Iterator.
	 * @return integer the index of the current row.
	 */
	public function key()
	{
		return $this->_index;
	}

	/**
	 * Returns the current row.
	 * This method is required by the interface Iterator.
	 * @return mixed the current row.
	 */
	public function current()
	{
		return $this->_row;
	}

	/**
	 * Moves the internal pointer to the next row.
	 * This method is required by the interface Iterator.
	 */
	public function next()
	{
		$this->_row=$this->_statement->fetch();
		$this->_index++;
	}

	/**
	 * Returns whether there is a row of data at current position.
	 * This method is required by the interface Iterator.
	 * @return boolean whether there is a row of data at current position.
	 */
	public function valid()
	{
		return $this->_row!==false;
	}
}
