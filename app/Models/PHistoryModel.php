<?php
namespace App\Models;

class PHistoryModel extends AppModel
{
	const ACTION_ADD = 1;
	const ACTION_UPDATE = 2;
	const ACTION_DELETE = 3;
	
	protected $primaryKey = 'id';
	
	protected $table = 'phistories';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'user_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'page_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'modified', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'ip', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'action', 'type' => 'tinyint', 'default' => ':NULL')
	);
	
	public $i18n = array('page_name', 'page_content');
	
	public static function factory($attr=array())
	{
	    return new PHistoryModel($attr);
	}
}
?>