<?php
/**
 * CEmailLogRoute sends selected log messages to email addresses
 */
class CEmailLogRoute extends CLogRoute
{
	
	public $utf8=false;
	/**
	 * @var array list of destination email addresses.
	 */
	private $_email=array();
	/**
	 * @var string email subject
	 */
	private $_subject;
	/**
	 * @var string email sent from address
	 */
	private $_from;
	/**
	 * @var array list of additional headers to use when sending an email.
	 */
	private $_headers=array();

	/**
	 * Sends log messages to specified email addresses.
	 */
	protected function processLogs($logs)
	{
		$message='';
		foreach($logs as $log)
			$message.=$this->formatLogMessage($log[0],$log[1],$log[2],$log[3]);
		$message=wordwrap($message,70);
		$subject=$this->getSubject();
		if($subject===null)
			$subject=Yii::t('yii','Application Log');
		foreach($this->getEmails() as $email)
			$this->sendEmail($email,$subject,$message);
	}

	
	protected function sendEmail($email,$subject,$message)
	{
		$headers=$this->getHeaders();
		if($this->utf8)
		{
			$headers[]="MIME-Version: 1.0";
			$headers[]="Content-Type: text/plain; charset=UTF-8";
			$subject='=?UTF-8?B?'.base64_encode($subject).'?=';
		}
		if(($from=$this->getSentFrom())!==null)
		{
			$matches=array();
			preg_match_all('/([^<]*)<([^>]*)>/iu',$from,$matches);
			if(isset($matches[1][0],$matches[2][0]))
			{
				$name=$this->utf8 ? '=?UTF-8?B?'.base64_encode(trim($matches[1][0])).'?=' : trim($matches[1][0]);
				$from=trim($matches[2][0]);
				$headers[]="From: {$name} <{$from}>";
			}
			else
				$headers[]="From: {$from}";
			$headers[]="Reply-To: {$from}";
		}
		mail($email,$subject,$message,implode("\r\n",$headers));
	}

	/**
	 * @return array list of destination email addresses
	 */
	public function getEmails()
	{
		return $this->_email;
	}

	
	public function setEmails($value)
	{
		if(is_array($value))
			$this->_email=$value;
		else
			$this->_email=preg_split('/[\s,]+/',$value,-1,PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * @return string email subject. Defaults to CEmailLogRoute::DEFAULT_SUBJECT
	 */
	public function getSubject()
	{
		return $this->_subject;
	}

	/**
	 * @param string $value email subject.
	 */
	public function setSubject($value)
	{
		$this->_subject=$value;
	}

	/**
	 * @return string send from address of the email
	 */
	public function getSentFrom()
	{
		return $this->_from;
	}

	/**
	 * @param string $value send from address of the email
	 */
	public function setSentFrom($value)
	{
		$this->_from=$value;
	}

	
	public function getHeaders()
	{
		return $this->_headers;
	}

	
	public function setHeaders($value)
	{
		if (is_array($value))
			$this->_headers=$value;
		else
			$this->_headers=preg_split('/\r\n|\n/',$value,-1,PREG_SPLIT_NO_EMPTY);
	}
}