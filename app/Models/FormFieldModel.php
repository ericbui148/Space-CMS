<?php
namespace App\Models;

class FormFieldModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'form_fields';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'form_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'order_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'type', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'label', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'default_value', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'send_confirmation', 'type' => 'enum', 'default' => 'T'),
		array('name' => 'hint', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'size', 'type' => 'int', 'default' => '100'),
		array('name' => 'maxlength', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'columns', 'type' => 'varchar', 'default' => '100'),
		array('name' => 'rows', 'type' => 'varchar', 'default' => '150'),
		array('name' => 'option_data', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'max_file_size', 'type' => 'varchar', 'default' => '1'),
		array('name' => 'extensions', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'allow_mulitple', 'type' => 'enum', 'default' => 'F'),
		array('name' => 'validate_image', 'type' => 'enum', 'default' => 'F'),
		array('name' => 'heading_size', 'type' => 'enum', 'default' => 'medium'),
		array('name' => 'required', 'type' => 'enum', 'default' => ':NULL'),
		array('name' => 'validation', 'type' => 'enum', 'default' => 'none'),
		array('name' => 'error_required', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'error_email', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'error_validation', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'error_maxsize', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'error_extensions', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'error_incorrect', 'type' => 'varchar', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>