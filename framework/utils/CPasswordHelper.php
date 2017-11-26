<?php
/**
 * CPasswordHelper provides a simple API for secure password hashing and verification.
 */
class CPasswordHelper
{
	/**
	 * Check for availability of PHP crypt() with the Blowfish hash option.
	 * @throws CException if the runtime system does not have PHP crypt() or its Blowfish hash option.
	 */
	protected static function checkBlowfish()
	{
		if(!function_exists('crypt'))
			throw new CException(Yii::t('yii','{class} requires the PHP crypt() function. This system does not have it.',
				array('{class}'=>__CLASS__)));

		if(!defined('CRYPT_BLOWFISH') || !CRYPT_BLOWFISH)
			throw new CException(Yii::t('yii',
				'{class} requires the Blowfish option of the PHP crypt() function. This system does not have it.',
				array('{class}'=>__CLASS__)));
	}

	/**
	 * Generate a secure hash from a password and a random salt.
	 */
	public static function hashPassword($password,$cost=13)
	{
		self::checkBlowfish();
		$salt=self::generateSalt($cost);
		$hash=crypt($password,$salt);

		if(!is_string($hash) || (function_exists('mb_strlen') ? mb_strlen($hash, '8bit') : strlen($hash))<32)
			throw new CException(Yii::t('yii','Internal error while generating hash.'));

		return $hash;
	}

	/**
	 * Verify a password against a hash.
	 */
	public static function verifyPassword($password, $hash)
	{
		self::checkBlowfish();
		if(!is_string($password) || $password==='')
			return false;

		if (!$password || !preg_match('{^\$2[axy]\$(\d\d)\$[\./0-9A-Za-z]{22}}',$hash,$matches) ||
			$matches[1]<4 || $matches[1]>31)
			return false;

		$test=crypt($password,$hash);
		if(!is_string($test) || strlen($test)<32)
			return false;

		return self::same($test, $hash);
	}

	/**
	 * Check for sameness of two strings using an algorithm with timing
	 * independent of the string values if the subject strings are of equal length.
	 */
	public static function same($a,$b)
	{
		if(!is_string($a) || !is_string($b))
			return false;

		$mb=function_exists('mb_strlen');
		$length=$mb ? mb_strlen($a,'8bit') : strlen($a);
		if($length!==($mb ? mb_strlen($b,'8bit') : strlen($b)))
			return false;

		$check=0;
		for($i=0;$i<$length;$i+=1)
			$check|=(ord($a[$i])^ord($b[$i]));

		return $check===0;
	}

	/**
	 * Generates a salt that can be used to generate a password hash.
	 */
	public static function generateSalt($cost=13)
	{
		if(!is_numeric($cost))
			throw new CException(Yii::t('yii','{class}::$cost must be a number.',array('{class}'=>__CLASS__)));

		$cost=(int)$cost;
		if($cost<4 || $cost>31)
			throw new CException(Yii::t('yii','{class}::$cost must be between 4 and 31.',array('{class}'=>__CLASS__)));

		if(($random=Yii::app()->getSecurityManager()->generateRandomString(22,true))===false)
			if(($random=Yii::app()->getSecurityManager()->generateRandomString(22,false))===false)
				throw new CException(Yii::t('yii','Unable to generate random string.'));
		return sprintf('$2y$%02d$',$cost).strtr($random,array('_'=>'.','~'=>'/'));
	}
}
