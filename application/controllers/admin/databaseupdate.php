<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class databaseupdate extends Survey_Common_Action
{
    /**
    * Update database
    */
    public function db($continue = null)
    {
        Yii::app()->loadHelper("update/update");
        if(isset($continue) && $continue=="yes")
        {
            $aViewUrls['output'] = CheckForDBUpgrades($continue);
            $aData['display']['header'] = false;
        }
        else
        {
            $aData['display']['header'] = true;
            $aViewUrls['output'] = CheckForDBUpgrades();
        }

        $aData['updatedbaction'] = true;

        $this->_renderWrappedTemplate('update', $aViewUrls, $aData);

        //$aData = array_merge($aData, $aViewUrls);
        //Yii::app()->getController()->renderPartial('databaseupdate/db', $aData);
    }
}
