<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class ParticipantAttribute extends LSActiveRecord
{

	
	public static function model($class = __CLASS__)
	{
		return parent::model($class);
	}

   
    public function tableName()
    {
        return '{{participant_attribute}}';
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return array('participant_id', 'attribute_id');
    }

    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'participant' => array(self::HAS_ONE, 'Participant', 'participant_id'),
            'participant_attribute_names'=>array(self::BELONGS_TO, 'ParticipantAttributeName', 'attribute_id')
        );
    }

    /**
     * @param string $participantid
     * @return array
     */
    public function getAttributeInfo($participantid)
    {
        return self::model()->findAllByAttributes(array('participant_id' => $participantid));
    }

    /**
     * @param array $data
     * @return void
     */
    public function updateParticipantAttributeValue($data)
    {
        $query = Yii::app()->db->createCommand()
            ->select('*')
            ->where("participant_id='".$data['participant_id']."' AND attribute_id = ".$data['attribute_id'])
            ->from('{{participant_attribute}}')
            ->queryAll();
        if (count($query) > 0)
        {
            Yii::app()->db->createCommand()
                  ->update('{{participant_attribute}}', $data, "participant_id = '".$data['participant_id']."' AND attribute_id = ".$data['attribute_id']);
        } else {
            Yii::app()->db->createCommand()
                  ->insert('{{participant_attribute}}', $data);
        }
    }

}
