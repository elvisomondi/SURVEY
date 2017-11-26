<?php

class Load_answers {

    function run($args) {
        extract($args);
        $redata = compact(array_keys(get_defined_vars()));
        $sTemplatePath=$_SESSION['survey_'.$surveyid]['templatepath'];
        sendCacheHeaders();
         doHeader();

        $oTemplate = Template::model()->getInstance(null, $surveyid);

        echo templatereplace(file_get_contents($oTemplate->viewPath."startpage.pstpl"),array(),$redata);

        echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n"
        ."\t<script type='text/javascript'>\n"
        ."function checkconditions(value, name, type, evt_type)\n"
        ."\t{\n"
        ."\t}\n"
        ."\t</script>\n\n";

        echo CHtml::form(array("/survey/index","sid"=>$surveyid), 'post')."\n";
        echo templatereplace(file_get_contents($oTemplate->viewPath."load.pstpl"),array(),$redata);

        //PRESENT OPTIONS SCREEN (Replace with Template Later)
        //END
        echo "<input type='hidden' name='loadall' value='reload' />\n";
        if (isset($clienttoken) && $clienttoken != "")
        {
            echo CHtml::hiddenField('token',$clienttoken);
        }
        echo "</form>";

        echo templatereplace(file_get_contents($oTemplate->viewPath."endpage.pstpl"),array(),$redata);
        doFooter($surveyid);
        exit;


    }
}
