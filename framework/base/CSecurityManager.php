<?php


/**
 * CSecurityManager provides private keys, hashing and encryption functions.
 */
class CSecurityManager extends CApplicationComponent
{
	const STATE_VALIDATION_KEY='Yii.CSecurityManager.validationkey';
	const STATE_ENCRYPTION_KEY='Yii.CSecurityManager.encryptionkey';

	/**
	 * @var array known minimum lengths per encryption algorithm
	 */
	protected static $encryptionKeyMinimumLengths=array(
		'blowfish'=>4,
		'arcfour'=>5,
		'rc2'=>5,
	);

	/**
	 * @var boolean if encryption key should be validated
	 * @deprecated
	 */
	public $validateEncryptionKey=true;

	/**
	 * @var string the name of the hashing algorithm to be used by {@link computeHMAC}.
	 */
	public $hashAlgorithm='sha1';
	/**
	 * @var mixed the name of the crypt algorithm to be used by {@link encrypt} and {@link decrypt}.
	 */
	public $cryptAlgorithm='rijndael-128';

	private $_validationKey;
	private $_encryptionKey;
	private $_mbstring;


	public function init()
	{
		parent::init();
		$this->_mbstring=extension_loaded('mbstring');
	}

	/**
	 * @return string a randomly generated private key.
	 * @deprecated in favor of {@link generateRandomString()} since 1.1.14. Never use this method.
	 */
	protected function generateRandomKey()
	{
		return $this->generateRandomString(32);
	}

	/**
	 * @return string the private key used to generate HMAC.
	 * If the key is not explicitly set, a random one is generated and returned.
	 * @throws CException in case random string cannot be generated.
	 */
	public function getValidationKey()
	{
		if($this->_validationKey!==null)
			return $this->_validationKey;
		else
		{
			if(($key=Yii::app()->getGlobalState(self::STATE_VALIDATION_KEY))!==null)
				$this->setValidationKey($key);
			else
			{
				if(($key=$this->generateRandomString(32,true))===false)
					if(($key=$this->generateRandomString(32,false))===false)
						throw new CException(Yii::t('yii',
							'CSecurityManager::generateRandomString() cannot generate random string in the current environment.'));
				$this->setValidationKey($key);
				Yii::app()->setGlobalState(self::STATE_VALIDATION_KEY,$key);
			}
			return $this->_validationKey;
		}
	}

	/**
	 * @param string $value the key used to generate HMAC
	 * @throws CException if the key is empty
	 */
	public function setValidationKey($value)
	{
		if(!empty($value))
			$this->_validationKey=$value;
		else
			throw new CException(Yii::t('yii','CSecurityManager.validationKey cannot be empty.'));
	}

	/**
	 * @return string the private key used to encrypt/decrypt data.
	 * If the key is not explicitly set, a random one is generated and returned.
	 * @throws CException in case random string cannot be generated.
	 */
	public function getEncryptionKey()
	{
		if($this->_encryptionKey!==null)
			return $this->_encryptionKey;
		else
		{
			if(($key=Yii::app()->getGlobalState(self::STATE_ENCRYPTION_KEY))!==null)
				$this->setEncryptionKey($key);
			else
			{
				if(($key=$this->generateRandomString(32,true))===false)
					if(($key=$this->generateRandomString(32,false))===false)
						throw new CException(Yii::t('yii',
							'CSecurityManager::generateRandomString() cannot generate random string in the current environment.'));
				$this->setEncryptionKey($key);
				Yii::app()->setGlobalState(self::STATE_ENCRYPTION_KEY,$key);
			}
			return $this->_encryptionKey;
		}
	}

	/**
	 * @param string $value the key used to encrypt/decrypt data.
	 * @throws CException if the key is empty
	 */
	public function setEncryptionKey($value)
	{
		$this->validateEncryptionKey($value);
		$this->_encryptionKey=$value;
	}

