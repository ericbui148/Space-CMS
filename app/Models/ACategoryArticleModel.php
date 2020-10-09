<?php
namespace App\Models;

class ACategoryArticleModel extends AppModel
{
	protected $primaryKey = null;
	
	protected $table = 'acategories_articles';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'article_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'category_id', 'type' => 'int', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>