<?php

class ParticipantAttributeNameLang extends LSActiveRecord
{
	/**
	 * Returns the static model of Participant Attribute Names Lang table
    */
    public function primaryKey()
    {
        return array('attribute_id', 'lang');
    }

	public static function model($class = __CLASS__)
	{
		return parent::model($class);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{participant_attribute_names_lang}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that will receive user inputs.
		return array(
            array('attribute_name','filter','filter' => 'strip_tags'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
			array('attribute_id, attribute_name, lang', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
            'participant_attribute_names'=>array(self::BELONGS_TO, 'ParticipantAttributeName', 'attribute_id')
		);
	}

}
