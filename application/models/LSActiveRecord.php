<?php

class LSActiveRecord extends CActiveRecord
{

    /**
     * Lists the behaviors of this model
     */
    public function behaviors()
    {
        $aBehaviors=array();
        $sCreateFieldName=($this->hasAttribute('created')?'created':null);
        $sUpdateFieldName=($this->hasAttribute('modified')?'modified':null);
        $sDriverName = Yii::app()->db->getDriverName();
        if ($sDriverName=='sqlsrv' || $sDriverName=='dblib')
        {
            $sTimestampExpression=new CDbExpression('GETDATE()');
        }
        else
        {
            $sTimestampExpression=new CDbExpression('NOW()');
        }
        $aBehaviors['CTimestampBehavior'] = array(
            'class' => 'zii.behaviors.CTimestampBehavior',
            'createAttribute' => $sCreateFieldName,
            'updateAttribute' => $sUpdateFieldName,
            'timestampExpression' =>  $sTimestampExpression
        );
        // Some tables might not exist/not be up to date during a database upgrade so in that case disconnect plugin events
        if (!Yii::app()->getConfig('Updating'))
        {
            $aBehaviors['PluginEventBehavior']= array(
                'class' => 'application.models.behaviors.PluginEventBehavior'
            );
        }
        return $aBehaviors;
    }

    /**
     * Modified version that default to do the same as the original, but allows via a
     * third parameter to retrieve the result as array instead of active records
     */
    protected function query($criteria, $all = false, $asAR = true)
    {
        if ($asAR === true)
        {
            return parent::query($criteria, $all);
        } else
        {
            $this->beforeFind();
            $this->applyScopes($criteria);
            if (!$all)
            {
                $criteria->limit = 1;
            }

            $command = $this->getCommandBuilder()->createFindCommand($this->getTableSchema(), $criteria);
            //For debug, this command will get you the generated sql:
            //echo $command->getText();

            return $all ? $command->queryAll() : $command->queryRow();
        }
    }

    /**
     * Finds all active records satisfying the specified condition but returns them as array
     */
    public function findAllAsArray($condition = '', $params = array())
    {
        Yii::trace(get_class($this) . '.findAll()', 'system.db.ar.CActiveRecord');
        $criteria = $this->getCommandBuilder()->createCriteria($condition, $params);
        return $this->query($criteria, true, false);  //Notice the third parameter 'false'
    }


    /**
     * Return the max value for a field
     */
    public function getMaxId($field = null, $forceRefresh = false)
    {
        static $maxIds = array();

        if (is_null($field)) {
            $primaryKey = $this->getMetaData()->tableSchema->primaryKey;
            if (is_string($primaryKey)) {
                $field = $primaryKey;
            } else {
                // Composite key, throw a warning to the programmer
                throw new Exception(sprintf('Table %s has a composite primary key, please explicitly state what field you need the max value for.', $this->tableName()));
            }
        }

        if ($forceRefresh || !array_key_exists($field, $maxIds)) {
            $maxId = $this->dbConnection->createCommand()
                    ->select('MAX(' .  $this->dbConnection->quoteColumnName($field) . ')')
                    ->from($this->tableName())
                    ->queryScalar();

            // Save so we can reuse in the same request
            $maxIds[$field] = $maxId;
        }

        return $maxIds[$field];
    }
    
    /**
     * Return the min value for a field
     */
    public function getMinId($field = null, $forceRefresh = false)
    {
        static $minIds = array();

        if (is_null($field)) {
            $primaryKey = $this->getMetaData()->tableSchema->primaryKey;
            if (is_string($primaryKey)) {
                $field = $primaryKey;
            } else {
                // Composite key, throw a warning to the programmer
                throw new Exception(sprintf('Table %s has a composite primary key, please explicitly state what field you need the min value for.', $this->tableName()));           }
        }

        if ($forceRefresh || !array_key_exists($field, $minIds)) {
            $minId = $this->dbConnection->createCommand()
                    ->select('MIN(' .  $this->dbConnection->quoteColumnName($field) . ')')
                    ->from($this->tableName())
                    ->queryScalar();

            // Save so we can reuse in the same request
            $minIds[$field] = $minId;
        }

        return $minIds[$field];
    }

    /**
     * @todo This should also be moved to the behavior at some point.
     */
    public function deleteAllByAttributes($attributes,$condition='',$params=array())
    {
        $builder=$this->getCommandBuilder();
        $table=$this->getTableSchema();
        $criteria=$builder->createColumnCriteria($table,$attributes,$condition,$params);
        $this->dispatchPluginModelEvent('before'.get_class($this).'DeleteMany', $criteria);
        $this->dispatchPluginModelEvent('beforeModelDeleteMany',                $criteria);
        return parent::deleteAllByAttributes(array(), $criteria, array());
    }

}
