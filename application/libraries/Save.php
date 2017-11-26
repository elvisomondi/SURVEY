<?php

class Save {

    function showsaveform()
    {
        //Show 'SAVE FORM' only when click the 'Save so far' button the first time, or when duplicate is found on SAVE FORM.
        global $errormsg, $thissurvey, $surveyid, $clienttoken, $thisstep;
        $redata = compact(array_keys(get_defined_vars()));
        $sTemplatePath = $_SESSION['survey_'.$surveyid]['templatepath'];
        sendCacheHeaders();
        doHeader();
        $oTemplate = Template::model()->getInstance(null, $surveyid);
        echo templatereplace(file_get_contents($oTemplate->viewPath."startpage.pstpl"),array(),$redata);
        echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n"
        ."\t<script type='text/javascript'>\n"
        ."\t<!--\n"
        ."function checkconditions(value, name, type, evt_type)\n"
        ."\t{\n"
        ."\t}\n"
        ."\t//-->\n"
        ."\t</script>\n\n";

        echo CHtml::form(array("/survey/index","sid"=>$surveyid), 'post')."\n";

        //PRESENT OPTIONS SCREEN
        if (isset($errormsg) && $errormsg != "")
        {
            $errormsg .= "<p>".gT("Please try again.")."</p>";
        }
        echo templatereplace(file_get_contents($oTemplate->viewPath."save.pstpl"),array(),$redata);
        //END
        echo "<input type='hidden' name='thisstep' value='$thisstep' />\n";
        echo CHtml::hiddenField('token',$clienttoken)."\n";
        echo "<input type='hidden' name='saveprompt' value='Y' />\n";
        echo "</form>";

        echo templatereplace(file_get_contents($oTemplate->viewPath."endpage.pstpl"),array(),$redata);
        echo "</html>\n";
        exit;
    }

    function savedcontrol()
    {

        global $surveyid, $thissurvey, $errormsg, $publicurl, $sitename, $clienttoken, $thisstep;

        $timeadjust = getGlobalSetting('timeadjust');

        //Check that the required fields have been completed.
        $errormsg = '';
        if (empty($_POST['savename'])) $errormsg .= gT("You must supply a name for this saved session.")."<br />\n";
        if (empty($_POST['savepass'])) $errormsg .= gT("You must supply a password for this saved session.")."<br />\n";
        if (empty($_POST['savepass']) || empty($_POST['savepass2']) || $_POST['savepass'] != $_POST['savepass2'])
        {
            $errormsg .= gT("Your passwords do not match.")."<br />\n";
        }
        // if security question asnwer is incorrect
        if (function_exists("ImageCreate") && isCaptchaEnabled('saveandloadscreen', $thissurvey['usecaptcha']))
        {
            if (empty($_POST['loadsecurity'])
             || !isset($_SESSION['survey_'.$surveyid]['secanswer'])
             || $_POST['loadsecurity'] != $_SESSION['survey_'.$surveyid]['secanswer']
            )
            {
                $errormsg .= gT("The answer to the security question is incorrect.")."<br />\n";
            }
        }

        if (!empty($errormsg))
        {
            return;
        }

        $duplicate = SavedControl::model()->findByAttributes(array('sid' => $surveyid, 'identifier' => $_POST['savename']));
        $duplicate = SavedControl::model()->findByAttributes(array('sid' => $surveyid, 'identifier' => $_POST['savename']));
        if (strpos($_POST['savename'],'/')!==false || strpos($_POST['savepass'],'/')!==false || strpos($_POST['savename'],'&')!==false || strpos($_POST['savepass'],'&')!==false
            || strpos($_POST['savename'],'\\')!==false || strpos($_POST['savepass'],'\\')!==false)
        {
            $errormsg .= gT("You may not use slashes or ampersands in your name or password.")."<br />\n";
            return;
        }
        elseif (!empty($duplicate) && $duplicate->count() > 0)  // OK - AR count
        {
            $errormsg .= gT("This name has already been used for this survey. You must use a unique save name.")."<br />\n";
            return;
        }
        else
        {
            //INSERT BLANK RECORD INTO "survey_x" if one doesn't already exist
            if (!isset($_SESSION['survey_'.$surveyid]['srid']))
            {
                $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust);
                $sdata = array(
                    "datestamp" => $today,
                    "ipaddr" => getIPAddress(),
                    "startlanguage" => $_SESSION['survey_'.$surveyid]['s_lang'],
                    "refurl" => ((isset($_SESSION['survey_'.$surveyid]['refurl'])) ? $_SESSION['survey_'.$surveyid]['refurl'] : getenv('HTTP_REFERER'))
                );
                if (SurveyDynamic::model($thissurvey['sid'])->insert($sdata))    // Checked
                {
                    $srid = getLastInsertID('{{survey_' . $surveyid . '}}');
                    $_SESSION['survey_'.$surveyid]['srid'] = $srid;
                }
                else
                {
                    safeDie("Unable to insert record into survey table.<br /><br />");
                }
            }
            //CREATE ENTRY INTO "saved_control"
            $today = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust);
            $saved_control = new SavedControl;
            $saved_control->sid = $surveyid;
            $saved_control->srid = $_SESSION['survey_'.$surveyid]['srid'];
            $saved_control->identifier = $_POST['savename']; // Binding does escape, so no quoting/escaping necessary
            $saved_control->access_code = hash('sha256',$_POST['savepass']);
            $saved_control->email = $_POST['saveemail'];
            $saved_control->ip = ($thissurvey['ipaddr']=='Y')?getIPAddress():'';
            $saved_control->saved_thisstep = $thisstep;
            $saved_control->status = 'S';
            $saved_control->saved_date = $today;
            if (isset($_SESSION['survey_'.$surveyid]['refurl']))
            {
                $saved_control->refurl = $_SESSION['survey_'.$surveyid]['refurl'];
            }
            else
            {
                $saved_control->refurl = getenv("HTTP_REFERER");
            }

