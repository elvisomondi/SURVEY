<?php

class LSHttpRequest extends CHttpRequest
{

    private $_pathInfo;
    
    public $noCsrfValidationRoutes = array();

    public function getUrlReferrer($sAlternativeUrl=null)
    {

       $referrer = parent::getUrlReferrer();
       $baseReferrer    = str_replace(Yii::app()->getBaseUrl(true), "", $referrer);
       $baseRequestUri  = str_replace(Yii::app()->getBaseUrl(), "", Yii::app()->request->requestUri);
       $referrer = ($baseReferrer != $baseRequestUri)?$referrer:null;
        //Use alternative url if the $referrer is still available in the checkLoopInNavigationStack
        if( ($this->checkLoopInNavigationStack($referrer)) || (is_null($referrer)) )
        {
            // Checks if the alternative url should be used
            if(isset($sAlternativeUrl))
            {
                $referrer = $sAlternativeUrl;
            }
            else 
            {
               return App()->createUrl('admin/index');
            }
       }
       return $referrer;
    }

    /**
    * Method to update the LimeSurvey Navigation Stack to prevent looping
    */
    public function updateNavigationStack()
    {
        $referrer = parent::getUrlReferrer();
        $navStack = App()->session['LSNAVSTACK'];

        if(!is_array($navStack))
        {
            $navStack = array();
        }

        array_unshift($navStack,$referrer);

        if(count($navStack)>5)
        {
            array_pop($navStack);
        }
        App()->session['LSNAVSTACK'] = $navStack;
    }

    /**
    * Method to check if an url is part of the stack
    * Returns true, when an url is saved in the stack
    * @param $referrerURL The URL that is checked against the stack 
    */
    protected function checkLoopInNavigationStack($referrerURL)
    {
        $navStack = App()->session['LSNAVSTACK'];
        foreach($navStack as $url)
        {
            $refEqualsUrl = ($referrerURL == $url);
              if ($refEqualsUrl)
              {
                  return true;
              }
        }
        return false;  
    }

    protected function normalizeRequest(){
        parent::normalizeRequest();

        if(!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST') return;

        $route = Yii::app()->getUrlManager()->parseUrl($this);
        if($this->enableCsrfValidation){
            foreach($this->noCsrfValidationRoutes as $cr){
                if(preg_match('#'.$cr.'#', $route)){
                    Yii::app()->detachEventHandler('onBeginRequest',
                        array($this,'validateCsrfToken'));
                    Yii::trace('Route "'.$route.' passed without CSRF validation');
                    break; // found first route and break
                }
            }
        }
    }


    public function getPathInfo()
    {
        if($this->_pathInfo===null)
        {
            $pathInfo=$this->getRequestUri();

            if(($pos=strpos($pathInfo,'?'))!==false)
                $pathInfo=substr($pathInfo,0,$pos);

            $pathInfo=$this->decodePathInfo($pathInfo);

            $scriptUrl=$this->getScriptUrl();
            $baseUrl=$this->getBaseUrl();
            if(strpos($pathInfo,$scriptUrl)===0)
                $pathInfo=substr($pathInfo,strlen($scriptUrl));
            elseif($baseUrl==='' || strpos($pathInfo,$baseUrl)===0)
                $pathInfo=substr($pathInfo,strlen($baseUrl));
            elseif(strpos($_SERVER['PHP_SELF'],$scriptUrl)===0)
                $pathInfo=substr($_SERVER['PHP_SELF'],strlen($scriptUrl));
            else
                throw new CException(Yii::t('yii','CHttpRequest is unable to determine the path info of the request.'));

            if($pathInfo==='/')
                $pathInfo='';
            elseif(!empty($pathInfo) && $pathInfo[0]==='/')
                $pathInfo=substr($pathInfo,1);

            if(($posEnd=strlen($pathInfo)-1)>0 && $pathInfo[$posEnd]==='/')
                $pathInfo=substr($pathInfo,0,$posEnd);

            $this->_pathInfo=$pathInfo;
        }
        return $this->_pathInfo;
    }

}