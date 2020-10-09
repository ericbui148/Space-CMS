<?php
namespace App\Models;

use Core\Framework\Model;

class AppModel extends Model
{
	protected $rules = array();
	
	public static function factory($attr=array())
	{
		return new AppModel($attr);
	}
	
	public function getI18n()
	{
		return isset($this->i18n) ? $this->i18n : array();
	}
	
	public function addRule($field)
	{
		if (!isset($this->validate['rules']))
		{
			$this->validate['rules'] = array();
		}
	
		if (!isset($this->validate['rules'][$field])
				&& isset($this->rules[$field]))
		{
			$this->validate['rules'][$field] = $this->rules[$field];
			return TRUE;
		}
	
		return FALSE;
	}
	
	public function removeRule($field, $rule=NULL)
	{
		if (isset($this->validate['rules'], $this->validate['rules'][$field]))
		{
			if (is_null($rule))
			{
				unset($this->validate['rules'][$field]);
				return TRUE;
			}
				
			if (is_array($this->validate['rules'][$field])
					&& is_string($rule)
					&& isset($this->validate['rules'][$field][$rule]))
			{
				unset($this->validate['rules'][$field][$rule]);
				return TRUE;
			}
		}
	
		return FALSE;
	}
}
?>