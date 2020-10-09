<?php
namespace App\Models;

class SubmissionDetailModel extends AppModel
{
	protected $table = 'submission_details';
	
	protected $schema = array(
		array('name' => 'form_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'submission_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'field_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'type', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'value', 'type' => 'text', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>