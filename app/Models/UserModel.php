<?php
namespace App\Models;

class UserModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'users';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'role_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'avatar', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'email', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'password', 'type' => 'blob', 'default' => ':NULL', 'encrypt' => 'AES'),
		array('name' => 'name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'phone', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'last_login', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T'),
		array('name' => 'is_active', 'type' => 'enum', 'default' => 'F'),
		array('name' => 'ip', 'type' => 'varchar', 'default' => ':NULL')
	);
	
	protected $validate = array(
		'rules' => array(
			'role_id' => array(
				'Numeric' => true,
				'Required' => true
			),
			'email' => array(
				'Email' => true,
				'Required' => true,
				'NotEmpty' => true
			),
			'password' => array(
				'Required' => true,
				'NotEmpty' => true
			),
			'name' => array(
				'Required' => true,
				'NotEmpty' => true
			),
			'status' => 'Required'
		)
	);

	public static function factory($attr=array())
	{
		return new UserModel($attr);
	}
}
?>