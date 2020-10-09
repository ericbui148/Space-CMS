<?php
namespace App\Models;

class UserPageModel extends AppModel
{
	protected $table = 'users_pages';
	
	protected $schema = array(
		array('name' => 'user_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'page_id', 'type' => 'int', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new UserPageModel($attr);
	}
}
?>