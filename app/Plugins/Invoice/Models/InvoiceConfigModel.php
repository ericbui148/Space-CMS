<?php
namespace App\Plugins\Invoice\Models;

class InvoiceConfigModel extends InvoiceAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'plugin_invoice_config';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'y_logo', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'y_company', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'y_name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'y_street_address', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'y_country', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'y_city', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'y_state', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'y_zip', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'y_phone', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'y_fax', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'y_email', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'y_url', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'y_template', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'p_accept_payments', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'p_accept_paypal', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'p_accept_authorize', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'p_accept_creditcard', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'p_accept_cash', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'p_accept_bank', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'p_paypal_address', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'p_authorize_tz', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'p_authorize_key', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'p_authorize_mid', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'p_authorize_hash', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'p_bank_account', 'type' => 'tinytext', 'default' => ':NULL'),
		array('name' => 'si_include', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_shipping_address', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_company', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_name', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_address', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_street_address', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_city', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_state', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_zip', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_phone', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_fax', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_email', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_url', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_date', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_terms', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_is_shipped', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'si_shipping', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'o_booking_url', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'o_qty_is_int', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'o_use_qty_unit_price', 'type' => 'tinyint', 'default' => 1)
	);
	
	public static function factory($attr=array())
	{
		return new InvoiceConfigModel($attr);
	}
}
?>