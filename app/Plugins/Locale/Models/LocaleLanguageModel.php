<?php
namespace App\Plugins\Locale\Models;

class LocaleLanguageModel extends LocaleAppModel
{
	protected $primaryKey = 'iso';
	
	protected $table = 'plugin_locale_languages';
	
	protected $schema = array(
		array('name' => 'iso', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'title', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'file', 'type' => 'varchar', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new LocaleLanguageModel($attr);
	}
}
?>