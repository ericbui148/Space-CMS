<?php
namespace Core\Framework;

class MysqliDriver extends DbDriver 
{
	protected $driver = 'mysqli';
	private $useSetNames;
	
	public function affectedRows() {
	
		return mysqli_affected_rows ( $this->connectionId !== FALSE ? $this->connectionId : NULL );
	}
	
	public function connect() {
		if(@MYSQL_SSL_SECURE == 1) {
			$db = mysqli_init();      
			$db->ssl_set(ROOT_PATH . 'app/config/access_key/client-key.pem', ROOT_PATH . 'app/config/access_key/client-cert.pem', ROOT_PATH . 'app/config/access_key/server-ca.pem', NULL, NULL);
			mysqli_real_connect ($db, $this->hostname, $this->username, $this->password, $this->database, 3306, NULL, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);
			$this->connectionId =$db;
		} else {
			$this->connectionId = @mysqli_connect ( $this->hostname, $this->username, $this->password, $this->database, ! is_null ( $this->port ) ? $this->port : NULL, ! is_null ( $this->socket ) ? $this->socket : NULL );
		}
		return $this->connectionId;
	}
	
	public function connectError() {
	
		return mysqli_connect_error ();
	}
	
	public function error() {
	
		return mysqli_error ( $this->connectionId );
	}
	
	public function escapeString($value) {
	
		if (get_magic_quotes_gpc ()) {
			$value = stripslashes ( $value );
		}
		if (function_exists ( 'mysqli_real_escape_string' ) && is_object ( $this->connectionId )) {
			return mysqli_real_escape_string ( $this->connectionId, $value );
		} elseif (function_exists ( 'mysqli_real_escape_string' )) {
			return mysql_real_escape_string ( $value );
		} else {
			return mysql_escape_string ( $value );
		}
	}

	public function fetchArray() {

		if (is_object ( $this->result )) {
			$this->data = array ();
			while ( $row = mysqli_fetch_array ( $this->result ) ) {
				$this->data [] = $row;
			}
			$this->freeResult ();
		}
		return $this;
	}

	public function fetchAssoc() {

		if (is_object ( $this->result )) {
			$this->data = array ();
			while ( $row = mysqli_fetch_assoc ( $this->result ) ) {
				$this->data [] = $row;
			}
			$this->freeResult ();
		}
		return $this;
	}
	
	public function fetchRow() {

		if (is_object ( $this->result )) {
			$this->data = array ();
			$this->data = mysqli_fetch_row ( $this->result );
		}
		return $this;
	}

	public function freeResult() {

		if (is_object ( $this->result )) {
			mysqli_free_result ( $this->result );
			return TRUE;
		}
		return FALSE;
	}

	public function insertId() {

		return mysqli_insert_id ( $this->connectionId );
	}

	public function numRows() {

		if (is_object ( $this->result )) {
			return @mysqli_num_rows ( $this->result );
		}
		return FALSE;
	}

	public function query($query) {

		$this->result = mysqli_query ( $this->connectionId, $query );
		return $this->result;
	}

	public function selectDb() {
		return mysqli_select_db ( $this->connectionId, $this->database );
	}

	protected function setCharset($charset, $collation) {

		if (! isset ( $this->useSetNames )) {
			$this->useSetNames = version_compare ( mysqli_get_server_info ( $this->connectionId ), '5.0.7', '>=' ) ? FALSE : TRUE;
		}
		if ($this->useSetNames === TRUE) {
			return $this->query ( "SET NAMES '" . $this->escapeString ( $charset ) . "' COLLATE '" . $this->escapeString ( $collation ) . "'" );
		} else {
			return @mysqli_set_charset ( $this->connectionId, $charset );
		}
	}
}
?>