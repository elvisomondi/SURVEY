<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class UserInGroup extends LSActiveRecord {

  
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }

  
    public function tableName()
    {
        return '{{user_in_groups}}';
    }

   
    public function primaryKey()
    {
        return array('ugid', 'uid');
    }

    
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'users' => array(self::BELONGS_TO, 'User', '', 'on' => 't.uid = users.uid')
        );
    }

    public function getAllRecords($condition=FALSE)
    {
        $criteria = new CDbCriteria;

        if ($condition != FALSE)
        {
            foreach ($condition as $item => $value)
            {
                $criteria->addCondition($item.'='.Yii::app()->db->quoteValue($value));
            }
        }

        $data = $this->findAll($criteria);

        return $data;
    }

    function insertRecords($data)
    {
        $user = Yii::app()->db->createCommand()->insert($this->tableName(), $data);
        return (bool) $user;
    }

    function join($fields, $from, $condition=FALSE, $join=FALSE, $order=FALSE)
    {
        $user = Yii::app()->db->createCommand();
        foreach ($fields as $field)
        {
            $user->select($field);
        }

        $user->from($from);

        if ($condition != FALSE)
        {
            $user->where($condition);
        }

        if ($order != FALSE)
        {
            $user->order($order);
        }

        if (isset($join['where'], $join['on']))
        {
            if (isset($join['left'])) {
                $user->leftjoin($join['where'], $join['on']);
            }else
            {
                $user->join($join['where'], $join['on']);
            }
        }

        $data = $user->queryRow();
        return $data;
    }

}
