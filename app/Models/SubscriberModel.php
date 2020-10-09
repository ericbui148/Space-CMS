<?php
namespace App\Models;

class SubscriberModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'subscribers';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'first_name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'last_name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'email', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'phone', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'website', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'gender', 'type' => 'enum', 'default' => ':NULL'),
		array('name' => 'age', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'birthday', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'address', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'city', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'state', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'country_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'zip', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'company_name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'subscribed', 'type' => 'enum', 'default' => 'F'),
		array('name' => 'ip', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'modified', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()')
	);
	
	public $i18n = array('name');
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>