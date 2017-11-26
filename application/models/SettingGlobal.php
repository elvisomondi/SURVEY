<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class SettingGlobal extends LSActiveRecord
{
	/**
	 * Returns the static model of Settings table
	 */
	public static function model($class = __CLASS__)
	{
		return parent::model($class);
	}

	/**
	 * Returns the setting's table name to be used by the model
	 */
	public function tableName()
	{
		return '{{settings_global}}';
	}

	/**
	 * Returns the primary key of this table
	 */
	public function primaryKey()
	{
		return 'stg_name';
	}
	function updateSetting($settingname, $settingvalue)
    {

        $data = array(
            'stg_name' => $settingname,
            'stg_value' => $settingvalue
        );

        $user = Yii::app()->db->createCommand()->from("{{settings_global}}")->where("stg_name = :setting_name")->bindParam(":setting_name", $settingname, PDO::PARAM_STR);
        $query = $user->queryRow('settings_global');
        $user1 = Yii::app()->db->createCommand()->from("{{settings_global}}")->where("stg_name = :setting_name")->bindParam(":setting_name", $settingname, PDO::PARAM_STR);
        if(count($query) == 0)
        {
            return $user1->insert('{{settings_global}}', $data);
        }
        else
        {
            $user2 = Yii::app()->db->createCommand()->from("{{settings_global}}")->where('stg_name = :setting_name')->bindParam(":setting_name", $settingname, PDO::PARAM_STR);
            return $user2->update('{{settings_global}}', array('stg_value' => $settingvalue));
        }

    }
}
?>
