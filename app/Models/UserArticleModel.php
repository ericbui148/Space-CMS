<?php
namespace App\Models;

class UserArticleModel extends AppModel
{
	protected $table = 'users_articles';
	
	protected $schema = array(
		array('name' => 'user_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'article_id', 'type' => 'int', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new UserArticleModel($attr);
	}
}
?>