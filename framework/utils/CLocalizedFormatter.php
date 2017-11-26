<?php
/**
 * CLocalizedFormatter class file.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CLocalizedFormatter provides a set of commonly used data formatting methods based on the current locale settings.
 */
class CLocalizedFormatter extends CFormatter
{
	private $_locale;
	/**
	 * @var string the width of the date pattern. It can be 'full', 'long', 'medium' and 'short'.
	 */
	public $dateFormat='medium';
	/**
	 * @var string the width of the time pattern. It can be 'full', 'long', 'medium' and 'short'.
	 */
	public $timeFormat='medium';

	/**
	 * Set the locale to use for formatting values.
	 */
	public function setLocale($locale)
	{
		if(is_string($locale))
			$locale=CLocale::getInstance($locale);
		$this->sizeFormat['decimalSeparator']=$locale->getNumberSymbol('decimal');
		$this->_locale=$locale;
	}

	/**
	 * @return CLocale $locale the locale currently used for formatting values
	 */
	public function getLocale()
	{
		if($this->_locale === null) {
			$this->setLocale(Yii::app()->locale);
		}
		return $this->_locale;
	}

	
	public function formatBoolean($value)
	{
		return $value ? Yii::t('yii','Yes') : Yii::t('yii','No');
	}

	
	public function formatDate($value)
	{
		return $this->getLocale()->dateFormatter->formatDateTime($this->normalizeDateValue($value), $this->dateFormat, null);
	}

	
	public function formatTime($value)
	{
		return $this->getLocale()->dateFormatter->formatDateTime($this->normalizeDateValue($value), null, $this->timeFormat);
	}

	public function formatDatetime($value)
	{
		return $this->getLocale()->dateFormatter->formatDateTime($this->normalizeDateValue($value), $this->dateFormat, $this->timeFormat);
	}

	
	public function formatNumber($value)
	{
		return $this->getLocale()->numberFormatter->formatDecimal($value);
	}
}
