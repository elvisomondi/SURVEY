<?php

/**
 * CFormStringElement represents a string in a form.
 */
class CFormStringElement extends CFormElement
{
	/**
	 * @var string the string content
	 */
	public $content;

	private $_on;

	public function getOn()
	{
		return $this->_on;
	}

	/**
	 * @param string $value scenario names separated by commas.
	 */
	public function setOn($value)
	{
		$this->_on=preg_split('/[\s,]+/',$value,-1,PREG_SPLIT_NO_EMPTY);
	}

	
	public function render()
	{
		return $this->content;
	}

	/**
	 * Evaluates the visibility of this element.
	 * This method will check the {@link on} property to see if
	 * the model is in a scenario that should have this string displayed.
	 * @return boolean whether this element is visible.
	 */
	protected function evaluateVisible()
	{
		return empty($this->_on) || in_array($this->getParent()->getModel()->getScenario(),$this->_on);
	}
}
