<?php
namespace App\Models;

class ProductModel extends AppModel
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    
	protected $primaryKey = 'id';

	protected $table = 'products';

	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'sku', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'tinyint', 'default' => ':NULL'),
		array('name' => 'digital_file', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'digital_name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'digital_expire', 'type' => 'time', 'default' => ':NULL'),
		array('name' => 'is_featured', 'type' => 'tinyint', 'default' => 0),
		array('name' => 'is_digital', 'type' => 'tinyint', 'default' => 0)
	);
	
	protected $i18n = array('name', 'short_desc', 'full_desc');
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>