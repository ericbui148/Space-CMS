<?php
namespace App\Models;

class StockAttributeModel extends AppModel
{
	protected $primaryKey = null;
	
	protected $table = 'stocks_attributes';
	
	protected $schema = array(
		array('name' => 'stock_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'product_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'attribute_parent_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'attribute_id', 'type' => 'int', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>