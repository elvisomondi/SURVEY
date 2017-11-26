<?php

class CRedisCache extends CCache
{
	/**
	 * @var string hostname to use for connecting to the redis server..
	 */
	public $hostname='localhost';
	/**
	 * @var int the port to use for connecting to the redis server..
	 */
	public $port=6379;
	/**
	 * @var string the password to use to authenticate with the redis server. If not set, no AUTH command will be sent.
	 */
	public $password;
	/**
	 * @var int the redis database to use. This is an integer value starting from 0. Defaults to 0.
	 */
	public $database=0;
	
	public $options=STREAM_CLIENT_CONNECT;
	
	public $timeout=null;
	/**
	 * @var resource redis socket connection
	 */
	private $_socket;

	/**
	 * Establishes a connection to the redis server.
	 * It does nothing if the connection has already been established.
	 * @throws CException if connecting fails
	 */
	protected function connect()
	{
		$this->_socket=@stream_socket_client(
			$this->hostname.':'.$this->port,
			$errorNumber,
			$errorDescription,
			$this->timeout ? $this->timeout : ini_get("default_socket_timeout"),
			$this->options
		);
		if ($this->_socket)
		{
			if($this->password!==null)
				$this->executeCommand('AUTH',array($this->password));
			$this->executeCommand('SELECT',array($this->database));
		}
		else
			throw new CException('Failed to connect to redis: '.$errorDescription,(int)$errorNumber);
	}

	/**
	 * Executes a redis command.
	 *  {@link http://redis.io/commands}.
	 
	 */
	public function executeCommand($name,$params=array())
	{
		if($this->_socket===null)
			$this->connect();

		array_unshift($params,$name);
		$command='*'.count($params)."\r\n";
		foreach($params as $arg)
			$command.='$'.$this->byteLength($arg)."\r\n".$arg."\r\n";

		fwrite($this->_socket,$command);

		return $this->parseResponse(implode(' ',$params));
	}

	/**
	 * Reads the result from socket and parses it
	 * @return array|bool|null|string
	 * @throws CException socket or data problems
	 */
	private function parseResponse()
	{
		if(($line=fgets($this->_socket))===false)
			throw new CException('Failed reading data from redis connection socket.');
		$type=$line[0];
		$line=substr($line,1,-2);
		switch($type)
		{
			case '+': // Status reply
				return true;
			case '-': // Error reply
				throw new CException('Redis error: '.$line);
			case ':': // Integer reply
				// no cast to int as it is in the range of a signed 64 bit integer
				return $line;
			case '$': // Bulk replies
				if($line=='-1')
					return null;
				$length=$line+2;
				$data='';
				while($length>0)
				{
					if(($block=fread($this->_socket,$length))===false)
						throw new CException('Failed reading data from redis connection socket.');
					$data.=$block;
					$length-=$this->byteLength($block);
				}
				return substr($data,0,-2);
			case '*': // Multi-bulk replies
				$count=(int)$line;
				$data=array();
				for($i=0;$i<$count;$i++)
					$data[]=$this->parseResponse();
				return $data;
			default:
				throw new CException('Unable to parse data received from redis.');
		}
	}

	/**
	 * Counting amount of bytes in a string.
	 *
	 * @param string $str
	 * @return int
	 */
	private function byteLength($str)
	{
		return function_exists('mb_strlen') ? mb_strlen($str, '8bit') : strlen($str);
	}

	
	protected function getValue($key)
	{
		return $this->executeCommand('GET',array($key));
	}

	
	protected function getValues($keys)
	{
		$response=$this->executeCommand('MGET',$keys);
		$result=array();
		$i=0;
		foreach($keys as $key)
			$result[$key]=$response[$i++];
		return $result;
	}

	
	protected function setValue($key,$value,$expire)
	{
		if ($expire==0)
			return (bool)$this->executeCommand('SET',array($key,$value));
		return (bool)$this->executeCommand('SETEX',array($key,$expire,$value));
	}


	protected function addValue($key,$value,$expire)
	{
		if ($expire == 0)
			return (bool)$this->executeCommand('SETNX',array($key,$value));

		if($this->executeCommand('SETNX',array($key,$value)))
		{
			$this->executeCommand('EXPIRE',array($key,$expire));
			return true;
		}
		else
			return false;
	}

	
	protected function deleteValue($key)
	{
		return (bool)$this->executeCommand('DEL',array($key));
	}

	
	protected function flushValues()
	{
		return $this->executeCommand('FLUSHDB');
	}
}
