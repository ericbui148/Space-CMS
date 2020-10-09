<?php
namespace App\Models;

class PageTagModel extends AppModel
{	
	protected $table = 'pages_tags';
	
	protected $schema = array(
		array('name' => 'page_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'tag_id', 'type' => 'int', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
	    return new PageTagModel($attr);
	}
}
?>