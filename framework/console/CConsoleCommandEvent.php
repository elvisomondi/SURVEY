<?php

class CConsoleCommandEvent extends CEvent
{
	/**
	 * @var string the action name
	 */
	public $action;
	
	public $stopCommand=false;
	
	public $exitCode;

	
	public function __construct($sender=null,$params=null,$action=null,$exitCode=0){
		parent::__construct($sender,$params);
		$this->action=$action;
		$this->exitCode=$exitCode;
	}
}