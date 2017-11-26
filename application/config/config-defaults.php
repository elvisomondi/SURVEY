<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



$config = array();


$config['rootdir']            =   getcwd(); 


// Site Info
$config['sitename']           =   'Utafiti';    
$config['scriptname']         =   'admin.php';      

$config['defaultuser']        =   'admin';          
$config['defaultpass']        =   'password';     

// Styling options
$config['admintheme']         =  'Sea_Green';    
$config['adminthemeiconsize'] =  32;               

// If the user enters password incorrectly
$config['maxLoginAttempt']    =   3;               
$config['timeOutTime']        =   60 * 10;          

// Site Settings
$config['printanswershonorsconditions'] = 1;     
$config['allow_templates_to_overwrite_views'] = 0;

// Only applicable, of course, if you have chosen 'R' for $dropdowns and/or $lwcdropdowns
$config['repeatheadings']     =   '25';            
$config['minrepeatheadings']  =   3;                
$config['defaultlang']        =   'en';             
$config['timeadjust']         =   0;             
$config['allowexportalldb']   =   0;               
$config['maxdumpdbrecords']   =   500;              
$config['deletenonvalues']    =   1;                
$config['stringcomparizonoperators']   =   0;       
$config['shownoanswer']       =   1;                // Show 'no answer' for non mandatory questions ( 0 = no , 1 = yes , 2 = survey admin can choose )
$config['blacklistallsurveys']     =  'N';         
$config['blacklistnewsurveys']     =  'N';         
$config['blockaddingtosurveys']     =  'Y';         
$config['hideblacklisted']     =  'N';              
$config['deleteblacklisted']     =  'N';            
$config['allowunblacklist']     =  'N';             
$config['userideditable']     =  'N';               
$config['defaulttemplate']    =  'default';         

$config['allowedtemplateuploads'] = 'gif,ico,jpg,png,css,js,map,json,eot,svg,ttf,woff,txt,md,xml,woff2';  // File types allowed to be uploaded in the templates section.

$config['allowedresourcesuploads'] = '7z,aiff,asf,avi,bmp,csv,doc,docx,fla,flv,gif,gz,gzip,ico,jpeg,jpg,mid,mov,mp3,mp4,mpc,mpeg,mpg,ods,odt,pdf,png,ppt,pxd,qt,ram,rar,rm,rmi,rmvb,rtf,sdc,sitd,swf,sxc,sxw,tar,tgz,tif,tiff,txt,vsd,wav,wma,wmv,xls,xlsx,xml,zip,pstpl,css,js';   // File types allowed to be uploaded in the resources sections, and with the HTML Editor

$config['memory_limit']        =  '128';  

$config['showpopups']         =   1;                // Show popup messages if mandatory or conditional questions have not been answered correctly.
// 1=Show popup message, 0=Show message on page instead.

$config['maxemails']          = 50;               // The maximum number of emails to send in one go (this is to prevent your mail server or script from timeouting when sending mass mail)

// Enable or Disable LDAP feature
$config['enableLdap'] = false;

	
$config['filterout_incomplete_answers'] = 'show';

$config['strip_query_from_referer_url'] = false;


$config['defaulthtmleditormode'] = 'none';

$config['surveyPreview_require_Auth'] = true;


$config['use_one_time_passwords'] = false;


$config['display_user_password_in_html'] = false;


$config['display_user_password_in_email'] = true;


$config['default_displayed_auth_method']= 'Authdb';

$config['auth_webserver'] = false;

$config['auth_webserver_user_map'] = array();

$config['auth_webserver_autocreate_user'] = false;


$config['auth_webserver_autocreate_profile'] = Array(
    'full_name' => 'autouser',
    'email' => 'autouser@test.test',
    'lang' => 'en',
    'htmleditormode' => $config['defaulthtmleditormode']
);

$config['auth_webserver_autocreate_permissions'] = Array(
    'surveys' => array('create'=>true,'read'=>true,'update'=>true,'delete'=>true)
);

// filterxsshtml
// Enables filtering of suspicious html tags in survey, group, questions
// and answer texts in the administration interface
$config['filterxsshtml'] = true;

$config['usercontrolSameGroupPolicy'] = true;



$config['demoMode'] = false;


$config['demoModePrefill'] = false;



$config['column_style'] = 'ul';


$config['hide_groupdescr_allinone']=true;



$config['use_firebug_lite'] = false;


$config['showaggregateddata'] = 1;


$config['standard_templates_readonly'] =  true;



$config['showsgqacode'] =  false;


