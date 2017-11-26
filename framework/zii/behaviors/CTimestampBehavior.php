<?php



class CTimestampBehavior extends CActiveRecordBehavior {
	/**
	 * @var mixed The name of the attribute to store the creation time.  Set to null to not
	 * use a timestamp for the creation attribute.  Defaults to 'create_time'
	 */
	public $createAttribute = 'create_time';
	/**
	 * @var mixed The name of the attribute to store the modification time.  Set to null to not
	 * use a timestamp for the update attribute.  Defaults to 'update_time'
	 */
	public $updateAttribute = 'update_time';

	/**
	 * @var bool Whether to set the update attribute to the creation timestamp upon creation.
	 * Otherwise it will be left alone.  Defaults to false.
	 */
	public $setUpdateOnCreate = false;

	
	public $timestampExpression;

	/**
	 * @var array Maps column types to database method
	 */
	protected static $map = array(
			'datetime'=>'NOW()',
			'timestamp'=>'NOW()',
			'date'=>'NOW()',
	);

	/**
	 * Responds to {@link CModel::onBeforeSave} event.
	 * Sets the values of the creation or modified attributes as configured
	 *
	 * @param CModelEvent $event event parameter
	 */
	public function beforeSave($event) {
		if ($this->getOwner()->getIsNewRecord() && ($this->createAttribute !== null)) {
			$this->getOwner()->{$this->createAttribute} = $this->getTimestampByAttribute($this->createAttribute);
		}
		if ((!$this->getOwner()->getIsNewRecord() || $this->setUpdateOnCreate) && ($this->updateAttribute !== null)) {
			$this->getOwner()->{$this->updateAttribute} = $this->getTimestampByAttribute($this->updateAttribute);
		}
	}

	/**
	 * Gets the appropriate timestamp depending on the column type $attribute is
	 *
	 * @param string $attribute $attribute
	 * @return mixed timestamp (eg unix timestamp or a mysql function)
	 */
	protected function getTimestampByAttribute($attribute) {
		if ($this->timestampExpression instanceof CDbExpression)
			return $this->timestampExpression;
		elseif ($this->timestampExpression !== null)
		{
			try
			{
				return @eval('return '.$this->timestampExpression.';');
			}
			catch (ParseError $e)
			{
				return false;
			}
		}

		$columnType = $this->getOwner()->getTableSchema()->getColumn($attribute)->dbType;
		return $this->getTimestampByColumnType($columnType);
	}

	/**
	 * Returns the appropriate timestamp depending on $columnType
	 *
	 * @param string $columnType $columnType
	 * @return mixed timestamp (eg unix timestamp or a mysql function)
	 */
	protected function getTimestampByColumnType($columnType) {
		return isset(self::$map[$columnType]) ? new CDbExpression(self::$map[$columnType]) : time();
	}
}
