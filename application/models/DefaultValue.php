<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class DefaultValue extends LSActiveRecord
{
    /* Default value when create (from DB) , leave some because add rules */
    public $specialtype='';
    public $scale_id='';
    public $sqid=0;
    public $language='';// required ?

   
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
        return '{{defaultvalues}}';
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string[]
     */
    public function primaryKey()
    {
        return array('qid', 'specialtype', 'scale_id', 'sqid', 'language');
    }

    /**
    * Relations with questions
    *
    * @access public
    * @return array
    */
    public function relations()
    {
        $alias = $this->getTableAlias();
        return array(
            'question' => array(self::HAS_ONE, 'Question', '',
            'on' => "$alias.qid = question.qid",
            ),
        );
    }
    /**
    * Returns this model's validation rules
    *
    */
    public function rules()
    {
        return array(
            array('qid', 'required'),
            array('qid', 'numerical','integerOnly'=>true),
            array('qid', 'unique', 'criteria'=>array(
                    'condition'=>'specialtype=:specialtype and scale_id=:scale_id and sqid=:sqid and language=:language',
                    'params'=>array(
                        ':specialtype'=>$this->specialtype,
                        ':scale_id'=>$this->scale_id,
                        ':sqid'=>$this->sqid,
                        ':language'=>$this->language,
                    )
                ),
                'message'=>'{attribute} "{value}" is already in use.'),
        );
    }
    function insertRecords($data)
    {
        $oRecord = new self;
        foreach ($data as $k => $v)
            $oRecord->$k = $v;
        if($oRecord->validate())
            return $oRecord->save();
        tracevar($oRecord->getErrors());
    }
}
?>
