<?php
namespace App\Plugins\Gallery\Models;

class GalleryModel extends GalleryAppModel
{
	const SOURCE_TYPE_PRODUCT_IMAGE = 1;
	const SOURCE_TYPE_PRODUCT_SHORT_DESCRIPTION = 2;
	const SOURCE_TYPE_PRODUCT_DESCRIPTION = 3;
	const SOURCE_TYPE_SLIDER = 4;
	const SOURCE_TYPE_GALLERY = 5;
	const SOURCE_TYPE_ARTICLE_SHORT_DESCRIPTION = 6;
	const SOURCE_TYPE_ARTICLE_DESCRIPTION = 7;
	const SOURCE_TYPE_PAGE_SHORT_DESCRIPTION = 8;
	const SOURCE_TYPE_PAGE_DESCRIPTION = 9;
	const SOURCE_TYPE_WIDGET = 10;
	const SOURCE_TYPE_FILE_MANAGER = 11;
	
    protected $primaryKey = 'id';
	
	protected $table = 'plugin_gallery';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'foreign_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'hash', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'mime_type', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'small_path', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'small_size', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'small_width', 'type' => 'smallint', 'default' => ':NULL'),
		array('name' => 'small_height', 'type' => 'smallint', 'default' => ':NULL'),
		array('name' => 'medium_path', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'medium_size', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'medium_width', 'type' => 'smallint', 'default' => ':NULL'),
		array('name' => 'medium_height', 'type' => 'smallint', 'default' => ':NULL'),
		array('name' => 'large_path', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'large_size', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'large_width', 'type' => 'smallint', 'default' => ':NULL'),
		array('name' => 'large_height', 'type' => 'smallint', 'default' => ':NULL'),
		array('name' => 'source_path', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'source_size', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'source_width', 'type' => 'smallint', 'default' => ':NULL'),
		array('name' => 'source_height', 'type' => 'smallint', 'default' => ':NULL'),
		array('name' => 'name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'alt', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'watermark', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'link', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'sort', 'type' => 'int', 'default' => ':NULL'),
	    array('name' => 'source_type', 'type' => 'tinyint', 'default' => ':NULL'),
	);
	
	public $i18n = array('title','description');
	
	public static function factory($attr=array())
	{
		return new GalleryModel($attr);
	}
	
	public function Setup()
	{
		
	}
}
?>