<?php
namespace App\Models;

class GroupModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'groups';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'group_title', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'subscribed_fields', 'type' => 'varchar', 'default' => 'first_name,email'),
		array('name' => 'send_confirm', 'type' => 'enum', 'default' => 'F'),
		array('name' => 'confirm_subject', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'confirm_message', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'send_response', 'type' => 'enum', 'default' => 'F'),
		array('name' => 'response_subject', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'response_message', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T')
	);
	
	public $i18n = array('name');
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>