<?php
namespace App\Models;

class RoleModel extends AppModel
{
	const ROLE_ADMIN = 1;
	const ROLE_EDITOR = 2;
	protected $primaryKey = 'id';
	
	protected $table = 'roles';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'tinyint', 'default' => ':NULL'),
		array('name' => 'role', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T')
	);
	
	public static function factory($attr=array())
	{
		return new RoleModel($attr);
	}
}
?>