<?php
namespace App\Models;

class FileModel extends AppModel
{
    protected $primaryKey = 'id';
    
    protected $table = 'files';
    
    protected $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'user_id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'file_path', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'file_name', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'mime_type', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'hash', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'type', 'type' => 'tinyint', 'default' => ':NULL'),
        array('name' => 'size', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
        array('name' => 'status', 'type' => 'enum', 'default' => 'T')
    );
    
    public static function factory($attr=array())
    {
        return new FileModel($attr);
    }
}
?>