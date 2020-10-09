<?php
namespace App\Models;

class VoucherProductModel extends AppModel
{
	protected $primaryKey = null;
	
	protected $table = 'vouchers_products';
	
	protected $schema = array(
		array('name' => 'voucher_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'product_id', 'type' => 'int', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>