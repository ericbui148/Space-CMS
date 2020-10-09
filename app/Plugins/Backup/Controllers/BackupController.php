<?php
namespace App\Plugins\Backup\Controllers;

use App\Controllers\AppController;
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\ZipStreamComponent;
use Core\Framework\Components\ToolkitComponent;

class BackupController extends BackupAppController 
{

	const NAMESPACE_MODEL = 'App\\Models\\';
	const NAMESPACE_PLUGIN_MODEL = 'App\\Plugins\\%s\\Models\\';
	
	public function Delete() {
		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged () && $this->isAdmin ()) {
			if (isset ( $_GET ['id'] ) && ! empty ( $_GET ['id'] )) {
				$file = WEB_PATH . 'backup/' . basename ( $_GET ['id'] );
				clearstatcache ();
				if (is_file ( $file )) {
					@unlink ( $file );
					AppController::jsonResponse ( array (
							'status' => 'OK',
							'code' => 200,
							'text' => 'File has been deleted.' 
					) );
				} else {
					AppController::jsonResponse ( array (
							'status' => 'ERR',
							'code' => 100,
							'text' => 'File not found.' 
					) );
				}
			}
			AppController::jsonResponse ( array (
					'status' => 'ERR',
					'code' => 101,
					'text' => 'Missing or empty parameters.' 
			) );
		}
		exit ();
	}
	public function DeleteBulk() {
		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged () && $this->isAdmin ()) {
			if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
				foreach ( $_POST ['record'] as $item ) {
					$file = WEB_PATH . 'backup/' . basename ( $item );
					clearstatcache ();
					if (is_file ( $file )) {
						@unlink ( $file );
					}
				}
				AppController::jsonResponse ( array (
						'status' => 'OK',
						'code' => 200,
						'text' => 'Delete operation complete.' 
				) );
			}
			AppController::jsonResponse ( array (
					'status' => 'ERR',
					'code' => 100,
					'text' => 'Missing or empty parameters.' 
			) );
		}
		exit ();
	}
	public function Download() {
		$this->setAjax ( true );
		if ($this->isLoged () && $this->isAdmin ()) {
			if (isset ( $_GET ['id'] ) && ! empty ( $_GET ['id'] )) {
				$id = basename ( $_GET ['id'] );
				$file = WEB_PATH . 'backup/' . $id;
				$buffer = "";
				@clearstatcache ();
				if (is_file ( $file )) {
					$handle = @fopen ( $file, "rb" );
					if ($handle) {
						while ( ! feof ( $handle ) ) {
							$buffer .= fgets ( $handle, 4096 );
						}
						fclose ( $handle );
					}
					ToolkitComponent::download ( $buffer, $id );
				}
				die ( "File not found." );
			}
			die ( "Missing or empty parameters." );
		}
		$this->checkLogin ();
		exit ();
	}
	public function Get($key = NULL) {
		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged () && $this->isAdmin ()) {
			$column = 'created';
			$direction = 'DESC';
			if (isset ( $_GET ['direction'] ) && isset ( $_GET ['column'] ) && in_array ( strtoupper ( $_GET ['direction'] ), array (
					'ASC',
					'DESC' 
			) )) {
				$column = $_GET ['column'];
				$direction = strtoupper ( $_GET ['direction'] );
			}
			$data = $id = $created = $type = array ();
			if ($handle = opendir ( WEB_PATH . 'backup' )) {
				$i = 0;
				while ( false !== ($entry = readdir ( $handle )) ) {
					preg_match ( '/(database-backup|files-backup)-(\d{10})\.(sql|zip)/', $entry, $m );
					if (isset ( $m [2] )) {
						$id [$i] = $entry;
						$created [$i] = date ( $this->option_arr ['o_date_format'] . ", H:i", $m [2] );
						$type [$i] = $m [1] == 'database-backup' ? 'database' : 'files';
						$data [$i] ['id'] = $id [$i];
						$data [$i] ['created'] = $created [$i];
						$data [$i] ['type'] = $type [$i];
						$i ++;
					}
				}
				closedir ( $handle );
			}
			switch ($column) {
				case 'created' :
					array_multisort ( $created, $direction == 'ASC' ? SORT_ASC : SORT_DESC, $id, SORT_DESC, $type, SORT_ASC, $data );
					break;
				case 'type' :
					array_multisort ( $type, $direction == 'ASC' ? SORT_ASC : SORT_DESC, $id, SORT_DESC, $created, SORT_DESC, $data );
					break;
				case 'id' :
					array_multisort ( $id, $direction == 'ASC' ? SORT_ASC : SORT_DESC, $type, SORT_ASC, $created, SORT_DESC, $data );
					break;
			}
			$total = count ( $data );
			$rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 10;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}
			AppController::jsonResponse ( compact ( 'data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction' ) );
		}
		exit ();
	}
	public function Index() {
		$this->checkLogin ();
		if (isset ( $_POST ['backup'] )) {
			$backup_folder = WEB_PATH . 'backup/';
			if (! is_dir ( $backup_folder )) {
				UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=Backup&action=Index&err=PBU05" );
			}
			if (! is_writable ( $backup_folder )) {
				UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=Backup&action=Index&err=PBU06" );
			}
			@set_time_limit ( 600 );
			$err = 'PBU04';
			if (isset ( $_POST ['db'] )) {
				$app_models = array ();
				ToolkitComponent::readDir ( $app_models, MODELS_PATH );
				$plugin_models = array ();
				ToolkitComponent::readDir ( $plugin_models, PLUGINS_PATH );
				$sql = array ();
				$this->Loop ( $sql, $app_models );
				$this->Loop ( $sql, $plugin_models, true );
				$content = join ( "", $sql );
				if (! $handle = fopen ( WEB_PATH . 'backup/database-backup-' . time () . '.sql', 'wb' )) {
				} else {
					if (fwrite ( $handle, $content ) === FALSE) {
					} else {
						fclose ( $handle );
						$err = 'PBU02';
					}
				}
			}
			if (isset ( $_POST ['files'] )) {
				$files = array ();
				ToolkitComponent::readDir ( $files, UPLOAD_PATH );
				$zipName = 'files-backup-' . time () . '.zip';
				$zip = new ZipStreamComponent();
				$zip->setZipFile ( WEB_PATH . 'backup/' . $zipName );
				foreach ( $files as $file ) {
					$handle = @fopen ( $file, "rb" );
					if ($handle) {
						$buffer = "";
						while ( ! feof ( $handle ) ) {
							$buffer .= fgets ( $handle, 4096 );
						}
						$zip->addFile ( $buffer, $file );
						fclose ( $handle );
					}
				}
				$zip->finalize ();
				$err = 'PBU02';
			}
			if (! isset ( $_POST ['db'] ) && ! isset ( $_POST ['files'] )) {
				$err = 'PBU03';
			}
			UtilComponent::redirect ( sprintf ( "%sindex.php?controller=Backup&action=Index&err=%s", $this->baseUrl(), $err ) );
		}
		$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
		$this->appendJs ( 'Backup.js', $this->getConst ( 'PLUGIN_JS_PATH' ) );
	}
	private function Loop(&$sql, $files, $is_plugin = FALSE) {
		foreach ( $files as $filepath ) {
			$filename = basename ( $filepath );
			if (preg_match ( '/^(\w+)\.model\.php$/', $filename, $matches )) {
				$modelName = $matches [1] . 'Model';
				if ($is_plugin) {
					if (! preg_match ( '/\/(\w+)\/models\/(\w+)\.model\.php$/', $filepath, $m ) || ! in_array ( $m [1], @$GLOBALS ['CONFIG'] ['plugins'] )) {
						continue;
					}
				}
				if ($is_plugin) {
					$pluginNamespace = str_replace('%s', $m[1], self::NAMESPACE_PLUGIN_MODEL);
					$modelName = $pluginNamespace . $modelName;
					$model = new $modelName ();
				} else {
					$modelName = self::NAMESPACE_MODEL.$modelName;
					$model = new $modelName ();
				}
				
				$schema = $model->getSchema ();
				if (empty ( $schema )) {
					continue;
				}
				$table = $model->getTable ();
				if ($table == PREFIX . SCRIPT_PREFIX) {
					continue;
				}
				$fields = array ();
				$columns = array ();
				$schema_index = array ();
				foreach ( $schema as $col ) {
					if ($col ['type'] != 'blob') {
						$fields [] = sprintf ( "`%s`", $col ['name'] );
					} else {
						$fields [] = sprintf ( "LOWER(HEX(`%1\$s`)) AS `%1\$s`", $col ['name'] );
					}
					$columns [] = $col ['name'];
					$schema_index [$col ['name']] = $col;
				}
				$result = $model->reset ()->select ( join ( ", ", $fields ) )->findAll ()->getData ();
				$sql [] = sprintf ( "DROP TABLE IF EXISTS `%s`;\n\n", $table );
				$create = $model->reset ()->prepare ( sprintf ( "SHOW CREATE TABLE `%s`", $table ) )->exec ()->getData ();
				$create = array_values ( $create [0] );
				$sql [] = sprintf ( "%s;\n\n", $create [1] );
				foreach ( $result as $row ) {
					$sql [] = sprintf ( "INSERT INTO `%s` (`%s`) VALUES(", $table, join ( '`, `', $columns ) );
					$insert = array ();
					foreach ( $row as $key => $val ) {
						if (isset ( $schema_index [$key], $schema_index [$key] ['type'] ) && $schema_index [$key] ['type'] == 'blob') {
							$insert [] = '0x' . $val;
						} else {
							if (isset ( $schema_index [$key], $schema_index [$key] ['default'] ) && $val == '') {
								$insert [] = strpos ( $schema_index [$key] ['default'], ':' ) === 0 ? substr ( $schema_index [$key] ['default'], 1 ) : "'" . $schema_index [$key] ['default'] . "'";
							} else {
								$val = str_replace ( '\n', '\r\n', $val );
								$val = preg_replace ( "/\r\n/", '\r\n', $val );
								$insert [] = "'" . str_replace ( "'", "''", $val ) . "'";
							}
						}
					}
					$sql [] = join ( ", ", $insert );
					$sql [] = ");\n";
				}
				$sql [] = "\n";
			}
		}
	}
}
?>