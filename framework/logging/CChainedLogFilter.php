<?php
/**
 * CChainedLogFilter allows you to attach multiple log filters to a log route.
 */
class CChainedLogFilter extends CComponent implements ILogFilter
{
	
	public $filters=array();

	/**
	 * Filters the given log messages by applying all filters configured.
	 */
	public function filter(&$logs)
	{
		foreach($this->filters as $filter)
			Yii::createComponent($filter)->filter($logs);
	}
}