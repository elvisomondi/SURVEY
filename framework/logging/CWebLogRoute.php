<?php
/**
 * CWebLogRoute shows the log content in Web page.
 */
class CWebLogRoute extends CLogRoute
{
	/**
	 * @var boolean whether the log should be displayed in FireBug instead of browser window. 
	 */
	public $showInFireBug=false;
	/**
	 * @var boolean whether the log should be ignored in FireBug for ajax calls.
	 */
	public $ignoreAjaxInFireBug=true;
	
	public $ignoreFlashInFireBug=true;
	
	public $collapsedInFireBug=false;

	/**
	 * Displays the log messages
	 */
	public function processLogs($logs)
	{
		$this->render('log',$logs);
	}

	protected function render($view,$data)
	{
		$app=Yii::app();
		$isAjax=$app->getRequest()->getIsAjaxRequest();
		$isFlash=$app->getRequest()->getIsFlashRequest();

		if($this->showInFireBug)
		{
			// do not output anything for ajax and/or flash requests if needed
			if($isAjax && $this->ignoreAjaxInFireBug || $isFlash && $this->ignoreFlashInFireBug)
				return;
			$view.='-firebug';
			if(($userAgent=$app->getRequest()->getUserAgent())!==null && preg_match('/msie [5-9]/i',$userAgent))
			{
				echo '<script type="text/javascript">';
				echo file_get_contents(dirname(__FILE__).'/../vendors/console-normalizer/normalizeconsole.min.js');
				echo "</script>\n";
			}
		}
		elseif(!($app instanceof CWebApplication) || $isAjax || $isFlash)
			return;

		$viewFile=YII_PATH.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$view.'.php';
		include($app->findLocalizedFile($viewFile,'en'));
	}
}