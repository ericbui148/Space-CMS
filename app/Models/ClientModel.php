<?php
namespace App\Models;

class ClientModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'clients';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'email', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'password', 'type' => 'blob', 'default' => ':NULL', 'encrypt' => 'AES'),
		array('name' => 'client_name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'phone', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'url', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'last_login', 'type' => 'datetime', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'T')
	);
	
	protected $validate = array(
		'rules' => array(
			'email' => array(
				'Email' => true,
				'Required' => true,
				'NotEmpty' => true
			),
			'password' => array(
				'Required' => true,
				'NotEmpty' => true
			)
		)
	);

	protected $rules = array(
		'client_name' => array(
			'Required' => true,
			'NotEmpty' => true
		),
		'phone' => array(
			'Required' => true,
			'NotEmpty' => true
		),
		'url' => array(
			'Required' => true,
			'NotEmpty' => true,
			'Url' => true
		)
	);
	
	public function beforeValidate($option_arr)
	{
		if (!is_array($option_arr))
		{
			return FALSE;
		}
		
		if (isset($option_arr['o_bf_c_name']) && (int) $option_arr['o_bf_c_name'] === 3)
		{
			$this->addRule('client_name');
		}
		
		if (isset($option_arr['o_bf_c_phone']) && (int) $option_arr['o_bf_c_phone'] === 3)
		{
			$this->addRule('phone');
		}
		
		if (isset($option_arr['o_bf_c_url']) && (int) $option_arr['o_bf_c_url'] === 3)
		{
			$this->addRule('url');
		}
		
		return $this;
	}
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
}
?>