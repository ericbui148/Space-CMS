<?php
namespace App\Models;

class BannerModel extends AppModel
{
	const POSITION_LEFT = 1;
	const POSITION_BOTTOM = 2;

	protected $primaryKey = 'id';
	
	protected $table = 'banners';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'name', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'position', 'type' => 'tinyint', 'default' => ':NULL'),
		array('name' => 'link', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'file_name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'file_path', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'hash', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'size', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'minetype', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
        array('name' => 'modified', 'type' => 'datetime', 'default' => ':NOW()')
	);
	
	public static function factory($attr=array())
	{
		return new BannerModel($attr);
	}
}
?>