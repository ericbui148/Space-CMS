<?php
namespace App\Models;

class QueueModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'queues';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'message_id', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'subscriber_id', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'date_sent', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'ingrogress')
	);
	
	public $i18n = array('name');
	
	public static function factory($attr = array())
	{
		return new self($attr);
	}
}
?>