<?php
namespace App\Models;

class MenuModel extends AppModel
{
	const STATUS_ACTIVE = 'T';
	const STATUS_INACTIVE = 'F';
	
	protected $primaryKey = 'id';
	
	protected $table = 'menus';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'description', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => ':NULL'),
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'modified', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'uuid_code', 'type' => 'varchar', 'default' => ':NULL')
	);
	
	protected $validate = array(
		'rules' => array(
			'status' => array(
				'Required' => true
			),
			'name' => array(
					'Required' => true
			)	
		)
	);
	
	public $i18n = array();
	
	public static function factory($attr=array())
	{
		return new MenuModel($attr);
	}
}
?>