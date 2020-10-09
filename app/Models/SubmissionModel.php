<?php
namespace App\Models;

class SubmissionModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'submissions';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'form_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'ip', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'submitted_date', 'type' => 'datetime', 'default' => ':NOW()')
	);
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>