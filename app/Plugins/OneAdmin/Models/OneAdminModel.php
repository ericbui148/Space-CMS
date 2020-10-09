<?php
namespace App\Plugins\OneAdmin\Models;

use App\Models\AppModel;

class OneAdminModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'plugin_one_admin';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'url', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'email', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'password', 'type' => 'blob', 'default' => ':NULL', 'encrypt' => 'AES')
	);
	
	public static function factory($attr=array())
	{
		return new OneAdminModel($attr);
	}
	
	public function Setup()
	{

	}
}
?>