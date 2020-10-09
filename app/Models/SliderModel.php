<?php
namespace App\Models;

class SliderModel extends AppModel
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 0;
	
	protected $primaryKey = 'id';
	
	protected $table = 'sliders';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'description', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'type', 'type' => 'enum', 'default' => 'G'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T'),
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'modified', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'uuid_code', 'type' => 'varchar', 'default' => ':NULL')			
	);
	
	protected $validate = array(
		'rules' => array(
			'status' => array(
				'Required' => true
			)
		)
	);
	
	public $i18n = array();
	
	public static function factory($attr=array())
	{
		return new SliderModel($attr);
	}
}
?>