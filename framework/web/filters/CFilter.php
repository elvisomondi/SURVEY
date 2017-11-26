<?php

class CFilter extends CComponent implements IFilter
{
	
	public function filter($filterChain)
	{
		if($this->preFilter($filterChain))
		{
			$filterChain->run();
			$this->postFilter($filterChain);
		}
	}

	
	public function init()
	{
	}

	
	protected function preFilter($filterChain)
	{
		return true;
	}

	/**
	 * Performs the post-action filtering.
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 */
	protected function postFilter($filterChain)
	{
	}
}