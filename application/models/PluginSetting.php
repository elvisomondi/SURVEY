<?php


class PluginSetting extends CActiveRecord {

    /**
     * @param type $className
     * @return PluginSetting
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    public function tableName() {
        return '{{plugin_settings}}';
    }
}