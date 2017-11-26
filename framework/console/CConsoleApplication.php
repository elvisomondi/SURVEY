<?php

/**
 * CConsoleApplication represents a console application.
 */
class CConsoleApplication extends CApplication
{
	public $commandMap=array();

	private $_commandPath;
	private $_runner;

	/**
	 * Initializes the application by creating the command runner.
	 */
	protected function init()
	{
		parent::init();
		if(empty($_SERVER['argv']))
			die('This script must be run from the command line.');
		$this->_runner=$this->createCommandRunner();
		$this->_runner->commands=$this->commandMap;
		$this->_runner->addCommands($this->getCommandPath());
	}


	public function processRequest()
	{
		$exitCode=$this->_runner->run($_SERVER['argv']);
		if(is_int($exitCode))
			$this->end($exitCode);
	}

	/**
	 * Creates the command runner instance.
	 * @return CConsoleCommandRunner the command runner
	 */
	protected function createCommandRunner()
	{
		return new CConsoleCommandRunner;
	}

	/**
	 * Displays the captured PHP error.
	 */
	public function displayError($code,$message,$file,$line)
	{
		echo "PHP Error[$code]: $message\n";
		echo "    in file $file at line $line\n";
		$trace=debug_backtrace();
		// skip the first 4 stacks as they do not tell the error position
		if(count($trace)>4)
			$trace=array_slice($trace,4);
		foreach($trace as $i=>$t)
		{
			if(!isset($t['file']))
				$t['file']='unknown';
			if(!isset($t['line']))
				$t['line']=0;
			if(!isset($t['function']))
				$t['function']='unknown';
			echo "#$i {$t['file']}({$t['line']}): ";
			if(isset($t['object']) && is_object($t['object']))
				echo get_class($t['object']).'->';
			echo "{$t['function']}()\n";
		}
	}

	/**
	 * Displays the uncaught PHP exception.
	 */
	public function displayException($exception)
	{
		echo $exception;
	}

	/**
	 * @return string the directory that contains the command classes. Defaults to 'protected/commands'.
	 */
	public function getCommandPath()
	{
		$applicationCommandPath = $this->getBasePath().DIRECTORY_SEPARATOR.'commands';
		if($this->_commandPath===null && file_exists($applicationCommandPath))
			$this->setCommandPath($applicationCommandPath);
		return $this->_commandPath;
	}

	/**
	 * @param string $value the directory that contains the command classes.
	 * @throws CException if the directory is invalid
	 */
	public function setCommandPath($value)
	{
		if(($this->_commandPath=realpath($value))===false || !is_dir($this->_commandPath))
			throw new CException(Yii::t('yii','The command path "{path}" is not a valid directory.',
				array('{path}'=>$value)));
	}

	/**
	 * Returns the command runner.
	 * @return CConsoleCommandRunner the command runner.
	 */
	public function getCommandRunner()
	{
		return $this->_runner;
	}

	/**
	 * Returns the currently running command.
	 */
	public function getCommand()
	{
		return $this->getCommandRunner()->getCommand();
	}

	/**
	 * This is shortcut method for {@link CConsoleCommandRunner::setCommand()}.
	 */
	public function setCommand($value)
	{
		$this->getCommandRunner()->setCommand($value);
	}
}
