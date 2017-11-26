<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LSYii_EmailIDNAValidator extends CValidator {

    public $allowEmpty=false;
    public $allowMultiple=false;


    public function validateAttribute($object,$attribute){

        if ($object->$attribute=='' && $this->allowEmpty)
        {
             return;
        }

        if ($this->allowMultiple)
        {
            $aEmailAdresses = preg_split( "/(,|;)/", $object->$attribute );
        }
        else
        {
            $aEmailAdresses=array($object->$attribute);
        }

        foreach ($aEmailAdresses as $sEmailAddress)
        {
            if (!validateEmailAddress($sEmailAddress))
            {
                $this->addError($object, $attribute, gT('Invalid email address.'));
                return;
            }

        }
        return;
    }

}
