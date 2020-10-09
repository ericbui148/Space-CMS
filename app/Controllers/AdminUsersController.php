<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\MultiLangModel;
use App\Models\RoleModel;
use Core\Framework\Objects;
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\CSVComponent;

class AdminUsersController extends AdminController
{

	public function CheckEmail() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (! isset ( $_GET ['email'] ) || empty ( $_GET ['email'] )) {
				echo 'false';
				exit ();
			}
			$userModel = UserModel::factory ()->where ( 't1.email', $_GET ['email'] );
			if (isset ( $_GET ['id'] ) && ( int ) $_GET ['id'] > 0) {
				$userModel->where ( 't1.id !=', $_GET ['id'] );
			}
			echo $userModel->findCount ()->getData () == 0 ? 'true' : 'false';
		}
		exit ();
	}

	public function CloneUser() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
				$MultiLangModel = new MultiLangModel ();
				$data = UserModel::factory ()->whereIn ( 'id', $_POST ['record'] )->findAll ()->getData ();
				foreach ( $data as $item ) {
					$item_id = $item ['id'];
					unset ( $item ['id'] );
					unset ( $item ['email'] );
					$id = UserModel::factory ( $item )->insert ()->getInsertId ();
					if ($id !== false && ( int ) $id > 0) {
						$_data = MultiLangModel::factory ()->getMultiLang ( $item_id, 'User' );
						$MultiLangModel->saveMultiLang ( $_data, $id, 'User' );
					}
				}
			}
		}
		exit ();
	}

	public function Create() {
		$this->checkLogin ();
		if ($this->isAdmin ()) {
			if (isset ( $_POST ['user_create'] )) {
				$data = array ();
				$data ['is_active'] = 'T';
				$data ['ip'] = $_SERVER ['REMOTE_ADDR'];
				UserModel::factory ( array_merge ( $_POST, $data ) )->insert ()->getInsertId ();
				$err = 'AU04';
				UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=AdminUsers&action=Index&err=$err" );
			} else {
				$this->set ( 'role_arr', RoleModel::factory ()->orderBy ( 't1.id ASC' )->findAll ()->getData () );
				$this->appendJs ( 'jquery.multiselect.min.js', THIRD_PARTY_PATH . 'multiselect/' );
				$this->appendCss ( 'jquery.multiselect.css', THIRD_PARTY_PATH . 'multiselect/' );
				$this->appendJs ( 'chosen.jquery.js', THIRD_PARTY_PATH . 'harvest/chosen/' );
				$this->appendCss ( 'chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
				$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
				$this->appendJs ( 'AdminUsers.js' );
			}
		} else {
			$this->set ( 'status', 2 );
		}
	}

	public function DeleteUser() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$response = array ();
			if ($_GET ['id'] != $this->getUserId () && $_GET ['id'] != 1) {
				if (UserModel::factory ()->setAttributes ( array (
						'id' => $_GET ['id'] 
				) )->erase ()->getAffectedRows () == 1) {
					$response ['code'] = 200;
				} else {
					$response ['code'] = 100;
				}
			} else {
				$response ['code'] = 100;
			}
			AppController::jsonResponse ( $response );
		}
		exit ();
	}

	public function DeleteUserBulk() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
				UserModel::factory ()->where ( 'id !=', $this->getUserId () )->where ( 'id !=', 1 )->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
			}
		}
		exit ();
	}

	public function ExportUser() {
		$this->checkLogin ();
		if (isset ( $_POST ['record'] ) && is_array ( $_POST ['record'] )) {
			$arr = UserModel::factory ()->whereIn ( 'id', $_POST ['record'] )->findAll ()->getData ();
			$csv = new CSVComponent();
			$csv->setHeader ( true )->setName ( "Users-" . time () . ".csv" )->process ( $arr )->download ();
		}
		exit ();
	}
	public function GetUser() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$userModel = UserModel::factory ();
			if (isset ( $_GET ['q'] ) && ! empty ( $_GET ['q'] )) {
				$q = Objects::escapeString ( $_GET ['q'] );
				$userModel->where ( 't1.email LIKE', "%$q%" );
				$userModel->orWhere ( 't1.name LIKE', "%$q%" );
			}
			if (isset ( $_GET ['status'] ) && ! empty ( $_GET ['status'] ) && in_array ( $_GET ['status'], array (
					'T',
					'F' 
			) )) {
				$userModel->where ( 't1.status', $_GET ['status'] );
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
			$total = $userModel->findCount ()->getData ();
			$rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 10;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}
			$data = array ();
			$data = $userModel->select ( 't1.id, t1.email, t1.name, t1.created, t1.status, t1.is_active, t1.role_id, t2.role' )->join ( 'Role', 't2.id=t1.role_id', 'left' )->orderBy ( "$column $direction" )->limit ( $rowCount, $offset )->findAll ()->getData ();
			foreach ( $data as $k => $v ) {
				$v ['created'] = date ( $this->option_arr ['o_date_format'], strtotime ( $v ['created'] ) ) . ', ' . date ( $this->option_arr ['o_time_format'], strtotime ( $v ['created'] ) );
				$data [$k] = $v;
			}
			AppController::jsonResponse ( compact ( 'data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction' ) );
		}
		exit ();
	}

	public function Index() {

		$this->checkLogin ();
		if ($this->isAdmin ()) {
			$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
			$this->appendJs ( 'AdminUsers.js' );
		} else {
			$this->set ( 'status', 2 );
		}
	}

	public function SetActive() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$userModel = UserModel::factory ();
			$arr = $userModel->find ( $_POST ['id'] )->getData ();
			if (count ( $arr ) > 0) {
				switch ($arr ['is_active']) {
					case 'T' :
						$sql_status = 'F';
						break;
					case 'F' :
						$sql_status = 'T';
						break;
					default :
						return;
				}
				$userModel->reset ()->setAttributes ( array (
						'id' => $_POST ['id'] 
				) )->modify ( array (
						'is_active' => $sql_status 
				) );
			}
		}
		exit ();
	}

	public function SaveUser() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$userModel = UserModel::factory ();
			$pass = true;
			if (( int ) $_GET ['id'] === 1) {
				if (in_array ( $_POST ['column'], array (
						'role_id',
						'status',
						'is_active' 
				) )) {
					$pass = false;
				} else if (in_array ( $_POST ['column'], array (
						'name',
						'email' 
				) ) && $_POST ['value'] == '') {
					$pass = false;
				} else if ($_POST ['column'] == 'email' && $_POST ['value'] != '' && ! filter_var ( $_POST ['value'], FILTER_VALIDATE_EMAIL )) {
					$pass = false;
				}
			}
			if ($pass) {
				$userModel->where ( 'id', $_GET ['id'] )->limit ( 1 )->modifyAll ( array (
						$_POST ['column'] => $_POST ['value'] 
				) );
			}
		}
		exit ();
	}

	public function StatusUser() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
				UserModel::factory ()->whereIn ( 'id', $_POST ['record'] )->where ( 'id <>', 1 )->modifyAll ( array (
						'status' => ":IF(`status`='F','T','F')" 
				) );
			}
		}
		exit ();
	}

	public function Update() {

		$this->checkLogin ();

		if ($this->isAdmin ()) {
			if (isset ( $_POST ['user_update'] )) {
				UserModel::factory ()->where ( 'id', $_POST ['id'] )->limit ( 1 )->modifyAll ( $_POST );
				UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminUsers&action=Index&err=AU01" );
			} else {
				$arr = UserModel::factory ()->find ( $_GET ['id'] )->getData ();
				if (count ( $arr ) === 0) {
					UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminUsers&action=Index&err=AU08" );
				}
				$this->set ( 'arr', $arr );
				$this->set ( 'role_arr', RoleModel::factory ()->orderBy ( 't1.id ASC' )->findAll ()->getData () );

				$this->appendJs ( 'jquery.multiselect.min.js', THIRD_PARTY_PATH . 'multiselect/' );
				$this->appendCss ( 'jquery.multiselect.css', THIRD_PARTY_PATH . 'multiselect/' );
				$this->appendJs ( 'chosen.jquery.js', THIRD_PARTY_PATH . 'harvest/chosen/' );
				$this->appendCss ( 'chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
				$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
				$this->appendJs ( 'AdminUsers.js' );
			}
		} else {
			$this->set ( 'status', 2 );
		}
	}
}
?>