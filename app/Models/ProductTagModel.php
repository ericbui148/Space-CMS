<?php
namespace App\Models;

class ProductTagModel extends AppModel
{
    protected $table = 'products_tags';
    
    protected $schema = array(
        array('name' => 'product_id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'tag_id', 'type' => 'int', 'default' => ':NULL')
    );
    
    public static function factory($attr=array())
    {
        return new ProductTagModel($attr);
    }
}
?>