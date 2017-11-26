<?php
/**
 * CDateTimeParser converts a date/time string to a UNIX timestamp according to the specified pattern.
 */
class CDateTimeParser
{
	private static $_mbstringAvailable;

	
	public static function parse($value,$pattern='MM/dd/yyyy',$defaults=array())
	{
		if(self::$_mbstringAvailable===null)
			self::$_mbstringAvailable=extension_loaded('mbstring');

		$tokens=self::tokenize($pattern);
		$i=0;
		$n=self::$_mbstringAvailable ? mb_strlen($value,Yii::app()->charset) : strlen($value);
		foreach($tokens as $token)
		{
			switch($token)
			{
				case 'yyyy':
				case 'y':
				{
					if(($year=self::parseInteger($value,$i,4,4))===false)
						return false;
					$i+=4;
					break;
				}
				case 'yy':
				{
					if(($year=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=strlen($year);
					break;
				}
				case 'MMMM':
				{
					$monthName='';
					if(($month=self::parseMonth($value,$i,'wide',$monthName))===false)
						return false;
					$i+=self::$_mbstringAvailable ? mb_strlen($monthName,Yii::app()->charset) : strlen($monthName);
					break;
				}
				case 'MMM':
				{
					$monthName='';
					if(($month=self::parseMonth($value,$i,'abbreviated',$monthName))===false)
						return false;
					$i+=self::$_mbstringAvailable ? mb_strlen($monthName,Yii::app()->charset) : strlen($monthName);
					break;
				}
				case 'MM':
				{
					if(($month=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'M':
				{
					if(($month=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=strlen($month);
					break;
				}
				case 'dd':
				{
					if(($day=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'd':
				{
					if(($day=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=strlen($day);
					break;
				}
				case 'h':
				case 'H':
				{
					if(($hour=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=strlen($hour);
					break;
				}
				case 'hh':
				case 'HH':
				{
					if(($hour=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'm':
				{
					if(($minute=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=strlen($minute);
					break;
				}
				case 'mm':
				{
					if(($minute=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 's':
				{
					if(($second=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=strlen($second);
					break;
				}
				case 'ss':
				{
					if(($second=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'a':
				{
					if(($ampm=self::parseAmPm($value,$i))===false)
						return false;
					if(isset($hour))
					{
						if($hour==12 && $ampm==='am')
							$hour=0;
						elseif($hour<12 && $ampm==='pm')
							$hour+=12;
					}
					$i+=2;
					break;
				}
				default:
				{
					$tn=self::$_mbstringAvailable ? mb_strlen($token,Yii::app()->charset) : strlen($token);
					if($i>=$n || ($token{0}!='?' && (self::$_mbstringAvailable ? mb_substr($value,$i,$tn,Yii::app()->charset) : substr($value,$i,$tn))!==$token))
						return false;
					$i+=$tn;
					break;
				}
			}
		}
		if($i<$n)
			return false;

		if(!isset($year))
			$year=isset($defaults['year']) ? $defaults['year'] : date('Y');
		if(!isset($month))
			$month=isset($defaults['month']) ? $defaults['month'] : date('n');
		if(!isset($day))
			$day=isset($defaults['day']) ? $defaults['day'] : date('j');

		if(strlen($year)===2)
		{
			if($year>=70)
				$year+=1900;
			else
				$year+=2000;
		}
		$year=(int)$year;
		$month=(int)$month;
		$day=(int)$day;

		if(
			!isset($hour) && !isset($minute) && !isset($second)
			&& !isset($defaults['hour']) && !isset($defaults['minute']) && !isset($defaults['second'])
		)
			$hour=$minute=$second=0;
		else
		{
			if(!isset($hour))
				$hour=isset($defaults['hour']) ? $defaults['hour'] : date('H');
			if(!isset($minute))
				$minute=isset($defaults['minute']) ? $defaults['minute'] : date('i');
			if(!isset($second))
				$second=isset($defaults['second']) ? $defaults['second'] : date('s');
			$hour=(int)$hour;
			$minute=(int)$minute;
			$second=(int)$second;
		}

		if(CTimestamp::isValidDate($year,$month,$day) && CTimestamp::isValidTime($hour,$minute,$second))
			return CTimestamp::getTimestamp($hour,$minute,$second,$month,$day,$year);
		else
			return false;
	}

	/*
	 * @param string $pattern the pattern that the date string is following
	 */
	private static function tokenize($pattern)
	{
		if(!($n=self::$_mbstringAvailable ? mb_strlen($pattern,Yii::app()->charset) : strlen($pattern)))
			return array();
		$tokens=array();
		$c0=self::$_mbstringAvailable ? mb_substr($pattern,0,1,Yii::app()->charset) : substr($pattern,0,1);

		for($start=0,$i=1;$i<$n;++$i)
		{
			$c=self::$_mbstringAvailable ? mb_substr($pattern,$i,1,Yii::app()->charset) : substr($pattern,$i,1);
			if($c!==$c0)
			{
				$tokens[]=self::$_mbstringAvailable ? mb_substr($pattern,$start,$i-$start,Yii::app()->charset) : substr($pattern,$start,$i-$start);
				$c0=$c;
				$start=$i;
			}
		}
		$tokens[]=self::$_mbstringAvailable ? mb_substr($pattern,$start,$n-$start,Yii::app()->charset) : substr($pattern,$start,$n-$start);
		return $tokens;
	}

	
	protected static function parseInteger($value,$offset,$minLength,$maxLength)
	{
		for($len=$maxLength;$len>=$minLength;--$len)
		{
			$v=self::$_mbstringAvailable ? mb_substr($value,$offset,$len,Yii::app()->charset) : substr($value,$offset,$len);
			if(ctype_digit($v) && (self::$_mbstringAvailable ? mb_strlen($v,Yii::app()->charset) : strlen($v))>=$minLength)
				return $v;
		}
		return false;
	}

	
	protected static function parseAmPm($value, $offset)
	{
		$v=strtolower(self::$_mbstringAvailable ? mb_substr($value,$offset,2,Yii::app()->charset) : substr($value,$offset,2));
		return $v==='am' || $v==='pm' ? $v : false;
	}

	
	protected static function parseMonth($value,$offset,$width,&$monthName)
	{
		$valueLength=self::$_mbstringAvailable ? mb_strlen($value,Yii::app()->charset) : strlen($value);
		for($len=1; $offset+$len<=$valueLength; $len++)
		{
			$monthName=self::$_mbstringAvailable ? mb_substr($value,$offset,$len,Yii::app()->charset) : substr($value,$offset,$len);
			if(!preg_match('/^[\p{L}\p{M}]+$/u',$monthName)) // unicode aware replacement for ctype_alpha($monthName)
			{
				$monthName=self::$_mbstringAvailable ? mb_substr($monthName,0,-1,Yii::app()->charset) : substr($monthName,0,-1);
				break;
			}
		}
		$monthName=self::$_mbstringAvailable ? mb_strtolower($monthName,Yii::app()->charset) : strtolower($monthName);

		$monthNames=Yii::app()->getLocale()->getMonthNames($width,false);
		foreach($monthNames as $k=>$v)
			$monthNames[$k]=rtrim(self::$_mbstringAvailable ? mb_strtolower($v,Yii::app()->charset) : strtolower($v),'.');

		$monthNamesStandAlone=Yii::app()->getLocale()->getMonthNames($width,true);
		foreach($monthNamesStandAlone as $k=>$v)
			$monthNamesStandAlone[$k]=rtrim(self::$_mbstringAvailable ? mb_strtolower($v,Yii::app()->charset) : strtolower($v),'.');

		if(($v=array_search($monthName,$monthNames))===false && ($v=array_search($monthName,$monthNamesStandAlone))===false)
			return false;
		return $v;
	}
}
