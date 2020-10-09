<?php
namespace App\Models;

class AttributeModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'attributes';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'product_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'parent_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'order_group', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'order_item', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'hash', 'type' => 'varchar', 'default' => ':NULL')
	);
	
	protected $i18n = array('name');
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
	
	public function getLastOrder($product_id)
	{
		$last_order = 0;
		$arr = $this->reset()
			->where('t1.product_id', $product_id)
			->orderBy('t1.`order_group` DESC')
			->limit(1)
			->findAll()
			->getData();
		if(!empty($arr))
		{
			$last_order = $arr[0]['order_group'] + 1;
		}
		return $last_order;
	}
}
?>