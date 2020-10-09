<?php
namespace App\Plugins\Locale\Models;

class LocaleModel extends LocaleAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'plugin_locale';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'language_iso', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'sort', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'is_default', 'type' => 'tinyint', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new LocaleModel($attr);
	}
	
	public function Setup()
	{

	}
}
?>