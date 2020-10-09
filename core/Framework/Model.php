<?php
namespace Core\Framework;

use Core\Framework\Components\ToolkitComponent;
use Core\Framework\Components\ValidationComponent;

class Model extends Objects {
	private $affectedRows = - 1;
	private $arBatch = array ();
	private $arBatchFields = array ();
	private $arDebug = FALSE;
	private $arDistinct = FALSE;
	private $arFrom = NULL;
	private $arGroupBy = NULL;
	private $arHaving = NULL;
	private $arIndex = NULL;
	private $arJoin = array ();
	private $arOffset = NULL;
	private $arOrderBy = NULL;
	private $arRowCount = NULL;
	private $arSelect = array ();
	private $arWhere = array ();
	private $arWhereIn = array ();
	private $assocTypes = array (
			'hasOne',
			'hasMany',
			'belongsTo',
			'hasAndBelongsToMany' 
	);
	protected $belongsTo = NULL;
	private $data = array ();
	private $dbo = NULL;
	private $errors = array ();
	protected $hasAndBelongsToMany = NULL;
	protected $hasMany = NULL;
	protected $hasOne = NULL;
	private $initialized = FALSE;
	private $insertId = FALSE;
	private $joinArr = array (
			'LEFT',
			'RIGHT',
			'OUTER',
			'INNER',
			'LEFT OUTER',
			'RIGHT OUTER',
			'CROSS',
			'NATURAL',
			'STRAIGHT' 
	);
	private $prefix = NULL;
	protected $primaryKey = NULL;
	protected $schema = array ();
	private $scriptPrefix = NULL;
	private $statement = NULL;
	protected $table = NULL;
	protected $i18n = array ();
	private $transactionStarted = false;
	protected $validate = array ();
	public function __construct($attr = array()) {
		if (defined ( 'PREFIX' )) {
			$this->setPrefix ( PREFIX );
		}
		if (defined ( 'SCRIPT_PREFIX' )) {
			$this->scriptPrefix = SCRIPT_PREFIX;
		}
		$registry = Registry::getInstance ();
		if ($registry->is ( 'dbo' )) {
			$this->dbo = $registry->get ( 'dbo' );
			$this->initialized = TRUE;
		} else {
			$driver = function_exists ( 'mysqli_connect' ) ? 'MysqliDriver' : 'MysqlDriver';
			$params = array (
					'hostname' => HOST,
					'username' => USER,
					'password' => PASS,
					'database' => DB 
			);
			if (strpos ( $params ['hostname'], ":" ) !== FALSE) {
				list ( $hostname, $value ) = explode ( ":", $params ['hostname'], 2 );
				if (preg_match ( '/\D/', $value )) {
					$params ['socket'] = $value;
				} else {
					$params ['port'] = $value;
				}
				$params ['hostname'] = $hostname;
			}
			$this->dbo = Singleton::getInstance ( $driver, $params );
			$this->initialized = $this->dbo->init ();
			if (! $this->initialized) {
				die ( $this->dbo->connectError () );
			}
			$registry->set ( 'dbo', $this->dbo );
		}
		$this->setAttributes ( $attr );
		return $this;
	}
	public function afterDelete() {
		return true;
	}
	public function afterFind() {
		return true;
	}
	public function afterSave() {
		return true;
	}
	public function autocommit($value = 0) {
		if (! in_array ( $value, array (
				0,
				1 
		) ))
			return false;
		if (! $this->transactionStarted && $this->prepare ( "SET autocommit = " . $value )->exec ()->dbo->getResult ()) {
			$this->transactionStarted = true;
			return true;
		}
		return false;
	}
	public function beforeDelete() {
		return true;
	}
	public function beforeFind() {
		return true;
	}
	public function beforeSave() {
		return true;
	}
	public function begin() {
		if (! $this->transactionStarted && $this->prepare ( "START TRANSACTION" )->exec ()->dbo->getResult ()) {
			$this->transactionStarted = true;
			return true;
		}
		return false;
	}
	private function buildSave($type = NULL) {
		$save = array ();
		$data = $this->getAttributes ();
		foreach ( $this->schema as $field ) {
			if (isset ( $data [$field ['name']] )) {
				if (! is_array ( $data [$field ['name']] )) {
					if (! isset ( $field ['encrypt'] )) {
						$save [] = sprintf ( "`%s` = %s", $field ['name'], preg_match ( '/^:[a-zA-Z]{1}.*/', $data [$field ['name']] ) ? substr ( $data [$field ['name']], 1 ) : $this->escapeValue ( $data [$field ['name']] ) );
					} else {
						switch (strtoupper ( $field ['encrypt'] )) {
							case 'AES' :
								$save [] = sprintf ( "`%s` = AES_ENCRYPT(%s, %s)", $field ['name'], $this->escapeValue ( $data [$field ['name']] ), $this->escapeValue ( SALT ) );
								break;
						}
					}
				}
			} else {
				if (! is_null ( $type ) && $type == 'insert') {
					$save [] = "`" . $field ['name'] . "` = " . (strpos ( $field ['default'], ":" ) === 0 ? substr ( $field ['default'], 1 ) : "'" . $this->escape ( $field ['default'], null, $field ['type'] ) . "'");
				}
			}
		}
		return $save;
	}
	private function buildSelect() {
		$sql = "";
		$sql .= ! $this->arDistinct ? "SELECT " : "SELECT DISTINCT ";
		if (count ( $this->arSelect ) === 0) {
			$tmp = array ();
			foreach ( $this->schema as $field ) {
				if (! isset ( $field ['encrypt'] )) {
					$tmp [] = 't1.' . $field ['name'];
				} else {
					switch (strtoupper ( $field ['encrypt'] )) {
						case 'AES' :
							$tmp [] = sprintf ( "AES_DECRYPT(t1.%1\$s, %2\$s) AS `%1\$s`", $field ['name'], $this->escapeValue ( SALT ) );
							break;
					}
				}
			}
			$sql .= join ( ", ", $tmp );
		} else {
			$sql .= join ( ", ", $this->arSelect );
		}
		$sql .= "\n";
		$sql .= "FROM " . (empty ( $this->arFrom ) ? $this->getTable () : $this->arFrom) . " AS t1";
		$sql .= "\n";
		if (! empty ( $this->arIndex )) {
			$sql .= $this->arIndex;
			$sql .= "\n";
		}
		if (count ( $this->arJoin ) > 0) {
			$sql .= join ( "\n", $this->arJoin );
			$sql .= "\n";
		}
		if (is_array ( $this->arWhere ) && count ( $this->arWhere ) > 0) {
			$sql .= "WHERE " . join ( "\n", $this->arWhere );
			$sql .= "\n";
		}
		if (! empty ( $this->arGroupBy )) {
			$sql .= "GROUP BY " . $this->arGroupBy;
			$sql .= "\n";
		}
		if (! empty ( $this->arHaving )) {
			$sql .= "HAVING " . $this->arHaving;
			$sql .= "\n";
		}
		if (! empty ( $this->arOrderBy )) {
			$sql .= "ORDER BY " . $this->arOrderBy;
			$sql .= "\n";
		}
		if (( int ) $this->arRowCount > 0) {
			$sql .= "LIMIT " . ( int ) $this->arOffset . ", " . ( int ) $this->arRowCount;
		}
		return $sql;
	}
	public function commit() {
		if ($this->transactionStarted && $this->prepare ( "COMMIT" )->exec ()->dbo->getResult ()) {
			$this->transactionStarted = false;
			return true;
		}
		return false;
	}
	public function debug($val) {
		$this->arDebug = ( bool ) $val;
		return $this;
	}
	public function distinct($val) {
		$this->arDistinct = is_bool ( $val ) ? $val : true;
		return $this;
	}
	public function erase() {
		if ($this->beforeDelete ()) {
			$sql = sprintf ( "DELETE FROM `%s` WHERE `%s` = '%s' LIMIT 1", $this->getTable (), $this->primaryKey, $this->{$this->primaryKey} );
			if (FALSE !== $this->dbo->query ( $sql )) {
				$this->affectedRows = $this->dbo->affectedRows ();
				$this->afterDelete ();
			} else {
				die ( $this->dbo->error () );
			}
		}
		return $this;
	}
	public function eraseAll() {
		if ($this->beforeDelete ()) {
			$sql = "";
			$sql .= sprintf ( "DELETE FROM `%s`", empty ( $this->arFrom ) ? $this->getTable () : $this->arFrom );
			$sql .= "\n";
			if (is_array ( $this->arWhere ) && count ( $this->arWhere ) > 0) {
				$sql .= "WHERE " . join ( "\n", $this->arWhere );
				$sql .= "\n";
			}
			if (! empty ( $this->arOrderBy )) {
				$sql .= "ORDER BY " . $this->arOrderBy;
				$sql .= "\n";
			}
			if (( int ) $this->arRowCount > 0) {
				$sql .= "LIMIT " . ( int ) $this->arRowCount;
			}
			if ($this->arDebug) {
				printf ( '<pre>%s</pre>', $sql );
			}
			if (FALSE !== $this->dbo->query ( $sql )) {
				$this->affectedRows = $this->dbo->affectedRows ();
				$this->afterDelete ();
			} else {
				die ( $this->dbo->error () );
			}
		}
		return $this;
	}
	public function escape($value, $column = null, $type = null) {
		if (is_null ( $type ) && ! is_null ( $column )) {
			$type = $this->getColumnType ( $column );
		}
		switch ($type) {
			case 'null' :
			case 'tinyblob' :
			case 'mediumblob' :
			case 'blob' :
			case 'longblob' :
				return $value;
				break;
			case 'int' :
			case 'smallint' :
			case 'tinyint' :
			case 'mediumint' :
			case 'bigint' :
				return intval ( $value );
				break;
			case 'float' :
			case 'decimal' :
			case 'double' :
			case 'real' :
				return floatval ( $value );
				break;
			case 'string' :
			case 'varchar' :
			case 'enum' :
			case 'set' :
			case 'char' :
			case 'text' :
			case 'tinytext' :
			case 'mediumtext' :
			case 'longtext' :
			case 'date' :
			case 'datetime' :
			case 'year' :
			case 'time' :
			case 'timestamp' :
			default :
				return $this->escapeStr ( $value );
				break;
		}
	}
	public function escapeStr($value) {
		return $this->dbo->escapeString ( $value );
	}
	private function escapeValue($str) {
		if (is_string ( $str ) && strlen ( $str ) > 0) {
			return "'" . $this->escapeStr ( $str ) . "'";
		}
		if (is_bool ( $str )) {
			return ($str === FALSE) ? 0 : 1;
		}
		if (is_numeric ( $str )) {
			return $str;
		}
		if (is_null ( $str ) || empty ( $str )) {
			return 'NULL';
		}
		return $str;
	}
	public function exec($params = array()) {
		$sql = $this->statement;
		foreach ( $params as $key => $value ) {
			$sql = str_replace ( ":" . $key, $this->escapeValue ( $value ), $sql );
		}
		if ($this->arDebug) {
			printf ( '<pre>%s</pre>', $sql );
		}
		$special = array (
				'\x00',
				'\n',
				'\r',
				"'",
				'"',
				'\x1a',
				'\\' 
		);
		foreach ( $special as $str ) {
			if (strpos ( $this->statement, $str ) !== false) {
				trigger_error ( sprintf ( "Illegal string found: <code>%s</code> in: %s", ($str), $this->statement ), E_USER_WARNING );
				exit ();
			}
		}
		if (FALSE !== $this->dbo->query ( $sql )) {
			$this->dbo->fetchAssoc ();
			$this->data = $this->dbo->getData ();
			$this->affectedRows = $this->dbo->affectedRows ();
			$this->insertId = $this->dbo->insertId ();
		} else {
			die ( $this->dbo->error () );
		}
		return $this;
	}
	public function execute($sql) {
		if ($this->arDebug) {
			printf ( '<pre>%s</pre>', $sql );
		}
		if (FALSE !== $this->dbo->query ( $sql )) {
			$this->dbo->fetchAssoc ();
			$this->data = $this->dbo->getData ();
			$this->affectedRows = $this->dbo->affectedRows ();
			$this->insertId = $this->dbo->insertId ();
		} else {
			die ( $this->dbo->error () );
		}
		return $this;
	}
	public function find($pk) {
		if ($this->beforeFind ()) {
			$this->arWhere = array ();
			$this->arHaving = NULL;
			$this->arIndex = NULL;
			$this->arGroupBy = NULL;
			$this->arOrderBy = NULL;
			$this->arDistinct = FALSE;
			$this->limit ( 1, 0 )->where ( "t1." . $this->primaryKey, $pk );
			$sql = $this->buildSelect ();
			if ($this->arDebug) {
				printf ( '<pre>%s</pre>', $sql );
			}
			if (FALSE !== $this->dbo->query ( $sql )) {
				$this->dbo->fetchAssoc ();
				$this->afterFind ();
				$this->data = count ( $this->dbo->getData () ) > 0 ? $this->dbo->getData ( 0 ) : array ();
				$this->setAttributes ( $this->data );
			} else {
				die ( $this->dbo->error () );
			}
		}
		return $this;
	}
	public function findAll() {
		if ($this->beforeFind ()) {
			$sql = $this->buildSelect ();
			if ($this->arDebug) {
				printf ( '<pre>%s</pre>', $sql );
			}
			if (FALSE !== $this->dbo->query ( $sql )) {
				$this->dbo->fetchAssoc ();
				$this->afterFind ();
				$this->data = $this->dbo->getData ();
			} else {
				die ( $this->dbo->error () );
			}
		}
		return $this;
	}
	public function findCount() {
		$sql = "";
		$sql .= "SELECT COUNT(*) AS `cnt`";
		$sql .= "\n";
		$sql .= sprintf ( "FROM `%s` AS t1", ! empty ( $this->arFrom ) ? $this->arFrom : $this->getTable () );
		$sql .= "\n";
		if (! empty ( $this->arIndex )) {
			$sql .= $this->arIndex;
			$sql .= "\n";
		}
		if (count ( $this->arJoin ) > 0) {
			$sql .= join ( "\n", $this->arJoin );
			$sql .= "\n";
		}
		if (is_array ( $this->arWhere ) && count ( $this->arWhere ) > 0) {
			$sql .= "WHERE " . join ( "\n", $this->arWhere );
			$sql .= "\n";
		}
		if (! empty ( $this->arGroupBy )) {
			$sql .= "GROUP BY " . $this->arGroupBy;
			$sql .= "\n";
		}
		if (! empty ( $this->arHaving )) {
			$sql .= "HAVING " . $this->arHaving;
			$sql .= "\n";
		}
		if (! empty ( $this->arGroupBy )) {
			$sql = sprintf ( "SELECT COUNT(*) AS `cnt` FROM (%s) AS `tmp`", $sql );
			$sql .= "\n";
		}
		$sql .= "LIMIT 1";
		if ($this->arDebug) {
			printf ( '<pre>%s</pre>', $sql );
		}
		if (FALSE !== $this->dbo->query ( $sql )) {
			$this->dbo->fetchRow ();
			$this->data = $this->dbo->getData ( 0 );
		} else {
			die ( $this->dbo->error () );
		}
		return $this;
	}
	public function from($table, $escape = TRUE) {
		if (( bool ) $escape === TRUE) {
			$this->arFrom = $this->escapeStr ( $table );
		} else {
			$this->arFrom = $table;
		}
		return $this;
	}
	public function getAffectedRows() {
		return $this->affectedRows;
	}
	public function getAssocTypes() {
		return $this->assocTypes;
	}
	public function getAttributes() {
		$attr = array ();
		foreach ( $this->schema as $field ) {
			$attr [$field ['name']] = NULL;
			if (isset ( $this->{$field ['name']} )) {
				$attr [$field ['name']] = $this->{$field ['name']};
			}
		}
		return $attr;
	}
	public function getColumnType($column) {
		foreach ( $this->schema as $col ) {
			if ($col ['name'] == $column) {
				return $col ['type'];
			}
		}
		return false;
	}
	public function getColumns() {
		$this->prepare ( sprintf ( "SHOW COLUMNS FROM `%s`", $this->getTable () ) )->exec ();
		return $this;
	}
	public function getData() {
		return $this->data;
	}
	
