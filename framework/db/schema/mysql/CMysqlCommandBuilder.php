<?php

class CMysqlCommandBuilder extends CDbCommandBuilder
{
	
	public function applyJoin($sql,$join)
	{
		if($join=='')
			return $sql;

		if(strpos($sql,'UPDATE')===0 && ($pos=strpos($sql,'SET'))!==false)
			return substr($sql,0,$pos).$join.' '.substr($sql,$pos);
		elseif(strpos($sql,'DELETE FROM ')===0)
		{
			$tableName=substr($sql,12);
			return "DELETE {$tableName} FROM {$tableName} ".$join;
		}
		else
			return $sql.' '.$join;
	}

	
	public function applyLimit($sql,$limit,$offset)
	{
		
		if($limit<=0 && $offset>0)
			$limit=PHP_INT_MAX;
		return parent::applyLimit($sql,$limit,$offset);
	}
}
