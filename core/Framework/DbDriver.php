<?php
namespace Core\Framework;

class DbDriver 
{
	protected $charset = 'utf8';
	protected $collation = 'utf8_general_ci';
	protected $driver = 'mysqli';
	protected $connectionId = false;
	protected $data = array ();
	protected $database = null;
	protected $hostname = "localhost";
	protected $password = null;
	protected $persistent = false;
	protected $port = "3306";
	protected $result;
	protected $socket = null;
	protected $username = null;
	public function __construct($params = array()) {
		if (is_array ( $params )) {
			foreach ( $params as $key => $val ) {
				$this->$key = $val;
			}
		}
	}

	public function getData($index = NULL) {

		return is_null ( $index ) ? $this->data : $this->data [$index];
	}

	public function getResult() {

		return $this->result;
	}

	public function init() {
		if (is_resource ( $this->connectionId ) || is_object ( $this->connectionId )) {
			return TRUE;
		}
		if (! $this->connect ()) {
			return FALSE;
		}
		if ($this->database != '' && $this->driver == 'mysql') {
			if (! $this->selectDb ()) {
				return FALSE;
			}
		}
		if (! $this->setCharset ( $this->charset, $this->collation )) {
			return FALSE;
		}
		return TRUE;
	}
}
?>