<?php


class Plugin extends CActiveRecord {

    /**
     * @param type $className
     * @return Plugin
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    public function tableName() {
        return '{{plugins}}';
    }
}