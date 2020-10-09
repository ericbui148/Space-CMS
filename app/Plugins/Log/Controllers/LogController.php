<?php
namespace App\Plugins\Log\Controllers;

use App\Plugins\Log\Models\LogConfigModel;
use App\Controllers\Components\UtilComponent;
use App\Plugins\Log\Models\LogModel;
use App\Controllers\AppController;

class LogController extends LogAppController {
	public function Config() {
		$this->checkLogin ();
		if ($this->isAdmin ()) {
			$LogConfigModel = LogConfigModel::factory ();
			if (isset ( $_POST ['update_config'] )) {
				$LogConfigModel->eraseAll ();
				if (isset ( $_POST ['filename'] ) && count ( $_POST ['filename'] ) > 0) {
					$LogConfigModel->begin ();
					foreach ( $_POST ['filename'] as $filename ) {
						$LogConfigModel->reset ()->set ( 'filename', $filename )->insert ();
					}
					$LogConfigModel->commit ();
				}
				UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Log&action=Config&err=PLG01" );
			}
			$data = array ();
			UtilComponent::readDir ( $data, 'app/controllers/' );
			UtilComponent::readDir ( $data, 'app/plugins/' );
			$this->set ( 'data', $data );
			$this->set ( 'config_arr', $LogConfigModel->findAll ()->getDataPair ( 'id', 'filename' ) );
		} else {
			$this->set ( 'status', 2 );
		}
	}
	public function DeleteLogBulk() {
		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged () && $this->isAdmin ()) {
			if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
				LogModel::factory ()->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
			}
		}
		exit ();
	}
	public function EmptyLog() {
		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged () && $this->isAdmin ()) {
			LogModel::factory ()->truncate ();
		}
		exit ();
	}
	public function GetLog() {
		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged () && $this->isAdmin ()) {
			$LogModel = LogModel::factory ();
			if (isset ( $_GET ['q'] ) && ! empty ( $_GET ['q'] )) {
				$q = $LogModel->escapeStr ( $_GET ['q'] );
				$q = str_replace ( array (
						'%',
						'_' 
				), array (
						'\%',
						'\_' 
				), $q );
				$LogModel->where ( 't1.filename LIKE', "%$q%" );
			}
			$column = 'created';
			$direction = 'ASC';
			if (isset ( $_GET ['direction'] ) && isset ( $_GET ['column'] ) && in_array ( strtoupper ( $_GET ['direction'] ), array (
					'ASC',
					'DESC' 
			) )) {
				$column = $_GET ['column'];
				$direction = strtoupper ( $_GET ['direction'] );
			}
			$total = $LogModel->findCount ()->getData ();
			$rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 10;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}
			$data = $LogModel->select ( 't1.*' )->orderBy ( "`$column` $direction" )->limit ( $rowCount, $offset )->findAll ()->getData ();
			AppController::jsonResponse ( compact ( 'data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction' ) );
		}
		exit ();
	}
	public function Index() {
		$this->checkLogin ();
		if ($this->isAdmin ()) {
			$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
			$this->appendJs ( 'Log.js', $this->getConst ( 'PLUGIN_JS_PATH' ) );
			$this->appendJs ( 'index.php?controller=Admin&action=Messages', $this->baseUrl(), true );
		} else {
			$this->set ( 'status', 2 );
		}
	}
	public function Logger() {
		$params = $this->getParams ();
		if (! isset ( $params ['key'] ) || $params ['key'] != md5 ( @$this->option_arr ['private_key'] . SALT )) {
			return FALSE;
		}
		$debug_backtrace = debug_backtrace ( false );
		$controller = NULL;
		foreach ( $debug_backtrace as $item ) {
			if (strpos ( $item ['file'], 'Observer.class.php' ) !== false) {
				$params ['function'] = $item ['args'] [0] ['action'];
				$controller = $item ['args'] [0] ['controller'];
				break;
			}
		}
		foreach ( $debug_backtrace as $item ) {
			if (strpos ( $item ['file'], $controller ) !== false) {
				$params ['filename'] = str_replace ( INSTALL_PATH, "", str_replace ( "\\", "/", $item ['file'] ) );
			}
		}
		if (! is_null ( $controller )) {
			if (LogConfigModel::factory ()->where ( 't1.filename', $controller )->findCount ()->getData () != 0) {
				LogModel::factory ( $params )->insert ();
			}
		}
	}
}
?>