$config['showrelevance'] =  false;

/**
* To prevent brute force against forgotten password functionality, there is a random delay
* that prevent attacker from knowing whether username and email address are valid or not.
*/
$config['minforgottenpasswordemaildelay'] =  500000;
$config['maxforgottenpasswordemaildelay'] =  1500000;



$config['pdfdefaultfont'] = 'auto';              //Default font for the pdf Export

$config['alternatepdffontfile']=array(
    'ar'=>'dejavusans',// 'dejavusans' work but maybe more characters in aealarabiya or almohanad: but then need a dynamic font size too
    'be'=>'dejavusans',
    'bg'=>'dejavusans',
    'zh-Hans'=>'cid0cs',
    'zh-Hant-HK'=>'cid0ct',
    'zh-Hant-TW'=>'cid0ct',
    'cs'=>'dejavusans',
    'cs-informal'=>'dejavusans',// This one not really tested: no translation for Yes/No or Gender
    'el'=>'dejavusans',
    'he'=>'freesans',
    'hi'=>'dejavusans',
    'hr'=>'dejavusans',
    'hu'=>'dejavusans',
    'ja'=>'cid0jp',
    'ko'=>'cid0kr',
    'lv'=>'dejavusans',
    'lt'=>'dejavusans',
    'mk'=>'dejavusans',
    'mt'=>'dejavusans',
    'fa'=>'dejavusans',
    'pl'=>'dejavusans',
    'pa'=>'freesans',
    'ro'=>'dejavusans',
    'ru'=>'dejavusans',
    'sr'=>'dejavusans',
);
/**
*  $notsupportlanguages - array of language where no font was found for PDF
*  Seems not used actually
*/
$config['notsupportlanguages'] = array(
    'am',
    'si',
    'th',
    );
$config['pdffontsize']    = 9;                       
$config['pdforientation'] = 'P';                     
$config['pdfshowheader'] = 'N';           
$config['pdfheadertitle'] = 'UTAFITI';           
$config['pdfheaderstring'] = '';          
$config['bPdfQuestionFill'] = '1';  	   
$config['bPdfQuestionBold'] = '0';		  
$config['bPdfQuestionBorder'] = '1'; 	  // Border in questions. Accepts 0:no border, 1:border
$config['bPdfResponseBorder'] = '1';	  // Border in responses. Accepts 0:no border, 1:border


$config['quexmlshowprintablehelp'] = false;

$config['minlengthshortimplode'] = 20; 
$config['maxstringlengthshortimplode'] = 100; // short_implode: Max length of returned string


$config['chartfontfile']='auto';

$config['alternatechartfontfile']=array(
    'hi'=>'FreeSans.ttf',
    'ja'=> 'migmix-1p-regular.ttf',
    'ko'=>'UnBatang.ttf',
    'si'=>'FreeSans.ttf',
    'th'=>'TlwgTypist.ttf',
    'zh-Hans'=>'fireflysung.ttf',
    'zh-Hant-HK'=>'fireflysung.ttf',
    'zh-Hant-TW'=>'fireflysung.ttf',
);

/**
*  $chartfontsize - set the size of the font to created the charts in statistics
*/
$config['chartfontsize'] =10;



$config['updatecheckperiod']=0;


/**
* @var $showxquestions string allows you to control whether or not
* {THEREAREXQUESTIONS} is displayed (if it is included in a template)
*	hide = always hide {THEREAREXQUESTIONS}
*	show = always show {THEREAREXQUESTIONS}
*	choose = allow survey admins to choose
*/
$config['showxquestions'] = 'choose';



$config['showgroupinfo'] = 'choose';


$config['showqnumcode'] = 'choose';




$config['force_ssl'] = 'neither'; // DO not turn on unless you are sure your server supports SSL/HTTPS



$config['ssl_emergency_override'] = false;


$config['x_frame_options'] = 'allow';



$config['ipInfoDbAPIKey'] = '';

// Google Maps API key. http://code.google.com/apis/maps/signup.html
// To have questions that require google Maps!

$config['googleMapsAPIKey'] = '';

/**
* GeoNames username for API. http://www.geonames.org/export/web-services.html
*/
$config['GeoNamesUsername'] = 'Utafiti';


// Google Translate API key:  https://code.google.com/apis/language/translate/v2/getting_started.html
$googletranslateapikey = '';

/**
 * characterset (string)
 * Default character set for file import/export
 */
$config['characterset'] = 'auto';

