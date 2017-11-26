<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class htmleditor_pop extends Survey_Common_Action
{

    function index()
    {
        Yii::app()->loadHelper('admin/htmleditor');
        $aData = array(
            'ckLanguage' => sTranslateLangCode2CK(Yii::app()->session['adminlang']),
            'sFieldName' => sanitize_xss_string(App()->request->getQuery('name')),// The fieldname : an input name
            'sFieldText' => sanitize_xss_string(App()->request->getQuery('text')), // Not text : is description of the window
            'sFieldType' => sanitize_xss_string(App()->request->getQuery('type')), // Type of field : welcome email_invite question ....
            'sAction' => sanitize_paranoid_string(App()->request->getQuery('action')),
            'iSurveyId' => sanitize_int(App()->request->getQuery('sid',0)),
            'iGroupId' => sanitize_int(App()->request->getQuery('gid',0)),
            'iQuestionId'=> sanitize_int(App()->request->getQuery('qid',0)),
        );
        if (!$aData['sFieldName'])
        {
            $this->getController()->render('/admin/htmleditor/pop_nofields_view', $aData);
        }
        else
        {
            $aData['sControlIdEna'] = $aData['sFieldName'] . '_popupctrlena';
            $aData['sControlIdDis'] = $aData['sFieldName'] . '_popupctrldis';
            $aData['toolbarname'] = 'popup';
            $aData['htmlformatoption'] = '';

            if (in_array($aData['sFieldType'], array('email-inv', 'email-reg', 'email-conf', 'email-rem')))
            {
                $aData['htmlformatoption'] = ',fullPage:true';
            }

            $this->getController()->render('/admin/htmleditor/pop_editor_view', $aData);
        }

    }

}
