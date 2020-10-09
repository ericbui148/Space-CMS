<?php
namespace App\Models;

class ArticleModel extends AppModel
{
	const STATUS_ACTIVE = 'T';
	const STATUS_INACTIVE = 'F';

	
	protected $primaryKey = 'id';
	
	protected $table = 'articles';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'avatar_file', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'on_date', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'modified', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T'),
		array('name' => 'uuid_code', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'template', 'type' => 'varchar', 'default' => ':NULL'),
	    array('name' => 'slider_id', 'type' => 'int', 'default' => ':NULL')
	);
	
	public $i18n = array('article_name', 'sub_title','article_content', 'article_short_description', 'meta_title', 'meta_keyword', 'meta_description');
	
	public static function factory($attr = [])
	{
		return new ArticleModel($attr);
	}
}
?>