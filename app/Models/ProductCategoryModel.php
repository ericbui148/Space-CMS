<?php
namespace App\Models;

class ProductCategoryModel extends AppModel
{
	protected $primaryKey = null;
	
	protected $table = 'products_categories';
	
	protected $schema = array(
		array('name' => 'product_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'category_id', 'type' => 'int', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>