<?php
namespace App\Plugins\Invoice\Models;

class InvoiceItemModel extends InvoiceAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'plugin_invoice_items';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'invoice_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'tmp', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'description', 'type' => 'tinytext', 'default' => ':NULL'),
		array('name' => 'qty', 'type' => 'decimal', 'default' => ':NULL'),
		array('name' => 'unit_price', 'type' => 'decimal', 'default' => ':NULL'),
		array('name' => 'amount', 'type' => 'decimal', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new InvoiceItemModel($attr);
	}
}
?>