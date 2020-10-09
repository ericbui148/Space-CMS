<?php
namespace App\Models;

class ItemSortModel extends AppModel
{
    const TYPE_WIDGET_PRODUCTS = 1;
    const TYPE_PRODUCT_CATEGORY = 2;
    const TYPE_WIDGET_ARTICLES = 3;
    const TYPE_WDGET_PAGES = 4;
    const TYPE_ARTICLE_CATEGORY = 5;
    const TYPE_PAGE_CATEGORY = 6;
    
    protected $primaryKey = 'id';
    
    protected $table = 'item_sorts';
    
    protected $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'foreign_type_id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'foreign_id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'type', 'type' => 'tinyint', 'default' => ':NULL'),
        array('name' => 'sort', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
        array('name' => 'updated', 'type' => 'datetime', 'default' => ':NOW()')
    );
    
    public static function factory($attr=array())
    {
        return new self($attr);
    }
}