<?php
namespace App\Models;

class HistoryModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'history';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'record_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'user_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'table_name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'before', 'type' => 'longtext', 'default' => ':NULL'),
		array('name' => 'after', 'type' => 'longtext', 'default' => ':NULL'),
		array('name' => 'ip', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()')
	);
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>