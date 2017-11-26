<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class LSYii_CaseValidator extends CValidator {

    public $type='lower';


    public function validateAttribute($object,$attribute){

        if ($this->type=='upper')
        {
            if (strtoupper($object->$attribute)==$object->$attribute){
                return;
            }
            else
            {
                $this->addError($object, $attribute, gT('Text needs to be uppercase.'));
                return;
            }
        }
        else // default to lowercase
        {
            if (strtolower($object->$attribute)==$object->$attribute){
                return;
            }
            else
            {
                $this->addError($object, $attribute, gT('Text needs to be lowercase.'));
                return;
            }
        }
        return;
    }

}
