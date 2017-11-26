#!/usr/bin/php
<?php
 
  if (!isset($argv[0])) die();
  define('BASEPATH','.');
    /**
     * Load Psr4 autoloader, should be replaced by composer autoloader at some point.
     */
    require_once __DIR__ . '/../Psr4AutoloaderClass.php';
    $loader = new Psr4AutoloaderClass();
    $loader->register();
    $loader->addNamespace('ls\\pluginmanager', __DIR__ . '/../libraries/PluginManager');
    $loader->addNamespace('ls\\pluginmanager', __DIR__ . '/../libraries/PluginManager/Storage');
    require_once(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'yii.php');
    // Load configuration.
    $sCurrentDir=dirname(__FILE__);
    $settings=require (dirname($sCurrentDir).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config-defaults.php');
    $config=require (dirname($sCurrentDir).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'internal.php');
    $core = dirname($sCurrentDir) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR;
    if(isset($config['config'])){
      $settings=array_merge($settings,$config['config']);
    }
    unset ($config['defaultController']);
    unset ($config['config']);
    /* fix runtime path, unsure you can lauch function anywhere (if you use php /var/www/limesurvey/... : can be /root/ for config */
    if(!isset($config['runtimePath'])){
        $runtimePath=$settings['tempdir'].'/runtime';
        if(!is_dir($runtimePath) || !is_writable($runtimePath)){
            $runtimePath=str_replace($settings['rootdir'],dirname(dirname(dirname(__FILE__))),$runtimePath);
        }
        $config['runtimePath']=$runtimePath;
    }
    // fix for fcgi
    defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));

    defined('YII_DEBUG') or define('YII_DEBUG',true);



    if(isset($config))
    {
        require_once($core . 'ConsoleApplication.php');
        $app=Yii::createApplication('ConsoleApplication', $config);
        define('APPPATH', Yii::app()->getBasePath() . DIRECTORY_SEPARATOR);
        $app->commandRunner->addCommands(YII_PATH.'/cli/commands');
        $env=@getenv('YII_CONSOLE_COMMANDS');
        if(!empty($env)){
            $app->commandRunner->addCommands($env);
        }
    }
    $app->run();
?>