/**
* This variable defines the total space available to the file upload question across all surveys. If set to 0 then no limit applies.
*
* @var $config['iFileUploadTotalSpaceMB']  Integer number to determine the available space in MB - Default: 0
*
*/
$config['iFileUploadTotalSpaceMB']= 0;


// defines if the CKeditor toolbar should be opened by default
$config['ckeditexpandtoolbar']     = true;


$config['restrictToLanguages'] = '';


$config['RPCInterface'] = 'off';

/**
* This parameter sets the default session expiration time in seconds
* Default is 2 hours
* @var integer
*/
$config['iSessionExpirationTime'] = 7200;

/**
* This parameter can be used to set some question not selectable in LimeReplacementFiels
* Default is an empty array, leave it for new question modules system
* @var array
*/
$config['InsertansUnsupportedtypes'] = array();


$config['proxy_host_name'] = '';
$config['proxy_host_port'] = 80;




if(!isset($argv[0]) && Yii::app()!=null)
{
    $config['publicurl'] = Yii::app()->baseUrl . '/';                          // The public website location (url) of the public survey script
}
else
{
    $config['publicurl'] =  '/';
}

$config['homeurl']                 = $config['publicurl'].'admin';          // The website location (url) of the admin scripts
$config['tempurl']                 = $config['publicurl'].'tmp';
$config['imageurl']                = $config['publicurl'].'images';         // Location of button bar files for admin script
$config['uploadurl']               = $config['publicurl'].'upload';
$config['standardtemplaterooturl'] = $config['publicurl'].'templates';      // Location of the standard templates
$config['adminscripts']            = $config['publicurl'].'scripts/admin/';
$config['generalscripts']          = $config['publicurl'].'scripts/';
$config['third_party']                 = $config['publicurl'].'third_party/';

$config['styleurl']                = $config['publicurl'].'styles/';


$config['publicstyleurl']          = $config['publicurl'].'styles-public/';
$config['sCKEditorURL']            = $config['third_party'].'ckeditor';
$config['usertemplaterooturl']     = $config['uploadurl'].'/templates';     // Location of the user templates

$config['adminimageurl']           = $config['styleurl'].$config['admintheme'].'/images/';         // Location of button bar files for admin script




$config['adminstyleurl']           = $config['styleurl'].$config['admintheme'].'/';         // Location of button bar files for admin script


$config['publicdir']               = $config['rootdir'];                                   // The directory path of the public scripts
$config['homedir']                 = $config['rootdir'];       // The directory path of the admin scripts
$config['tempdir']                 = $config['rootdir'].DIRECTORY_SEPARATOR."tmp";         // The directory path where LimeSurvey can store temporary files
$config['imagedir']                = $config['rootdir'].DIRECTORY_SEPARATOR."images";      // The directory path of the image directory
$config['uploaddir']               = $config['rootdir'].DIRECTORY_SEPARATOR."upload";
$config['standardtemplaterootdir'] = $config['rootdir'].DIRECTORY_SEPARATOR."templates";   // The directory path of the standard templates
$config['usertemplaterootdir']     = $config['uploaddir'].DIRECTORY_SEPARATOR."templates"; // The directory path of the user templates
$config['styledir']                = $config['rootdir'].DIRECTORY_SEPARATOR.'styles';
$config['questiontypedir']         = $config['rootdir'].DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.'questionTypes';


// Use alias notation, we should move to this format everywhere.
$config['plugindir']               = 'webroot.plugins';

// (javascript) Fix automatically the value entered in numeric question type : 1: remove all non numeric caracters; 0 : leave all caracters
$config['bFixNumAuto']             = 1;
// (javascript) Send real value entered when using Numeric question type in Expression Manager : 0 : {NUMERIC} with bad caracters send '', 1 : {NUMERIC} send all caracters entered
$config['bNumRealValue']             = 0;

// Home page default Settings
$config['show_logo'] = 'show';
$config['show_last_survey_and_question'] = 'show';
$config['show_survey_list_search'] = 'show';
$config['boxes_by_row'] = '3';
$config['boxes_offset'] = '3';

// Bounce settings
$config['bounceaccounthost']='';
$config['bounceaccounttype']='off';
$config['bounceencryption']='off';
$config['bounceaccountuser']='';

// Question selector
$config['defaultquestionselectormode']='default';

// Template editor mode
$config['defaulttemplateeditormode']='default';

// Side Menu behaviout
$config['sideMenuBehaviour']='adaptive';

// Hide update key
$config['hide_update_key']=false;

return $config;
//settings deleted
