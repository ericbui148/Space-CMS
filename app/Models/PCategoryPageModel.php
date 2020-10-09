<?php
namespace App\Models;

class PCategoryPageModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'pcategories_pages';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'page_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'category_id', 'type' => 'int', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>