<?php
/**
 * CLogFilter preprocesses the logged messages before they are handled by a log route.
 */
class CLogFilter extends CComponent implements ILogFilter
{
	/** whether to prefix each log message with the current user session ID.
	 */
	public $prefixSession=false;
	/**
	 * @var boolean whether to prefix each log message with the current user
	 */
	public $prefixUser=false;
	/**
	 * @var boolean whether to log the current user name and ID.
	 */
	public $logUser=true;
	/**
	 * @var array list of the PHP predefined variables that should be logged.
	 */
	public $logVars=array('_GET','_POST','_FILES','_COOKIE','_SESSION','_SERVER');

	public $dumper='var_export';


	
	public function filter(&$logs)
	{
		if (!empty($logs))
		{
			if(($message=$this->getContext())!=='')
				array_unshift($logs,array($message,CLogger::LEVEL_INFO,'application',YII_BEGIN_TIME));
			$this->format($logs);
		}
		return $logs;
	}

	/**
	 * Formats the log messages.
	 */
	protected function format(&$logs)
	{
		$prefix='';
		if($this->prefixSession && ($id=session_id())!=='')
			$prefix.="[$id]";
		if($this->prefixUser && ($user=Yii::app()->getComponent('user',false))!==null)
			$prefix.='['.$user->getName().']['.$user->getId().']';
		if($prefix!=='')
		{
			foreach($logs as &$log)
				$log[0]=$prefix.' '.$log[0];
		}
	}

	/**
	 * Generates the context information to be logged
	 */
	protected function getContext()
	{
		$context=array();
		if($this->logUser && ($user=Yii::app()->getComponent('user',false))!==null)
			$context[]='User: '.$user->getName().' (ID: '.$user->getId().')';

		if($this->dumper==='var_export' || $this->dumper==='print_r')
		{
			foreach($this->logVars as $name)
				if(($value=$this->getGlobalsValue($name))!==null)
					$context[]="\${$name}=".call_user_func($this->dumper,$value,true);
		}
		else
		{
			foreach($this->logVars as $name)
				if(($value=$this->getGlobalsValue($name))!==null)
					$context[]="\${$name}=".call_user_func($this->dumper,$value);
		}

		return implode("\n\n",$context);
	}

	/**
	 * @param string[] $path
	 * @return string|null
	 */
	private function getGlobalsValue(&$path)
	{
		if(is_scalar($path))
			return !empty($GLOBALS[$path]) ? $GLOBALS[$path] : null;
		$pathAux=$path;
		$parts=array();
		$value=$GLOBALS;
		do
		{
			$value=$value[$parts[]=array_shift($pathAux)];
		}
		while(!empty($value) && !empty($pathAux) && !is_string($value));
		$path=implode('.',$parts);
		return $value;
	}
}
