<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pdfHelper
{

  
    public static function getPdfLanguageSettings($language)
    {
        Yii::import('application.libraries.admin.pdf', true);
        Yii::import('application.helpers.surveytranslator_helper',true);

        $pdffont=Yii::app()->getConfig('pdfdefaultfont');
        if($pdffont=='auto')
        {
            $pdffont=PDF_FONT_NAME_DATA;
        }
        $pdfcorefont=array("freesans","dejavusans","courier","helvetica","freemono","symbol","times","zapfdingbats");
        if (in_array($pdffont,$pdfcorefont))
        {
            $alternatepdffontfile=Yii::app()->getConfig('alternatepdffontfile');
            if(array_key_exists($language,$alternatepdffontfile))
            {
                $pdffont = $alternatepdffontfile[$language];// Actually use only core font
            }
        }
        $pdffontsize=Yii::app()->getConfig('pdffontsize');
        if ($pdffontsize=='auto')
        {
            $pdffontsize=PDF_FONT_SIZE_MAIN;
        }
        $lg=array();
        $lg['a_meta_charset'] = 'UTF-8';
        if (getLanguageRTL($language))
        {
            $lg['a_meta_dir'] = 'rtl';
        }
        else
        {
            $lg['a_meta_dir'] = 'ltr';
        }
        $lg['a_meta_language'] = $language;
        $lg['w_page']=gT("page");

        return array('pdffont'=>$pdffont,'pdffontsize'=>$pdffontsize,'lg'=>$lg);
    }

}
