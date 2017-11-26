<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function CheckForDBUpgrades($subaction = null)
{
    $dbversionnumber = Yii::app()->getConfig('dbversionnumber');
    $currentDBVersion=GetGlobalSetting('DBVersion');
    $usertemplaterootdir = Yii::app()->getConfig('usertemplaterootdir');
    $standardtemplaterootdir = Yii::app()->getConfig('standardtemplaterootdir');
    if (intval($dbversionnumber)>intval($currentDBVersion))
    {
        Yii::app()->loadHelper('update/updatedb');
         if(isset($subaction) && $subaction=="yes")
        {
            echo Yii::app()->getController()->_getAdminHeader();
            $result=db_upgrade_all(intval($currentDBVersion));
            if ($result)
            {
                $data =
                '<div class="jumbotron message-box">'.
                    '<h2 class="">'.gT('Success').'</h2>'.
                    '<p class="lead">'.
                        sprintf(gT("Database has been successfully upgraded to version %s"),$dbversionnumber).
                    '</p>'.
                    '<p>'.
                        '<a href="'.Yii::app()->getController()->createUrl("/admin").'">'.gT("Back to main menu").'</a>'.
                    '</p>'.
                 '</div>';
            }
            else
            {
                $msg = '';
                foreach(yii::app()->user->getflashes() as $key => $message)
                {
                    $msg .=  '<div class="alert alert-danger flash-' . $key . '">' . $message . "</div>\n";
                }
                $data = $msg . "<p><a href='".Yii::app()->getController()->createUrl("/admin/databaseupdate/sa/db")."'>".gT("Please fix this error in your database and try again")."</a></p></div> ";
            }
            return $data;
        }
        else {
            return ShowDBUpgradeNotice();
        }
    }
}

/**
 * @return string html
 */
function ShowDBUpgradeNotice()
{
    $message = Yii::app()->getController()->renderPartial('/admin/databaseupdate/verify', null, true);
    return $message;
}

/**
 * @param string $sProperty
 */
function getDBConnectionStringProperty($sProperty)
{
    // Yii doesn't give us a good way to get the database name
    preg_match('/'.$sProperty.'=([^;]*)/', Yii::app()->db->getSchema()->getDbConnection()->connectionString, $aMatches);
    if ( count($aMatches) === 0 ) {
        return null;
    }
    return $aMatches[1];
}
