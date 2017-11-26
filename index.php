<?php


    $system_path = "framework";


 
    $application_folder = dirname(__FILE__) . "/application";


    if (realpath($system_path) !== FALSE)
    {
        $system_path = realpath($system_path).'/';
    }

    // ensure there's a trailing slash
    $system_path = rtrim($system_path, '/').'/';

    // Is the system path correct?
    if (!is_dir($system_path))
    {
        exit("Your system folder path does not appear to be set correctly. Please open the following file and correct this: ".pathinfo(__FILE__, PATHINFO_BASENAME));
    }



    // The name of THIS file
    define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

    define('ROOT', dirname(__FILE__));

    // The PHP file extension
    define('EXT', '.php');

    // Path to the system folder
    define('BASEPATH', str_replace("\\", "/", $system_path));

    // Path to the front controller (this file)
    define('FCPATH', str_replace(SELF, '', __FILE__));

    // Name of the "system folder"
    define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));


    // The path to the "application" folder
    if (is_dir($application_folder))
    {
        define('APPPATH', $application_folder.'/');
    }
    else
    {
        if (!is_dir(BASEPATH . $application_folder . '/'))
        {
            exit("Your application folder path does not appear to be set correctly. Please open the following file and correct this: ".SELF);
        }

        define('APPPATH', BASEPATH . $application_folder . '/');
    }
    if (file_exists(APPPATH.'config'.DIRECTORY_SEPARATOR.'config.php'))
    {
        $aSettings= include(APPPATH.'config'.DIRECTORY_SEPARATOR.'config.php');
    }
    else
    {
        $aSettings=array();
    }
    // Set debug : if not set : set to default from PHP 5.3
    if (isset($aSettings['config']['debug']))
    {
        if ($aSettings['config']['debug']>0)
        {
            define('YII_DEBUG', true);
        if($aSettings['config']['debug']>1)
        error_reporting(E_ALL);
        else
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
        }
        else
        {
            define('YII_DEBUG', false);
            error_reporting(0);
        }
    }
    else
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);// Not needed if user don't remove his 'debug'=>0, for application/config/config.php (Installation is OK with E_ALL)
    }

    if (version_compare(PHP_VERSION, '5.3.3', '<'))
        die ('This script can only be run on PHP version 5.3.3 or later! Your version: '.PHP_VERSION.'<br />');


/**
 * Load Psr4 autoloader, should be replaced by composer autoloader at some point.
 */
    require_once 'application/Psr4AutoloaderClass.php';
    $loader = new Psr4AutoloaderClass();
    $loader->register();
    $loader->addNamespace('ls\\pluginmanager', __DIR__ . '/application/libraries/PluginManager');
    $loader->addNamespace('ls\\pluginmanager', __DIR__ . '/application/libraries/PluginManager/Storage');
    $loader->addNamespace('ls\\menu', __DIR__ . '/application/libraries/MenuObjects');
    $loader->addNamespace('ls\\helpers', __DIR__ . '/application/helpers');


require_once BASEPATH . 'yii' . EXT;
require_once APPPATH . 'core/LSYii_Application' . EXT;

$config = require_once(APPPATH . 'config/internal' . EXT);

if (!file_exists(APPPATH . 'config/config' . EXT)) {
    // If Yii can not start due to unwritable runtimePath, present an error
    $sDefaultRuntimePath = dirname(__FILE__).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'runtime';
    if (!is_dir($sDefaultRuntimePath) || !is_writable($sDefaultRuntimePath)) {
        // @@TODO: present html page styled like the installer
        die (sprintf('%s should be writable by the webserver (766 or 776).', $sDefaultRuntimePath));
    }
}

Yii::$enableIncludePath = false;
Yii::createApplication('LSYii_Application', $config)->run();

/* End of file index.php */
/* Location: ./index.php */
