<?php
namespace App\Plugins\Invoice\Models;

class InvoiceModel extends InvoiceAppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'plugin_invoice';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'uuid', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'order_id', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'foreign_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'issue_date', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'due_date', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'modified', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => ':NULL'),
		array('name' => 'payment_method', 'type' => 'enum', 'default' => ':NULL'),
		array('name' => 'cc_type', 'type' => 'blob', 'default' => ':NULL', 'encrypt' => 'AES'),
		array('name' => 'cc_num', 'type' => 'blob', 'default' => ':NULL', 'encrypt' => 'AES'),
		array('name' => 'cc_exp_month', 'type' => 'blob', 'default' => ':NULL', 'encrypt' => 'AES'),
		array('name' => 'cc_exp_year', 'type' => 'blob', 'default' => ':NULL', 'encrypt' => 'AES'),
		array('name' => 'cc_code', 'type' => 'blob', 'default' => ':NULL', 'encrypt' => 'AES'),
		array('name' => 'txn_id', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'processed_on', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'subtotal', 'type' => 'decimal', 'default' => 0),
		array('name' => 'discount', 'type' => 'decimal', 'default' => 0),
		array('name' => 'tax', 'type' => 'decimal', 'default' => 0),
		array('name' => 'shipping', 'type' => 'decimal', 'default' => 0),
		array('name' => 'total', 'type' => 'decimal', 'default' => 0),
		array('name' => 'paid_deposit', 'type' => 'decimal', 'default' => 0),
		array('name' => 'amount_due', 'type' => 'decimal', 'default' => 0),
		array('name' => 'currency', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'notes', 'type' => 'text', 'default' => ':NULL'),
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
		array('name' => 'b_billing_address', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'b_company', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'b_name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'b_address', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'b_street_address', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'b_country', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'b_city', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'b_state', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'b_zip', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'b_phone', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'b_fax', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'b_email', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'b_url', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 's_shipping_address', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 's_company', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 's_name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 's_address', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 's_street_address', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 's_country', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 's_city', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 's_state', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 's_zip', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 's_phone', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 's_fax', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 's_email', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 's_url', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 's_date', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 's_terms', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 's_is_shipped', 'type' => 'tinyint', 'default' => 0)
	);
	
	protected $validate = array(
		'rules' => array(
			'uuid' => array(
				'ActionAlphaNumeric' => true,
				'ActionNotEmpty' => true,
				'ActionRequired' => true
			),
			'order_id' => array(
				'ActionAlphaNumeric' => true,
				'ActionNotEmpty' => true,
				'ActionRequired' => true
			),
			'foreign_id' => array(
				'ActionNumeric' => true,
				'ActionNotEmpty' => true,
				'ActionRequired' => true
			),
			'issue_from' => array(
				'rule' => array('ActionDate', 'ymd', '/\d{4}-\d{2}-\d{2}/'),
				'ActionRequired' => true,
				'ActionNotEmpty' => true
			),
			'due_to' => array(
				'rule' => array('ActionDate', 'ymd', '/\d{4}-\d{2}-\d{2}/'),
				'ActionRequired' => true,
				'ActionNotEmpty' => true
			)
		)
	);
	
	public static function factory($attr=array())
	{
		return new InvoiceModel($attr);
	}
	
	public function Setup()
	{
		
	}
	
	public function getInvoiceID()
	{
		$id = 1;
		$arr = $this
			->reset()
			->limit(1)
			->orderBy("id DESC")
			->findAll()
			->getData();
		if(!empty($arr))
		{
			$id = $arr[0]['id'] + 1;
		}
	
		return $id;
	}
}
?>