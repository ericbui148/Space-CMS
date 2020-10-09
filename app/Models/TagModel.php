<?php
namespace App\Models;

class TagModel extends AppModel
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    
	protected $primaryKey = 'id';
	
	protected $table = 'tags';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
	    array('name' => 'status', 'type' => 'tinyint', 'default' => 1),
	);
	
	public $i18n = ['name'];
	
	public static function factory($attr=array())
	{
		return new TagModel($attr);
	}
}
?>