	public function first() {
	    return !empty($this->data[0])? $this->data[0] : NULL;
	}
	public function getDataSlice($offset, $length = NULL, $preserve_keys = FALSE) {
		if (is_null ( $length )) {
			$length = count ( $this->data ) - $offset;
		}
		return array_slice ( $this->data, $offset, $length, $preserve_keys );
	}
	public function getDataIndex($index) {
		return ! empty ( $this->data ) && isset ( $this->data [$index] ) ? $this->data [$index] : FALSE;
	}
	public function getDataPair($key = NULL, $value = NULL) {
		$arr = array ();
		foreach ( $this->data as $item ) {
			if ($key !== NULL) {
				$arr [$item [$key]] = ! is_null ( $value ) ? $item [$value] : $item;
			} else {
				$arr [] = ! is_null ( $value ) ? $item [$value] : $item;
			}
		}
		return $arr;
	}

	public function getDataGroupFields($key = NULL, $fields = []) {
		$arr = array ();
		foreach ( $this->data as $item ) {
			$groupItems = [];
			if (!empty($fields)) {
				foreach ($fields as $field) {
					$groupItems[$field] = $item[$field];
				}
			}
			if ($key !== NULL) {
				$arr [$item [$key]] = $groupItems;
			} else {
				$arr [] = $groupItems;
			}
		}
		return $arr;
	}

