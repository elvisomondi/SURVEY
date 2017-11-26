<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class LSYii_ClientScript extends CClientScript {

    public function getCssFiles()
    {
        return $this->cssFiles;
    }

    public function getCoreScripts()
    {
        return $this->coreScripts;
    }

    public function unregisterPackage($name)
    {
        if(!empty($this->coreScripts[$name])){
            unset($this->coreScripts[$name]);
        }
    }
}
