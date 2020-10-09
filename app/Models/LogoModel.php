<?php
namespace App\Models;

class LogoModel extends AppModel {
	protected $primaryKey = 'id';
	
	protected $table = 'logos';
	
	protected $schema = array(
			array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
			array('name' => 'origin_path', 'type' => 'varchar', 'default' => ':NULL'),
			array('name' => 'small_path', 'type' => 'varchar', 'default' => ':NULL'),			
			array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
			array('name' => 'modified', 'type' => 'datetime', 'default' => ':NOW()'),
	);
	
	public $i18n = array();
	
	public static function factory($attr=array())
	{
		return new LogoModel($attr);
	}
}