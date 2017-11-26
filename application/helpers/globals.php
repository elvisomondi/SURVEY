<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    
    function App()
    {
        return Yii::app();
    }


    function traceVar($variable, $depth = 10) {
        $msg = CVarDumper::dumpAsString($variable, $depth, false);
        $fullTrace = debug_backtrace();
        $trace=array_shift($fullTrace);
        if(isset($trace['file'],$trace['line']) && strpos($trace['file'],YII_PATH)!==0)
        {
            $msg = $trace['file'].' ('.$trace['line']."):\n" . $msg;
        }
        Yii::trace($msg, 'vardump');
    }
    
?>