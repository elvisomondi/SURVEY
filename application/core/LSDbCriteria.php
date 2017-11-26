<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class LSDbCriteria extends CDbCriteria
{
    /**
     * Basic initialiser to the base controller class
     */
    // public function compare(string $column, mixed $value, boolean $partialMatch=false, string $operator='AND', boolean $escape=true)
    public function compare($column, $value, $partialMatch=false, $operator='AND', $escape=true)
    {
        if ($partialMatch && Yii::app()->db->getDriverName()=='pgsql')
        {
            $this->addSearchCondition($column, $value, true, $operator, 'ILIKE');
        }
        else
        {
            parent::compare($column, $value, $partialMatch, $operator, $escape);
        }
    }
}