            if ($saved_control->save())
            {
                $scid = getLastInsertID('{{saved_control}}');
                $_SESSION['survey_'.$surveyid]['scid'] = $scid;
            }
            else
            {
                safeDie("Unable to insert record into saved_control table.<br /><br />");
            }

            $_SESSION['survey_'.$surveyid]['holdname'] = $_POST['savename']; //Session variable used to load answers every page. Unsafe - so it has to be taken care of on output
            $_SESSION['survey_'.$surveyid]['holdpass'] = $_POST['savepass']; //Session variable used to load answers every page. Unsafe - so it has to be taken care of on output

            //Email if needed
            if (isset($_POST['saveemail']) && validateEmailAddress($_POST['saveemail']))
            {
                $subject  = gT("Saved Survey Details") . " - " . $thissurvey['name'];
                $message  = gT("Thank you for saving your survey in progress.  The following details can be used to return to this survey and continue where you left off.  Please keep this e-mail for your reference - we cannot retrieve the password for you.");
                $message .= "\n\n".$thissurvey['name']."\n\n";
                $message .= gT("Name").": ".$_POST['savename']."\n";
                $message .= gT("Password").": ".$_POST['savepass']."\n\n";
                $message .= gT("Reload your survey by clicking on the following link (or pasting it into your browser):")."\n";
                $aParams=array('scid'=>$scid,'lang'=>App()->language,'loadname'=>$_POST['savename'],'loadpass'=>$_POST['savepass']);
                if (!empty($clienttoken))
                {
                    $aParams['token'] = $clienttoken;
                }
                $message .= Yii::app()->getController()->createAbsoluteUrl("/survey/index/sid/{$surveyid}/loadall/reload",$aParams);

                $from="{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";
                if (SendEmailMessage($message, $subject, $_POST['saveemail'], $from, $sitename, false, getBounceEmail($surveyid)))
                {
                    $emailsent = "Y";
                }
                else
                {
                    $errormsg .= gT('Error: Email failed, this may indicate a PHP Mail Setup problem on the server. Your survey details have still been saved, however you will not get an email with the details. You should note the "name" and "password" you just used for future reference.');
                    if (trim($thissurvey['adminemail'])=='')
                    {
                        $errormsg .=gT('(Reason: Admin email address empty)');
                    }
                }
            }
            return gT('Your survey was successfully saved.');
        }
    }

    /**
    * This functions saves the answer time for question/group and whole survey.
    * [ It compares current time with the time in $_POST['start_time'] ]
    * The times are saved in table: {prefix}{surveytable}_timings
    * @return void
    */
    function set_answer_time()
    {
        global $thissurvey;
        if (!isset($_POST['start_time']))
        {
            return; // means haven't passed welcome page yet.
        }

        if (isset($_POST['lastanswer']))
        {
            $setField = $_POST['lastanswer'];
        }
        elseif (isset($_POST['lastgroup']))
        {
            $setField = $_POST['lastgroup'];
        }
        $passedTime = str_replace(',','.',round(microtime(true) - $_POST['start_time'],2));
        if(!isset($setField)){ //we show the whole survey on one page - we don't have to save time for group/question
            $query = "UPDATE {{survey_{$thissurvey['sid']}_timings}} SET "
            ."interviewtime = (CASE WHEN interviewtime IS NULL THEN 0 ELSE interviewtime END) + " .$passedTime
            ." WHERE id = " .$_SESSION['survey_'.$thissurvey['sid']]['srid'];

        }
        else
        {
            $aColumnNames=SurveyTimingDynamic::model($thissurvey['sid'])->getTableSchema()->columnNames;
            $setField .= "time";
            if (!in_array($setField,$aColumnNames)) die('Invalid last group timing fieldname');
            $setField = Yii::app()->db->quoteColumnName($setField);
            $query = "UPDATE {{survey_{$thissurvey['sid']}_timings}} SET "
            ."interviewtime =  (CASE WHEN interviewtime IS NULL THEN 0 ELSE interviewtime END) + " .$passedTime .","
            .$setField." =  (CASE WHEN $setField IS NULL THEN 0 ELSE $setField END) + ".$passedTime
            ." WHERE id = " .$_SESSION['survey_'.$thissurvey['sid']]['srid'];
        }
        Yii::app()->db->createCommand($query)->execute();
    }
}
