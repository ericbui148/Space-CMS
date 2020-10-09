<?php
namespace App\Models;

class OptionModel extends AppModel
{
	protected $primaryKey = NULL;
	
	protected $table = 'options';
	
	protected $schema = array(
		array('name' => 'foreign_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'key', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'tab_id', 'type' => 'tinyint', 'default' => ':NULL'),
		array('name' => 'value', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'label', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'type', 'type' => 'varchar', 'default' => 'string'),
		array('name' => 'order', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'is_visible', 'type' => 'tinyint', 'default' => 1),
		array('name' => 'style', 'type' => 'varchar', 'default' => 'string')
	);
	
	protected $i18n = array(
			'confirm_subject_client', 
			'confirm_tokens_client', 
			'payment_subject_client', 
			'payment_tokens_client',
			'confirm_subject_admin', 
			'confirm_tokens_admin', 
			'payment_subject_admin', 
			'payment_tokens_admin', 
			'terms_url', 
			'terms_body',
			'confirm_sms_admin', 
			'payment_sms_admin',
			'register_subject', 
			'register_tokens', 
			'forgot_subject', 
			'forgot_tokens'
	);
	
	public static function factory($attr = array())
	{
		return new OptionModel($attr);
	}
	
	public function getAllPairs($foreign_id)
	{
		return $this->where('t1.foreign_id', $foreign_id)->findAll()->getDataPair('key', 'value');
	}
	
	public function getPairs($foreign_id)
	{
		$_arr = $this->where('t1.foreign_id', $foreign_id)->findAll()->getData();
		$arr = array();
		foreach ($_arr as $row)
		{
			switch ($row['type'])
			{
				case 'enum':
				case 'bool':
					list(, $arr[$row['key']]) = explode("::", $row['value']);
					break;
				default:
					$arr[$row['key']] = $row['value'];
					break;
			}
		}
		return $arr;
	}
}
?>