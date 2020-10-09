<?php
namespace App\Models;

class FormModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'forms';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'form_title', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'form_width', 'type' => 'int', 'default' => '550'),
		array('name' => 'label_width', 'type' => 'int', 'default' => '80'),
		array('name' => 'label_position', 'type' => 'enum', 'default' => 'right'),
		array('name' => 'date_format', 'type' => 'varchar', 'default' => 'd-m-Y'),
		array('name' => 'confirm_options', 'type' => 'enum', 'default' => 'message'),
		array('name' => 'confirm_message', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'thankyou_page', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'send_to', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'subject', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'auto_subject', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'auto_message', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'email_type', 'type' => 'enum', 'default' => 'html'),
		array('name' => 'captcha_type', 'type' => 'enum', 'default' => 'string'),
		array('name' => 'reject_links', 'type' => 'enum', 'default' => 'F'),
		array('name' => 'block_words', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'font_family', 'type' => 'varchar', 'default' => 'Arial'),
		array('name' => 'font_size', 'type' => 'int', 'default' => '12'),
		array('name' => 'font_color', 'type' => 'varchar', 'default' => '000000'),
		array('name' => 'background_color', 'type' => 'varchar', 'default' => 'FFFFFF'),
		array('name' => 'field_background_color', 'type' => 'varchar', 'default' => 'FFFFFF'),
		array('name' => 'button_background_color', 'type' => 'varchar', 'default' => 'FFFFFF'),
		array('name' => 'button_hover_background_color', 'type' => 'varchar', 'default' => 'e6e6e6'),
		array('name' => 'button_border_color', 'type' => 'varchar', 'default' => 'CCCCCC'),
		array('name' => 'button_hover_border_color', 'type' => 'varchar', 'default' => 'adadad'),
		array('name' => 'modified', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T')
	);
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>