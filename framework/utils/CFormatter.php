<?php
/**
 * CFormatter provides a set of commonly used data formatting methods.
 */
class CFormatter extends CApplicationComponent
{
	/**
	 * @var CHtmlPurifier
	 */
	private $_htmlPurifier;

	/**
	 * @var string the format string to be used to format a date using PHP date() function.
	 */
	public $dateFormat='Y/m/d';
	/**
	 * @var string the format string to be used to format a time using PHP date() function.
	 */
	public $timeFormat='h:i:s A';
	/**
	 * @var string the format string to be used to format a date and time using PHP date() function.
	 */
	public $datetimeFormat='Y/m/d h:i:s A';
	
	public $numberFormat=array('decimals'=>null, 'decimalSeparator'=>null, 'thousandSeparator'=>null);
	
	public $booleanFormat=array('No','Yes');
	
	public $htmlPurifierOptions=array();
	
	public $sizeFormat=array(
		'base'=>1024,
		'decimals'=>2,
		'decimalSeparator'=>null,
	);

	
	public function __call($name,$parameters)
	{
		if(method_exists($this,'format'.$name))
			return call_user_func_array(array($this,'format'.$name),$parameters);
		else
			return parent::__call($name,$parameters);
	}


	public function format($value,$type)
	{
		$method='format'.$type;
		if(method_exists($this,$method))
			return $this->$method($value);
		else
			throw new CException(Yii::t('yii','Unknown type "{type}".',array('{type}'=>$type)));
	}

	/**
	 * Formats the value as is without any formatting.
	 */
	public function formatRaw($value)
	{
		return $value;
	}

	/**
	 * Formats the value as a HTML-encoded plain text.
	 */
	public function formatText($value)
	{
		return CHtml::encode($value);
	}

	
	public function formatNtext($value,$paragraphs=false,$removeEmptyParagraphs=true)
	{
		$value=CHtml::encode($value);
		if($paragraphs)
		{
			$value='<p>'.str_replace(array("\r\n", "\n", "\r"), '</p><p>',$value).'</p>';
			if($removeEmptyParagraphs)
				$value=preg_replace('/(<\/p><p>){2,}/i','</p><p>',$value);
			return $value;
		}
		else
		{
			return nl2br($value);
		}
	}

	
	public function formatHtml($value)
	{
		return $this->getHtmlPurifier()->purify($value);
	}

	
	public function formatDate($value)
	{
		return date($this->dateFormat,$this->normalizeDateValue($value));
	}

	
	public function formatTime($value)
	{
		return date($this->timeFormat,$this->normalizeDateValue($value));
	}

	public function formatDatetime($value)
	{
		return date($this->datetimeFormat,$this->normalizeDateValue($value));
	}

	
	protected function normalizeDateValue($time)
	{
		if(is_string($time))
		{
			if(ctype_digit($time) || ($time{0}=='-' && ctype_digit(substr($time, 1))))
				return (int)$time;
			else
				return strtotime($time);
		}
		elseif (class_exists('DateTime', false) && $time instanceof DateTime)
			return $time->getTimestamp();
		else
			return (int)$time;
	}

	/**
	 * Formats the value as a boolean.
	 */
	public function formatBoolean($value)
	{
		return $value ? $this->booleanFormat[1] : $this->booleanFormat[0];
	}

	/**
	 * Formats the value as a mailto link.
	 */
	public function formatEmail($value)
	{
		return CHtml::mailto($value);
	}

	/**
	 * Formats the value as an image tag.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function formatImage($value)
	{
		return CHtml::image($value);
	}

	/**
	 * Formats the value as a hyperlink.
	 */
	public function formatUrl($value)
	{
		$url=$value;
		if(strpos($url,'http://')!==0 && strpos($url,'https://')!==0)
			$url='http://'.$url;
		return CHtml::link(CHtml::encode($value),$url);
	}

	/**
	 * Formats the value as a number using PHP number_format() function.
	 */
	public function formatNumber($value)
	{
		return number_format($value,$this->numberFormat['decimals'],$this->numberFormat['decimalSeparator'],$this->numberFormat['thousandSeparator']);
	}

	/**
	 * @return CHtmlPurifier the HTML purifier instance
	 */
	public function getHtmlPurifier()
	{
		if($this->_htmlPurifier===null)
			$this->_htmlPurifier=new CHtmlPurifier;
		$this->_htmlPurifier->options=$this->htmlPurifierOptions;
		return $this->_htmlPurifier;
	}

	/**
	 * Formats the value in bytes as a size in human readable form.
	 */
	public function formatSize($value,$verbose=false)
	{
		$base=$this->sizeFormat['base'];
		for($i=0; $base<=$value && $i<5; $i++)
			$value=$value/$base;

		$value=round($value, $this->sizeFormat['decimals']);
		$formattedValue=isset($this->sizeFormat['decimalSeparator']) ? str_replace('.',$this->sizeFormat['decimalSeparator'],$value) : $value;
		$params=array($value,'{n}'=>$formattedValue);

		switch($i)
		{
			case 0:
				return $verbose ? Yii::t('yii','{n} byte|{n} bytes',$params) : Yii::t('yii', '{n} B',$params);
			case 1:
				return $verbose ? Yii::t('yii','{n} kilobyte|{n} kilobytes',$params) : Yii::t('yii','{n} KB',$params);
			case 2:
				return $verbose ? Yii::t('yii','{n} megabyte|{n} megabytes',$params) : Yii::t('yii','{n} MB',$params);
			case 3:
				return $verbose ? Yii::t('yii','{n} gigabyte|{n} gigabytes',$params) : Yii::t('yii','{n} GB',$params);
			default:
				return $verbose ? Yii::t('yii','{n} terabyte|{n} terabytes',$params) : Yii::t('yii','{n} TB',$params);
		}
	}
}
