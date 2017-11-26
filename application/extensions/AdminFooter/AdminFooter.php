<?php

class AdminFooter extends CWidget
{
    public function run()
        {
            //If user is not logged in, don't print the version number information in the footer.
            if (empty(Yii::app()->session['loginID']))
            {
                $versionnumber="";
                $versiontitle="";
                $buildtext="";
            } else {
                $versionnumber = Yii::app()->getConfig("versionnumber");
                $versiontitle = gT('Version');
                $buildtext = "";
                if(Yii::app()->getConfig("buildnumber")!="") {
                   $buildtext = "+".Yii::app()->getConfig("buildnumber");
                }
            }

            $aData = array(
                'versionnumber' => $versionnumber,
                'versiontitle'  => $versiontitle,
                'buildtext'     => $buildtext
            );

            $this->render('footer', $aData);
        }
}