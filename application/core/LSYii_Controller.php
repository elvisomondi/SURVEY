<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


abstract class LSYii_Controller extends CController
{
    
    public $navData = array();
   
    public function __construct($id, $module = null)
    {
        parent::__construct($id, $module);
        $this->_checkInstallation();

        Yii::app()->session->init();
        $this->loadLibrary('LS.LS');
        // This will setConfig from database
        $this->loadHelper('globalsettings');
        $this->loadHelper('common');
        $this->loadHelper('expressions.em_manager');
        $this->loadHelper('replacements');
        $this->_init();
    }

   
    protected function _checkInstallation()
    {
        $file_name = Yii::app()->getConfig('rootdir').'/application/config/config.php';
        if (!file_exists($file_name))
        {
            $this->redirect(array('/installer'));
        }
    }

    /**
     * Loads a helper
     *
     * @access public
     * @param string $helper
     * @return void
     */
    public function loadHelper($helper)
    {
        Yii::app()->loadHelper($helper);
    }

    /**
     * Loads a library
     *
     * @access public
     * @param string $library
     * @return void
     */
    public function loadLibrary($library)
    {
        Yii::app()->loadLibrary($library);
    }

    protected function _init()
    {
        // Check for most necessary requirements
        // Now check for PHP & db version
        // Do not localize/translate this!

        $dieoutput='';
        if (version_compare(PHP_VERSION, '5.3.3', '<'))
            $dieoutput .= 'This script can only be run on PHP version 5.3.3 or later! Your version: '.PHP_VERSION.'<br />';

        if (!function_exists('mb_convert_encoding'))
            $dieoutput .= "This script needs the PHP Multibyte String Functions library installed: See <a href='http://manual.limesurvey.org/wiki/Installation_FAQ'>FAQ</a> and <a href='http://de.php.net/manual/en/ref.mbstring.php'>PHP documentation</a><br />";

        if ($dieoutput != '')
            throw new CException($dieoutput);

           if (ini_get("max_execution_time") < 1200) @set_time_limit(1200); // Maximum execution time - works only if safe_mode is off
        if (ini_get('memory_limit')!=-1 && convertPHPSizeToBytes(ini_get("memory_limit"))<convertPHPSizeToBytes(Yii::app()->getConfig('memory_limit').'M'))
        {
            @ini_set("memory_limit",Yii::app()->getConfig('memory_limit').'M'); // Set Memory Limit for big surveys
        }

        // The following function (when called) includes FireBug Lite if true
        defined('FIREBUG') or define('FIREBUG' , Yii::app()->getConfig('use_firebug_lite'));

        //Every 50th time clean up the temp directory of old files (older than 1 day)
        //depending on the load the  probability might be set higher or lower
        if (rand(1,50)==1)
        {
            cleanTempDirectory();
        }

        //GlobalSettings Helper
        Yii::import("application.helpers.globalsettings");

        enforceSSLMode();// This really should be at the top but for it to utilise getGlobalSetting() it has to be here

        if (Yii::app()->getConfig('debug')==1) {//For debug purposes - switch on in config.php
            @ini_set("display_errors", 1);
            error_reporting(E_ALL);
        }
        elseif (Yii::app()->getConfig('debug')==2) {//For debug purposes - switch on in config.php
            @ini_set("display_errors", 1);
            error_reporting(E_ALL | E_STRICT);
        }
        else {
            @ini_set("display_errors", 0);
            error_reporting(0);
        }

        //SET LOCAL TIME
        $timeadjust = Yii::app()->getConfig("timeadjust");
        if (substr($timeadjust,0,1)!='-' && substr($timeadjust,0,1)!='+') {$timeadjust='+'.$timeadjust;}
        if (strpos($timeadjust,'hours')===false && strpos($timeadjust,'minutes')===false && strpos($timeadjust,'days')===false)
        {
            Yii::app()->setConfig("timeadjust",$timeadjust.' hours');
        }

        //Yii::app()->setConfig('adminimageurl', Yii::app()->getConfig('styleurl').Yii::app()->getConfig('admintheme').'/images/');
        //Yii::app()->setConfig('adminstyleurl', Yii::app()->getConfig('styleurl').Yii::app()->getConfig('admintheme').'/');
    }

   
    public function createAbsoluteUrl($route,$params=array(),$schema='',$ampersand='&')
    {
        $sPublicUrl=Yii::app()->getConfig("publicurl");
        // Control if public url are really public : need scheme and host
        // If yes: use it
        $aPublicUrl=parse_url($sPublicUrl);
        if(isset($aPublicUrl['scheme']) && isset($aPublicUrl['host']))
        {
            $url=parent::createAbsoluteUrl($route,$params,$schema,$ampersand);
            $sActualBaseUrl=Yii::app()->getBaseUrl(true);
            if (substr($url, 0, strlen($sActualBaseUrl)) == $sActualBaseUrl) {
                $url = substr($url, strlen($sActualBaseUrl));
            }
            return trim($sPublicUrl,"/").$url;
        }
        else
            return parent::createAbsoluteUrl($route,$params,$schema,$ampersand);
    }

    /**
     * Loads page states from a hidden input.
     * @return array the loaded page states
     */
    protected function loadPageStates()
    {
        return array();
    }
}