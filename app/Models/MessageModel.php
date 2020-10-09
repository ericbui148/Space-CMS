<?php
namespace App\Models;

class MessageModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'messages';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'subject', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'tinymce_message', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'plain_message', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'modified', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T')
	);
	
	public $i18n = array('name');
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>