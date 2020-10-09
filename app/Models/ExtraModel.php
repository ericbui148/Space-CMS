<?php
namespace App\Models;

class ExtraModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'extras';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'product_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'type', 'type' => 'enum', 'default' => 'single'),
		array('name' => 'price', 'type' => 'decimal', 'default' => ':NULL'),
		array('name' => 'is_mandatory', 'type' => 'tinyint', 'default' => 0)
	);
	
	protected $i18n = array('extra_name', 'extra_title');
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>