	public function getErrors() {
		return $this->errors;
	}
	public function getI18n() {
		return $this->i18n;
	}
	public function getInitialized() {
		return $this->initialized;
	}
	public function getInsertId() {
		return $this->insertId;
	}
	public function getResult() {
		return $this->dbo->getResult ();
	}
	public function getSchema() {
		return $this->schema;
	}
	public function getTable() {
		return $this->prefix . $this->scriptPrefix . $this->table;
	}
	public function groupBy($group, $escape = TRUE) {
		if (( bool ) $escape === TRUE) {
			$this->arGroupBy = $this->escapeStr ( $group );
		} else {
			$this->arGroupBy = $group;
		}
		return $this;
	}
	public function hasColumn($columnName) {
		foreach ( $this->schema as $field ) {
			if ($field ['name'] == $columnName) {
				return true;
			}
		}
		return false;
	}
	private function hasOperator($str) {
		$str = trim ( $str );
		if (! preg_match ( "/(\s|<|>|!|=|IS NULL|IS NOT NULL)/i", $str )) {
			return FALSE;
		}
		return TRUE;
	}
	public function having($val, $escape = TRUE) {
		if (( bool ) $escape === TRUE) {
			$this->arHaving = $this->escapeStr ( $val );
		} else {
			$this->arHaving = $val;
		}
		return $this;
	}
	public function index($val, $escape = TRUE) {
		if (( bool ) $escape === TRUE) {
			$this->arIndex = $this->escapeStr ( $val );
		} else {
			$this->arIndex = $val;
		}
		return $this;
	}
	public function insert() {
		if ($this->beforeSave ()) {
			$save = $this->buildSave ( 'insert' );
			if (count ( $save ) > 0) {
				$sql = sprintf ( "INSERT IGNORE INTO `%s` SET %s;", $this->getTable (), join ( ",", $save ) );
				if ($this->arDebug) {
					printf ( '<pre>%s</pre>', $sql );
				}
				if (FALSE !== $this->dbo->query ( $sql )) {
					$this->affectedRows = $this->dbo->affectedRows ();
					if ($this->getAffectedRows () === 1) {
						$this->insertId = $this->dbo->insertId ();
						$this->afterSave ();
					}
				} else {
					die ( $this->dbo->error () );
				}
			}
		}
		return $this;
	}
	public function setBatchFields($value) {
		if (is_array ( $value )) {
			$this->arBatchFields = $value;
		}
		return $this;
	}
	public function addBatchRow($value) {
		if (is_array ( $value )) {
			$this->arBatch [] = $value;
		}
		return $this;
	}
	public function setBatchRows($value) {
		if (is_array ( $value )) {
			$this->arBatch = $value;
		}
		return $this;
	}
	private function buildBatch() {
		$save = array ();
		$i = 0;
		foreach ( $this->arBatch as $item ) {
			foreach ( $item as $k => $v ) {
				$item [$k] = preg_match ( '/^:[a-zA-Z]{1}.*/', $v ) ? substr ( $v, 1 ) : $this->escapeValue ( $v );
			}
			$save [$i] = sprintf ( "(%s)", join ( ",", $item ) );
			$i ++;
		}
		return $save;
	}
	public function insertBatch() {
		if ($this->beforeSave ()) {
			$save = $this->buildBatch ();
			if (! empty ( $save )) {
				$sql = sprintf ( "INSERT IGNORE INTO `%s` (`%s`) VALUES %s;", $this->getTable (), join ( "`, `", $this->arBatchFields ), join ( ",", $save ) );
				if ($this->arDebug) {
					printf ( '<pre>%s</pre>', $sql );
				}
				if (FALSE !== $this->dbo->query ( $sql )) {
					$this->affectedRows = $this->dbo->affectedRows ();
					if ($this->getAffectedRows () > 0) {
						$this->afterSave ();
					}
				} else {
					die ( $this->dbo->error () );
				}
			}
		}
		return $this;
	}
	public function join($modelName, $cond, $type = NULL, $index = NULL) {
		if (! is_null ( $type )) {
			$type = strtoupper ( trim ( $type ) );
			if (! in_array ( $type, $this->joinArr )) {
				$type = '';
			} else {
				$type .= ' ';
			}
		}
		if (! is_null ( $index )) {
			if (! preg_match ( '/^\s*(USE|FORCE|IGNORE)\s+(INDEX|KEY)/', $index )) {
				$index = NULL;
			} else {
				$index = ' ' . $this->escapeStr ( $index );
			}
		}
		if (preg_match ( '/([\w\.]+)([\W\s]+)(.+)/', $cond, $match )) {
			$cond = $match [1] . $match [2] . $match [3];
		}
		$className = ToolkitComponent::getFullModelClassName($modelName . 'Model');
		if (class_exists ( $className )) {
			$model = new $className ();
		} else {
			$m = 'C' . md5 ( $className );
			if (class_exists ( $m )) {
				$model = new $m ();
			}
		}
		if (isset ( $model ) && is_object ( $model )) {
			$join = $type . 'JOIN ' . $model->getTable () . ' AS t' . (count ( $this->arJoin ) + 2) . $index . ' ON ' . $cond;
			$this->arJoin [] = $join;
		}
		return $this;
	}
	public function limit($row_count, $offset = NULL) {
		$this->arRowCount = ( int ) $row_count;
		if (! is_null ( $offset )) {
			$this->arOffset = ( int ) $offset;
		}
		return $this;
	}
	public function modify($data = array()) {
		if ($this->beforeSave ()) {
			$data [$this->primaryKey] = $this->{$this->primaryKey};
			$this->setAttributes ( $data );
			$update = $this->buildSave ();
			if (count ( $update ) > 0) {
				$sql = sprintf ( "UPDATE `%s` SET %s WHERE `%s` = '%s' LIMIT 1", $this->getTable (), join ( ", ", $update ), $this->primaryKey, $this->{$this->primaryKey} );
				if ($this->arDebug) {
					printf ( '<pre>%s</pre>', $sql );
				}
				if (FALSE !== $this->dbo->query ( $sql )) {
					$this->affectedRows = $this->dbo->affectedRows ();
					if ($this->getAffectedRows () === 1) {
						$this->afterSave ();
					}
				} else {
					die ( $this->dbo->error () );
				}
			}
		}
		return $this;
	}
	public function modifyAll($data = array()) {
		if ($this->beforeSave ()) {
			$this->setAttributes ( $data );
			$update = $this->buildSave ();
			if (count ( $update ) > 0) {
				$sql = sprintf ( "UPDATE `%s` SET %s", $this->getTable (), join ( ",", $update ) );
				$sql .= "\n";
				if (is_array ( $this->arWhere ) && count ( $this->arWhere ) > 0) {
					$sql .= "WHERE " . join ( "\n", $this->arWhere );
					$sql .= "\n";
				}
				if (! empty ( $this->arOrderBy )) {
					$sql .= "ORDER BY " . $this->arOrderBy;
					$sql .= "\n";
				}
				if (( int ) $this->arRowCount > 0) {
					$sql .= "LIMIT " . ( int ) $this->arRowCount;
				}
				if ($this->arDebug) {
					printf ( '<pre>%s</pre>', $sql );
				}
				if (FALSE !== $this->dbo->query ( $sql )) {
					$this->affectedRows = $this->dbo->affectedRows ();
					$this->afterSave ();
				} else {
					die ( $this->dbo->error () );
				}
			}
		}
		return $this;
	}
	public function offset($offset) {
		$this->arOffset = ( int ) $offset;
		return $this;
	}
	public function orderBy($order, $escape = TRUE) {
		if (( bool ) $escape === TRUE) {
			$this->arOrderBy = $this->escapeStr ( $order );
		} else {
			$this->arOrderBy = $order;
		}
		return $this;
	}
	public function orWhere($key, $value = NULL, $escape = TRUE) {
		return $this->setWhere ( $key, $value, 'OR', $escape );
	}
	public function orWhereIn($key = NULL, $values = NULL) {
		return $this->setWhereIn ( $key, $values, FALSE, 'OR' );
	}
	public function orWhereNotIn($key = NULL, $values = NULL) {
		return $this->setWhereIn ( $key, $values, TRUE, 'OR' );
	}
	public function prepare($statement) {
		$this->statement = $statement;
		return $this;
	}
	public function releaseSavepoint($identifier) {
		if ($this->transactionStarted && $this->prepare ( "RELEASE SAVEPOINT " . $identifier )->exec ()->dbo->getResult ()) {
			return true;
		}
		return false;
	}
	public function reset() {
		$this->arBatch = array ();
		$this->arBatchFields = array ();
		$this->arDebug = FALSE;
		$this->arDistinct = FALSE;
		$this->arFrom = NULL;
		$this->arGroupBy = NULL;
		$this->arHaving = NULL;
		$this->arIndex = NULL;
		$this->arJoin = array ();
		$this->arOffset = NULL;
		$this->arOrderBy = NULL;
		$this->arRowCount = NULL;
		$this->arSelect = array ();
		$this->arWhere = array ();
		$this->arWhereIn = array ();
		$this->data = array ();
		$this->statement = NULL;
		foreach ( $this->schema as $field ) {
			$this->{$field ['name']} = NULL;
		}
		return $this;
	}
	public function rollback() {
		if ($this->transactionStarted && $this->prepare ( "ROLLBACK" )->exec ()->dbo->getResult ()) {
			$this->transactionStarted = false;
			return true;
		}
		return false;
	}
	public function rollbackToSavepoint($identifier) {
		if ($this->transactionStarted && $this->prepare ( "ROLLBACK TO SAVEPOINT " . $identifier )->exec ()->dbo->getResult ()) {
			return true;
		}
		return false;
	}
	public function savepoint($identifier) {
		if ($this->transactionStarted && $this->prepare ( "SAVEPOINT " . $identifier )->exec ()->dbo->getResult ()) {
			return true;
		}
		return false;
	}
	public function select($fields = "*") {
		if (is_string ( $fields )) {
			$fields = explode ( ",", $fields );
		}
		foreach ( $fields as $field ) {
			$field = trim ( $field );
			if (! empty ( $field )) {
				$this->arSelect [] = $field;
			}
		}
		return $this;
	}
	public function set($key, $value) {
		foreach ( $this->schema as $field ) {
			if ($field ['name'] == $key) {
				$this->{$field ['name']} = $value;
				break;
			}
		}
		return $this;
	}
	public function setAttributes($attr) {
		foreach ( $this->schema as $field ) {
			$this->{$field ['name']} = NULL;
			if (isset ( $attr [$field ['name']] )) {
				$this->{$field ['name']} = $attr [$field ['name']];
			}
		}
		return $this;
	}
	public function setPrefix($prefix) {
		$this->prefix = $prefix;
		return $this;
	}
	public function setTable($tblName) {
		$this->table = $tblName;
		return $this;
	}
	private function setWhere($key, $value = NULL, $type = 'AND', $escape = TRUE) {
		if (! is_array ( $key )) {
			$key = array (
					$key => $value 
			);
		}
		foreach ( $key as $k => $v ) {
			$operator = count ( $this->arWhere ) === 0 ? NULL : $type;
			if (is_null ( $v ) && ! $this->hasOperator ( $k )) {
				$k .= ' IS NULL';
			}
			if (! is_null ( $v )) {
				if ($escape) {
					$v = $this->escapeValue ( $v );
				}
				if (! $this->hasOperator ( $k )) {
					$k .= ' =';
				}
			}
			$this->arWhere [] = sprintf ( "%s %s %s", $operator, $k, $v );
		}
		return $this;
	}
	private function setWhereIn($key = NULL, $values = NULL, $not = FALSE, $type = 'AND') {
		if ($key === NULL || $values === NULL) {
			return;
		}
		if (! is_array ( $values )) {
			$values = array (
					$values 
			);
		}
		$not = ($not) ? ' NOT' : NULL;
		foreach ( $values as $value ) {
			$this->arWhereIn [] = $this->escapeValue ( $value );
		}
		$operator = (count ( $this->arWhere ) == 0) ? NULL : $type;
		$whereIn = $operator . " " . $key . $not . " IN (" . join ( ", ", $this->arWhereIn ) . ") ";
		$this->arWhere [] = $whereIn;
		$this->arWhereIn = array ();
		return $this;
	}
	public function toArray($key, $separator = "|", $newKey = NULL) {
		$data = $this->getData ();
		foreach ( $this->data as $k => $v ) {
			if (is_array ( $v ) && is_numeric ( $k )) {
				foreach ( $v as $_k => $_v ) {
					if ($_k == $key) {
						$this->data [$k] [is_null ( $newKey ) ? $key : $newKey] = strpos ( $_v, $separator ) !== FALSE ? explode ( $separator, $_v ) : (strlen ( $_v ) > 0 ? array (
								$_v 
						) : array ());
						break;
					}
				}
			} else {
				if ($k == $key) {
					$this->data [is_null ( $newKey ) ? $key : $newKey] = strpos ( $v, $separator ) !== FALSE ? explode ( $separator, $v ) : (strlen ( $v ) > 0 ? array (
							$v 
					) : array ());
					break;
				}
			}
		}
		return $this;
	}
	public function truncate($tblName = NULL) {
		if ($this->beforeDelete ()) {
			$sql = sprintf ( "TRUNCATE TABLE `%s`;", ! empty ( $tblName ) ? $tblName : $this->getTable () );
			if ($this->arDebug) {
				printf ( '<pre>%s</pre>', $sql );
			}
			if (FALSE !== $this->dbo->query ( $sql )) {
				$this->afterDelete ();
			} else {
				die ( $this->dbo->error () );
			}
		}
		return $this;
	}
	public function validates($data) {
		foreach ( $this->schema as $field ) {
			if (isset ( $this->validate ['rules'] ) && isset ( $this->validate ['rules'] [$field ['name']] )) {
				$rule = $this->validate ['rules'] [$field ['name']];
				if (is_array ( $rule )) {
					foreach ( $rule as $ruleName => $ruleValue ) {
						if (is_array ( $ruleValue )) {
							$rule = $ruleValue;
							array_shift ( $rule );
							$param_arr = array_merge ( array (
									@$data [$field ['name']] 
							), $rule );
							if (! call_user_func_array ( array (
									'ValidationComponent',
									$ruleValue [0] 
							), $param_arr )) {
								$this->errors [] = array (
										'field' => $field ['name'],
										'value' => @$data [$field ['name']] 
								);
							}
						} else {
							if (! ValidationComponent::$ruleName ( @$data [$field ['name']] ) == $ruleValue) {
								$this->errors [] = array (
										'field' => $field ['name'],
										'value' => @$data [$field ['name']] 
								);
							}
						}
					}
				} else {
					if (! ValidationComponent::$rule ( @$data [$field ['name']] )) {
						$this->errors [] = array (
								'field' => $field ['name'],
								'value' => @$data [$field ['name']] 
						);
					}
				}
			}
		}
		return count ( $this->errors ) === 0;
	}
	public function where($key, $value = NULL, $escape = TRUE) {
		return $this->setWhere ( $key, $value, 'AND', $escape );
	}
	public function whereIn($key = NULL, $values = NULL) {
		return $this->setWhereIn ( $key, $values );
	}
	public function whereNotIn($key = NULL, $values = NULL) {
		return $this->setWhereIn ( $key, $values, TRUE );
	}
}
?>