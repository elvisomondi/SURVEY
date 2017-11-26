<?php
    
    class PluginCommand extends CConsoleCommand
    {
        public $connection;

        /**
         * Call for cron action
         * @interval int $interval Minutes for interval
         * @return void
         */
        public function actionCron($interval=null)
        {
            $pm = \Yii::app()->pluginManager;
            $event = new PluginEvent('cron');
            $event->set('interval', $interval);
            $pm->dispatchEvent($event);
        }

        
        public function actionIndex($target, $function=null,$option=null)
        {
            $pm = \Yii::app()->pluginManager;
            $event = new PluginEvent('direct');
            $event->set('target', $target);
            $event->set('function', $function);
            $event->set('option', $option);
            $pm->dispatchEvent($event);
        }

    }

?>
