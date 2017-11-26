<?php

class CViewAction extends CAction
{
	/**
	 * @var string the name of the GET parameter that contains the requested view name. Defaults to 'view'.
	 */
	public $viewParam='view';

	public $defaultView='index';
	
	public $view;
	
	public $basePath='pages';
	
	public $layout;
	/**
	 * @var boolean whether the view should be rendered as PHP script or static text. Defaults to false.
	 */
	public $renderAsText=false;

	private $_viewPath;


	
	public function getRequestedView()
	{
		if($this->_viewPath===null)
		{
			if(!empty($_GET[$this->viewParam]) && is_string($_GET[$this->viewParam]))
				$this->_viewPath=$_GET[$this->viewParam];
			else
				$this->_viewPath=$this->defaultView;
		}
		return $this->_viewPath;
	}


	protected function resolveView($viewPath)
	{
		// start with a word char and have word chars, dots and dashes only
		if(preg_match('/^\w[\w\.\-]*$/',$viewPath))
		{
			$view=strtr($viewPath,'.','/');
			if(!empty($this->basePath))
				$view=$this->basePath.'/'.$view;
			if($this->getController()->getViewFile($view)!==false)
			{
				$this->view=$view;
				return;
			}
		}
		throw new CHttpException(404,Yii::t('yii','The requested view "{name}" was not found.',
			array('{name}'=>$viewPath)));
	}

	/**
	 * Runs the action.
	 * This method displays the view requested by the user.
	 * @throws CHttpException if the view is invalid
	 */
	public function run()
	{
		$this->resolveView($this->getRequestedView());
		$controller=$this->getController();
		if($this->layout!==null)
		{
			$layout=$controller->layout;
			$controller->layout=$this->layout;
		}

		$this->onBeforeRender($event=new CEvent($this));
		if(!$event->handled)
		{
			if($this->renderAsText)
			{
				$text=file_get_contents($controller->getViewFile($this->view));
				$controller->renderText($text);
			}
			else
				$controller->render($this->view);
			$this->onAfterRender(new CEvent($this));
		}

		if($this->layout!==null)
			$controller->layout=$layout;
	}


	public function onBeforeRender($event)
	{
		$this->raiseEvent('onBeforeRender',$event);
	}

	/**
	 * Raised right after the action invokes the render method.
	 * @param CEvent $event event parameter
	 */
	public function onAfterRender($event)
	{
		$this->raiseEvent('onAfterRender',$event);
	}
}