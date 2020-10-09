<?php
namespace App\Models;

class RouterModel extends AppModel
{
    const TYPE_ARTICLE = 1;
    const TYPE_PAGE = 2;
    const TYPE_ARTICLE_CATEGORY = 3;
    const TYPE_PAGE_CATEGORY = 4;
    const TYPE_PRODUCT = 5;
    const TYPE_PRODUCT_CATEGORY = 6;
    const TYPE_TAG = 9;

	protected $primaryKey = 'id';
	
	protected $table = 'routers';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'url', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'hash', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'controller', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'action', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'params', 'type' => 'varchar', 'default' => 'T'),
        array('name' => 'type', 'type' => 'tinyint', 'default' => ':NULL'),
        array('name' => 'foreign_id', 'type' => 'int', 'default' => ':NULL'),
	    array('name' => 'locale_id', 'type' => 'tinyint', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new RouterModel($attr);
	}
}
?>