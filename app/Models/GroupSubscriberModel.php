<?php
namespace App\Models;

class GroupSubscriberModel extends AppModel
{
	protected $table = 'groups_subscribers';
	
	protected $schema = array(
		array('name' => 'group_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'subscriber_id', 'type' => 'int', 'default' => ':NULL')
	);
	
	public $i18n = array('name');
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>