<?php
namespace App\Models;

class CmsSettingModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'cms_settings';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'title', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'meta_description', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'meta_keywords', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'support_widget', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'google_site_map', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'google_verify', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'google_analytics', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'favicon', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'is_maintain', 'type' => 'enum', 'default' => 'F'),
		array('name' => 'maintain_url', 'type' => 'varchar', 'default' => ':NULL'),
	    array('name' => 'copyright', 'type' => 'varchar', 'default' => ':NULL'),
	    array('name' => 'logo_text', 'type' => 'varchar', 'default' => ':NULL')
	);
	
	public $i18n = array();
	
	public static function factory($attr=array())
	{
		return new CmsSettingModel($attr);
	}
}
?>