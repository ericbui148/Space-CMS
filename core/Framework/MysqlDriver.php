<?php
namespace Core\Framework;

class MysqlDriver extends DbDriver 
{
	protected $driver = 'mysql';
	private $useSetNames;
	public function affectedRows() {
		return mysql_affected_rows ();
	}
	public function connect() {
		if ($this->persistent) {
			$this->connectionId = @mysql_pconnect ( is_null ( $this->socket ) ? ($this->hostname . (! is_null ( $this->port ) ? ":" . $this->port : NULL)) : $this->hostname . ":" . $this->socket, $this->username, $this->password );
		} else {
			$this->connectionId = @mysql_connect ( is_null ( $this->socket ) ? ($this->hostname . (! is_null ( $this->port ) ? ":" . $this->port : NULL)) : $this->hostname . ":" . $this->socket, $this->username, $this->password );
		}
		return $this->connectionId;
	}
	public function connectError() {
		return $this->error ();
	}
	public function error() {
		return mysql_error ( $this->connectionId !== false ? $this->connectionId : NULL );
	}
	public function escapeString($value) {
		if (get_magic_quotes_gpc ()) {
			$value = stripslashes ( $value );
		}
		return function_exists ( 'mysql_real_escape_string' ) ? mysql_real_escape_string ( $value ) : mysql_escape_string ( $value );
	}
	public function fetchArray() {
		if (is_resource ( $this->result )) {
			$this->data = array ();
			while ( $row = mysql_fetch_array ( $this->result ) ) {
				$this->data [] = $row;
			}
			$this->freeResult ();
		}
		return $this;
	}
	public function fetchAssoc() {
		if (is_resource ( $this->result )) {
			$this->data = array ();
			while ( $row = mysql_fetch_assoc ( $this->result ) ) {
				$this->data [] = $row;
			}
			$this->freeResult ();
		}
		return $this;
	}
	public function fetchRow() {
		if (is_resource ( $this->result )) {
			$this->data = array ();
			$this->data = mysql_fetch_row ( $this->result );
		}
		return $this;
	}
	public function freeResult() {
		if (is_resource ( $this->result )) {
			return mysql_free_result ( $this->result );
		}
		return false;
	}
	public function insertId() {
		return mysql_insert_id ();
	}
	public function numRows() {
		if (is_resource ( $this->result )) {
			return @mysql_num_rows ( $this->result );
		}
		return false;
	}
	public function query($query) {
		$this->result = mysql_query ( $query, $this->connectionId );
		return $this->result;
	}
	public function selectDb() {
		return mysql_select_db ( $this->database, $this->connectionId );
	}
	protected function setCharset($charset, $collation) {
		if (! isset ( $this->useSetNames )) {
			$this->useSetNames = (version_compare ( PHP_VERSION, '5.2.3', '>=' ) && version_compare ( mysql_get_server_info ( $this->connectionId ), '5.0.7', '>=' )) ? FALSE : TRUE;
		}
		if ($this->useSetNames === TRUE) {
			return $this->query ( "SET NAMES '" . $this->escapeString ( $charset ) . "' COLLATE '" . $this->escapeString ( $collation ) . "'" );
		} else {
			return @mysql_set_charset ( $charset, $this->connectionId );
		}
	}
}
?>