	/**
	 * This method has been deprecated since version 1.1.3.
	 * Please use {@link hashAlgorithm} instead.
	 * @return string -
	 * @deprecated
	 */
	public function getValidation()
	{
		return $this->hashAlgorithm;
	}

	/**
	 * This method has been deprecated since version 1.1.3.
	 * Please use {@link hashAlgorithm} instead.
	 * @param string $value -
	 * @deprecated
	 */
	public function setValidation($value)
	{
		$this->hashAlgorithm=$value;
	}

	/**
	 * Encrypts data.
	 */
	public function encrypt($data,$key=null)
	{
		if($key===null)
			$key=$this->getEncryptionKey();
		$this->validateEncryptionKey($key);
		$module=$this->openCryptModule();
		srand();
		$iv=mcrypt_create_iv(mcrypt_enc_get_iv_size($module), MCRYPT_RAND);
		mcrypt_generic_init($module,$key,$iv);
		$encrypted=$iv.mcrypt_generic($module,$data);
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);
		return $encrypted;
	}

	/**
	 * Decrypts data
	 */
	public function decrypt($data,$key=null)
	{
		if($key===null)
			$key=$this->getEncryptionKey();
		$this->validateEncryptionKey($key);
		$module=$this->openCryptModule();
		$ivSize=mcrypt_enc_get_iv_size($module);
		$iv=$this->substr($data,0,$ivSize);
		mcrypt_generic_init($module,$key,$iv);
		$decrypted=mdecrypt_generic($module,$this->substr($data,$ivSize,$this->strlen($data)));
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);
		return rtrim($decrypted,"\0");
	}

	/**
	 * Opens the mcrypt module with the configuration specified in {@link cryptAlgorithm}.
	 */
	protected function openCryptModule()
	{
		if(extension_loaded('mcrypt'))
		{
			if(is_array($this->cryptAlgorithm))
				$module=@call_user_func_array('mcrypt_module_open',$this->cryptAlgorithm);
			else
				$module=@mcrypt_module_open($this->cryptAlgorithm,'', MCRYPT_MODE_CBC,'');

			if($module===false)
				throw new CException(Yii::t('yii','Failed to initialize the mcrypt module.'));

			return $module;
		}
		else
			throw new CException(Yii::t('yii','CSecurityManager requires PHP mcrypt extension to be loaded in order to use data encryption feature.'));
	}

	/**
	 * Prefixes data with an HMAC.
	 */
	public function hashData($data,$key=null)
	{
		return $this->computeHMAC($data,$key).$data;
	}

	/**
	 * Validates if data is tampered.
	 */
	public function validateData($data,$key=null)
	{
		if (!is_string($data))
			return false;

		$len=$this->strlen($this->computeHMAC('test'));
		if($this->strlen($data)>=$len)
		{
			$hmac=$this->substr($data,0,$len);
			$data2=$this->substr($data,$len,$this->strlen($data));
			return $this->compareString($hmac,$this->computeHMAC($data2,$key))?$data2:false;
		}
		else
			return false;
	}

	/**
	 * Computes the HMAC for the data with {@link getValidationKey validationKey}. This method has been made public
	 */
	public function computeHMAC($data,$key=null,$hashAlgorithm=null)
	{
		if($key===null)
			$key=$this->getValidationKey();
		if($hashAlgorithm===null)
			$hashAlgorithm=$this->hashAlgorithm;

		if(function_exists('hash_hmac'))
			return hash_hmac($hashAlgorithm,$data,$key);

		if(0===strcasecmp($hashAlgorithm,'sha1'))
		{
			$pack='H40';
			$func='sha1';
		}
		elseif(0===strcasecmp($hashAlgorithm,'md5'))
		{
			$pack='H32';
			$func='md5';
		}
		else
		{
			throw new CException(Yii::t('yii','Only SHA1 and MD5 hashing algorithms are supported when using PHP 5.1.1 or below.'));
		}
		if($this->strlen($key)>64)
			$key=pack($pack,$func($key));
		if($this->strlen($key)<64)
			$key=str_pad($key,64,chr(0));
		$key=$this->substr($key,0,64);
		return $func((str_repeat(chr(0x5C), 64) ^ $key) . pack($pack, $func((str_repeat(chr(0x36), 64) ^ $key) . $data)));
	}

	/**
	 * Generate a random ASCII string.
	 */
	public function generateRandomString($length,$cryptographicallyStrong=true)
	{
		if(($randomBytes=$this->generateRandomBytes($length+2,$cryptographicallyStrong))!==false)
			return strtr($this->substr(base64_encode($randomBytes),0,$length),array('+'=>'_','/'=>'~'));
		return false;
	}

	/**
	 * Generates a string of random bytes.
	 */
	public function generateRandomBytes($length,$cryptographicallyStrong=true)
	{
		$bytes='';
		if(function_exists('openssl_random_pseudo_bytes'))
		{
			$bytes=openssl_random_pseudo_bytes($length,$strong);
			if($this->strlen($bytes)>=$length && ($strong || !$cryptographicallyStrong))
				return $this->substr($bytes,0,$length);
		}

		if(function_exists('mcrypt_create_iv') &&
			($bytes=mcrypt_create_iv($length, MCRYPT_DEV_URANDOM))!==false &&
			$this->strlen($bytes)>=$length)
		{
			return $this->substr($bytes,0,$length);
		}

		if(($file=@fopen('/dev/urandom','rb'))!==false &&
			($bytes=@fread($file,$length))!==false &&
			(fclose($file) || true) &&
			$this->strlen($bytes)>=$length)
		{
			return $this->substr($bytes,0,$length);
		}

		$i=0;
		while($this->strlen($bytes)<$length &&
			($byte=$this->generateSessionRandomBlock())!==false &&
			++$i<3)
		{
			$bytes.=$byte;
		}
		if($this->strlen($bytes)>=$length)
			return $this->substr($bytes,0,$length);

		if ($cryptographicallyStrong)
			return false;

		while($this->strlen($bytes)<$length)
			$bytes.=$this->generatePseudoRandomBlock();
		return $this->substr($bytes,0,$length);
	}

	
	public function generatePseudoRandomBlock()
	{
		$bytes='';

		if (function_exists('openssl_random_pseudo_bytes')
			&& ($bytes=openssl_random_pseudo_bytes(512))!==false
			&& $this->strlen($bytes)>=512)
		{
			return $this->substr($bytes,0,512);
		}

		for($i=0;$i<32;++$i)
			$bytes.=pack('S',mt_rand(0,0xffff));

		// On UNIX and UNIX-like operating systems the numerical values in `ps`, `uptime` and `iostat`
		// ought to be fairly unpredictable. Gather the non-zero digits from those.
		foreach(array('ps','uptime','iostat') as $command) {
			@exec($command,$commandResult,$retVal);
			if(is_array($commandResult) && !empty($commandResult) && $retVal==0)
				$bytes.=preg_replace('/[^1-9]/','',implode('',$commandResult));
		}

		// Gather the current time's microsecond part. Note: this is only a source of entropy on
		// the first call! If multiple calls are made, the entropy is only as much as the
		// randomness in the time between calls.
		$bytes.=$this->substr(microtime(),2,6);

		// Concatenate everything gathered, mix it with sha512. hash() is part of PHP core and
		// enabled by default but it can be disabled at compile time but we ignore that possibility here.
		return hash('sha512',$bytes,true);
	}

	
	public function generateSessionRandomBlock()
	{
		ini_set('session.entropy_length',20);
		if(ini_get('session.entropy_length')!=20)
			return false;

		// These calls are (supposed to be, according to PHP manual) safe even if
		// there is already an active session for the calling script.
		@session_start();
		@session_regenerate_id();

		$bytes=session_id();
		if(!$bytes)
			return false;

		// $bytes has 20 bytes of entropy but the session manager converts the binary
		// random bytes into something readable. We have to convert that back.
		// SHA-1 should do it without losing entropy.
		return sha1($bytes,true);
	}

	
	private function strlen($string)
	{
		return $this->_mbstring ? mb_strlen($string,'8bit') : strlen($string);
	}

	
	private function substr($string,$start,$length)
	{
		return $this->_mbstring ? mb_substr($string,$start,$length,'8bit') : substr($string,$start,$length);
	}
    
	
	protected function validateEncryptionKey($key)
	{
		if(is_string($key))
		{
			$cryptAlgorithm = is_array($this->cryptAlgorithm) ? $this->cryptAlgorithm[0] : $this->cryptAlgorithm;

			$supportedKeyLengths=mcrypt_module_get_supported_key_sizes($cryptAlgorithm);

			if($supportedKeyLengths)
			{
				if(!in_array($this->strlen($key),$supportedKeyLengths)) {
					throw new CException(Yii::t('yii','Encryption key length can be {keyLengths}.',array('{keyLengths}'=>implode(',',$supportedKeyLengths))));
				}
			}
			elseif(isset(self::$encryptionKeyMinimumLengths[$cryptAlgorithm]))
			{
				$minLength=self::$encryptionKeyMinimumLengths[$cryptAlgorithm];
				$maxLength=mcrypt_module_get_algo_key_size($cryptAlgorithm);
				if($this->strlen($key)<$minLength || $this->strlen($key)>$maxLength)
					throw new CException(Yii::t('yii','Encryption key length must be between {minLength} and {maxLength}.',array('{minLength}'=>$minLength,'{maxLength}'=>$maxLength)));
			}
			else
				throw new CException(Yii::t('yii','Failed to validate key. Supported key lengths of cipher not known.'));
		}
		else
			throw new CException(Yii::t('yii','Encryption key should be a string.'));
	}
    
	/**
	 * Decrypts legacy ciphertext which was produced by the old, broken implementation of encrypt().
	 */
	public function legacyDecrypt($data,$key=null,$cipher='des')
	{
		if (!$key)
		{
			$key=Yii::app()->getGlobalState(self::STATE_ENCRYPTION_KEY);
			if(!$key)
				throw new CException(Yii::t('yii','No encryption key specified.'));
			$key = md5($key);
		}

		if(extension_loaded('mcrypt'))
		{
			if(is_array($cipher))
				$module=@call_user_func_array('mcrypt_module_open',$cipher);
			else
				$module=@mcrypt_module_open($cipher,'', MCRYPT_MODE_CBC,'');

			if($module===false)
				throw new CException(Yii::t('yii','Failed to initialize the mcrypt module.'));
		}
		else
			throw new CException(Yii::t('yii','CSecurityManager requires PHP mcrypt extension to be loaded in order to use data encryption feature.'));

		$derivedKey=$this->substr($key,0,mcrypt_enc_get_key_size($module));
		$ivSize=mcrypt_enc_get_iv_size($module);
		$iv=$this->substr($data,0,$ivSize);
		mcrypt_generic_init($module,$derivedKey,$iv);
		$decrypted=mdecrypt_generic($module,$this->substr($data,$ivSize,$this->strlen($data)));
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);
		return rtrim($decrypted,"\0");
	}

	/**
	 * Performs string comparison using timing attack resistant approach
	 */
	public function compareString($expected,$actual)
	{
		$expected.="\0";
		$actual.="\0";
		$expectedLength=$this->strlen($expected);
		$actualLength=$this->strlen($actual);
		$diff=$expectedLength-$actualLength;
		for($i=0;$i<$actualLength;$i++)
			$diff|=(ord($actual[$i])^ord($expected[$i%$expectedLength]));
		return $diff===0;
	}
}
