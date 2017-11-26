<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

injectglobalsettings();


function injectglobalsettings()
{
    $settings = SettingGlobal::model()->findAll();

    //if ($dbvaluearray!==false)
    if (count($settings) > 0)
    {
        foreach ($settings as $setting)
        {
            
            Yii::app()->setConfig($setting->getAttribute('stg_name'), $setting->getAttribute('stg_value'));
        }
    }
}

function getGlobalSetting($settingname)
{
    $dbvalue = Yii::app()->getConfig($settingname);

    if ($dbvalue === false)
    {
        $dbvalue = SettingGlobal::model()->findByPk($settingname);

        if ($dbvalue === null)
        {
            Yii::app()->setConfig($settingname, null);
            $dbvalue = '';
        }
        else
        {
            $dbvalue = $dbvalue->getAttribute('stg_value');
        }

        if (Yii::app()->getConfig($settingname) !== false)
        {
            // If the setting was not found in the setting table but exists as a variable (from config.php)
            // get it and save it to the table
            setGlobalSetting($settingname, Yii::app()->getConfig($settingname));
            $dbvalue = Yii::app()->getConfig($settingname);
        }
    }

    return $dbvalue;
}

function setGlobalSetting($settingname, $settingvalue)
{
    if (Yii::app()->getConfig("demoMode")==true && ($settingname=='sitename' || $settingname=='defaultlang' || $settingname=='defaulthtmleditormode' || $settingname=='filterxsshtml'))
    {
        return; //don't save
    }

    if ($record = SettingGlobal::model()->findByPk($settingname))
    {
        $record->stg_value = $settingvalue;
        $record->save();
    }
    else
    {
        $record = new SettingGlobal;
        $record->stg_name = $settingname;
        $record->stg_value = $settingvalue;
        $record->save();
    }

    Yii::app()->setConfig($settingname, $settingvalue);
}

?>
