<?php



function getSurveyDefaultSettings()
{
    return array(
    'active'=>'N',
    'questionindex'               => 0,
    'format'                   => 'G', //Group-by-group mode
    'template'                 => $this->config->item('defaulttemplate'),
    'allowsave'                => 'Y',
    'allowprev'                => 'N',
    'nokeyboard'               => 'N',
    'printanswers'             => 'N',
    'publicstatistics'         => 'N',
    'publicgraphs'             => 'N',
    'public'                   => 'Y',
    'autoredirect'             => 'N',
    'tokenlength'              => 15,
    'allowregister'            => 'N',
    'usecookie'                => 'N',
    'usecaptcha'               => 'D',
    'htmlemail'                => 'Y',
    'emailnotificationto'      => '',
    'anonymized'               => 'N',
    'datestamp'                => 'N',
    'ipaddr'                   => 'N',
    'refurl'                   => 'N',
    'tokenanswerspersistence'  => 'N',
    'alloweditaftercompletion' => 'N',
    'startdate'                => '',
    'savetimings'              => 'N',
    'expires'                  => '',
    'showqnumcode'             => 'X',
    'showwelcome'              => 'Y',
    'emailresponseto'          => '',
    'assessments'              => 'N',
    'navigationdelay'          => 0);
}
