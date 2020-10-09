<?php
namespace App\Models;

class ArticleTagModel extends AppModel
{	
	protected $table = 'articles_tags';
	
	protected $schema = array(
		array('name' => 'article_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'tag_id', 'type' => 'int', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new ArticleTagModel($attr);
	}
}
?>