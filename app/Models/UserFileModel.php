<?php
namespace App\Models;

class UserFileModel extends AppModel
{
	protected $table = 'users_files';
	
	protected $schema = array(
		array('name' => 'user_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'file_id', 'type' => 'int', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new UserFileModel($attr);
	}
}
?>