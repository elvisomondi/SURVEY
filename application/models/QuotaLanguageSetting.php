<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class QuotaLanguageSetting extends LSActiveRecord
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
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{quota_languagesettings}}';
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'quotals_id';
	}

	/**
	 * Returns the relations
	 *
	 * @access public
	 * @return array
	 */
	public function relations()
	{
		$alias = $this->getTableAlias();
		return array(
			'quota' => array(self::BELONGS_TO, 'Quota', 'quotals_quota_id'),
		);
	}

    /**
    * Returns this model's validation rules
    *
    */
    public function rules()
    {
        return array(
            array('quotals_message','required'),
            array('quotals_url','url'),
            array('quotals_name','LSYii_Validators'),// No access in quota editor, set to quota.name
            array('quotals_message','LSYii_Validators'),
            array('quotals_url','LSYii_Validators','isUrl'=>true),
            array('quotals_urldescrip','LSYii_Validators'),
            array('quotals_url','urlValidator'),
        );
    }
    public function urlValidator(){
        if($this->quota->autoload_url == 1 && !$this->quotals_url ){
            $this->addError('quotals_url',gT('URL must be set if autoload URL is turned on!'));
        }
    }

    public function attributeLabels()
    {
        return array(
            'quotals_message'=> gT("Quota message:"),
            'quotals_url'=> gT("URL:"),
            'quotals_urldescrip'=> gT("URL Description:"),
        );
    }


	function insertRecords($data)
    {
        $settings = new self;
		foreach ($data as $k => $v)
			$settings->$k = $v;
		return $settings->save();
    }
}