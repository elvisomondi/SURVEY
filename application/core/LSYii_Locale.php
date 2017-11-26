<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LSYii_Locale extends CLocale {
   

    public static function getInstance($id)
    {
        // Fix up the LimeSurvey language code for Yii
        $aLanguageData=getLanguageData();
        if (isset($aLanguageData[$id]['cldr']))
        {
            $id=$aLanguageData[$id]['cldr'];
        }
        static $locales=array();
        if(isset($locales[$id]))
            return $locales[$id];
        else
            return $locales[$id]=new CLocale($id);
    }

}