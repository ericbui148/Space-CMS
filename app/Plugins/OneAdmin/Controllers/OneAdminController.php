<?php
namespace App\Plugins\OneAdmin\Controllers;

use App\Plugins\OneAdmin\Models\OneAdminModel;
use App\Controllers\AppController;

class OneAdminController extends OneAdminAppController 
{

	public function Delete() {
		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged () && $this->isOneAdminReady ()) {
			if (isset ( $_GET ['id'] ) && ( int ) $_GET ['id'] > 0) {
				if (OneAdminModel::factory ()->set ( 'id', $_GET ['id'] )->erase ()->getAffectedRows () == 1) {
					AppController::jsonResponse ( array (
							'status' => 'OK',
							'code' => 200,
							'text' => 'Item have been deleted.' 
					) );
				}
				AppController::jsonResponse ( array (
						'status' => 'ERR',
						'code' => 100,
						'text' => 'Item have not been deleted.' 
				) );
			}
			AppController::jsonResponse ( array (
					'status' => 'ERR',
					'code' => 101,
					'text' => 'Missing, empty or invalid parameters.' 
			) );
		}
		exit ();
	}

	public function DeleteBulk() {

		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged () && $this->isOneAdminReady ()) {
			if (isset ( $_POST ['record'] ) && ! empty ( $_POST ['record'] )) {
				OneAdminModel::factory ()->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
				AppController::jsonResponse ( array (
						'status' => 'OK',
						'code' => 200,
						'text' => 'Item(s) have been deleted.' 
				) );
			}
			AppController::jsonResponse ( array (
					'status' => 'ERR',
					'code' => 100,
					'text' => 'Missing, empty or invalid parameters.' 
			) );
		}
		exit ();
	}

	public function GetOneAdmin() {

		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged () && $this->isOneAdminReady ()) {
			$OneAdminModel = OneAdminModel::factory ();
			if (isset ( $_GET ['q'] ) && ! empty ( $_GET ['q'] )) {
				$q = $OneAdminModel->escapeStr ( $_GET ['q'] );
				$q = str_replace ( array (
						'%',
						'_' 
				), array (
						'\%',
						'\_' 
				), trim ( $q ) );
				$OneAdminModel->where ( 't1.name LIKE', "%$q%" );
			}
			$column = 'name';
			$direction = 'ASC';
			if (isset ( $_GET ['direction'] ) && isset ( $_GET ['column'] ) && in_array ( strtoupper ( $_GET ['direction'] ), array (
					'ASC',
					'DESC' 
			) )) {
				$column = $_GET ['column'];
				$direction = strtoupper ( $_GET ['direction'] );
			}
			$total = $OneAdminModel->findCount ()->getData ();
			$rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 10;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}
			$data = $OneAdminModel->orderBy ( "$column $direction" )->limit ( $rowCount, $offset )->findAll ()->getData ();
			AppController::jsonResponse ( compact ( 'data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction' ) );
		}
		exit ();
	}

	public function Index() {

		$this->checkLogin ();
		if ($this->isOneAdminReady ()) {
			$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
			$this->appendJs ( 'OneAdmin.js', $this->getConst ( 'PLUGIN_JS_PATH' ) );
			$this->appendJs ( 'index.php?controller=Admin&action=Messages', $this->baseUrl(), true );
		} else {
			$this->set ( 'status', 2 );
		}
	}

	public function Menu() {

		$this->checkLogin ();
		$this->setAjax ( true );
		$this->set ( 'arr', OneAdminModel::factory ()->orderBy ( 't1.name ASC' )->findAll ()->getData () );
	}

	public function Save() {
		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged () && $this->isOneAdminReady ()) {
			if (isset ( $_GET ['id'] ) && ( int ) $_GET ['id'] > 0 && isset ( $_POST ['column'], $_POST ['value'] )) {
				OneAdminModel::factory ()->set ( 'id', $_GET ['id'] )->modify ( array (
						$_POST ['column'] => $_POST ['value'] 
				) );
				AppController::jsonResponse ( array (
						'status' => 'OK',
						'code' => 201,
						'text' => 'Item have been updated.' 
				) );
			} else {
				$insert_id = OneAdminModel::factory ( array (
						'name' => 'Script name',
						'url' => 'http://www.example.com/' 
				) )->insert ()->getInsertId ();
				if ($insert_id !== false && ( int ) $insert_id > 0) {
					AppController::jsonResponse ( array (
							'status' => 'OK',
							'code' => 200,
							'text' => 'Item have been saved.',
							'id' => $insert_id 
					) );
				}
				AppController::jsonResponse ( array (
						'status' => 'ERR',
						'code' => 100,
						'text' => 'Item have not been saved' 
				) );
			}
		}
		exit ();
	}
}
?>