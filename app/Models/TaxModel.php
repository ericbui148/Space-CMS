<?php
namespace App\Models;

class TaxModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'taxes';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'shipping', 'type' => 'decimal', 'default' => ':NULL'),
		array('name' => 'free', 'type' => 'decimal', 'default' => ':NULL'),
		array('name' => 'tax', 'type' => 'decimal', 'default' => ':NULL')
	);
	
	protected $i18n = array('location');
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>