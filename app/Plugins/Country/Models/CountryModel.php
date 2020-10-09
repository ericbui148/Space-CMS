<?php
namespace App\Plugins\Country\Models;

class CountryModel extends CountryAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'plugin_country';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'alpha_2', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'alpha_3', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T')
	);
	
	protected $i18n = array('name');
	
	public static function factory($attr=array())
	{
		return new CountryModel($attr);
	}
}
?>