<?php
namespace App\Models;

class AddressModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'addresses';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'client_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'country_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'state', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'city', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'zip', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'address_1', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'address_2', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'is_default_shipping', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'is_default_billing', 'type' => 'tinyint', 'default' => 0)
	);
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>