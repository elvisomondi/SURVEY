<?php
/**
 * CUnsafeValidator marks the associated attributes to be unsafe so that they cannot be massively assigned.
 */
class CUnsafeValidator extends CValidator
{
	
	public $safe=false;
	
	protected function validateAttribute($object,$attribute)
	{
	}
}

