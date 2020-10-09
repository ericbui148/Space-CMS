<?php
namespace App\Models;

class AHistoryModel extends AppModel
{
	const ACTION_ADD = 1;
	const ACTION_UPDATE = 2;
	const ACTION_DELETE = 3;
	
	protected $primaryKey = 'id';
	
	protected $table = 'ahistories';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'user_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'article_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'modified', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'ip', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'action', 'type' => 'tinyint', 'default' => ':NULL')
	);
	
	public $i18n = array('article_name', 'article_content');
	
	public static function factory($attr=array())
	{
		return new AHistoryModel($attr);
	}
}
?>