<?php
namespace App\Plugins\Log\Models;

class LogConfigModel extends LogAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'plugin_log_config';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'filename', 'type' => 'varchar', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new LogConfigModel($attr);
	}
}
?>