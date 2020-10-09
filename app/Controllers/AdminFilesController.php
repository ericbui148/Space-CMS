<?php
namespace App\Controllers;

use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\UploadComponent;
use App\Models\FileModel;
use App\Models\UserModel;
use Core\Framework\Components\CSVComponent;
use App\Models\UserFileModel;

class AdminFilesController extends AdminController
{

	public function Create() {
		$this->checkLogin ();
		if ($this->isAdmin () || ($this->isEditor () && $this->isFileAllowed ())) {
			$post_max_size = UtilComponent::getPostMaxSize ();
			if ($_SERVER ['REQUEST_METHOD'] == 'POST' && isset ( $_SERVER ['CONTENT_LENGTH'] ) && ( int ) $_SERVER ['CONTENT_LENGTH'] > $post_max_size) {
				UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminFiles&action=Index&err=AF05" );
			}
			if (isset ( $_POST ['file_create'] )) {
				$err = NULL;
				$valid = true;
				$data = array ();
				$data ['user_id'] = $this->getUserId ();
				if (isset ( $_FILES ['file'] ) && $_FILES ['file'] ['error'] == 0) {
					$path = $_FILES ['file'] ['name'];
					$extension = pathinfo ( $path, PATHINFO_EXTENSION );
					$allowed_arr = explode ( "|", $this->option_arr ['o_extension_allow'] );
					if (in_array ( $extension, $allowed_arr )) {
						$handle = new UploadComponent();
						if ($handle->load ( $_FILES ['file'] )) {
							$hash = md5 ( uniqid ( rand (), true ) );
							$file_path = UPLOAD_PATH . 'files/' . $hash . '.' . $handle->getExtension ();
							if ($handle->save ( $file_path )) {
								$data ['file_path'] = $file_path;
								$data ['file_name'] = $_FILES ['file'] ['name'];
								$data ['mime_type'] = $_FILES ['file'] ['type'];
								$data ['hash'] = $hash;
								$data ['size'] = UtilComponent::formatSizeUnits ( $_FILES ['file'] ['size'] );
							}
						}
					} else {
						$err = 'AF09';
						$valid = false;
					}
				} else {
					$err = 'AF09';
					$valid = false;
				}
				if ($valid == true) {
					$fileModel = FileModel::factory ();
					$id = $fileModel->setAttributes ( $data )->insert ()->getInsertId ();
					if ($id !== false && ( int ) $id > 0) {
						$userFileModel = UserFileModel::factory ();
						if (isset ( $_POST ['user_id'] )) {
							$userFileModel->begin ();
							foreach ( $_POST ['user_id'] as $user_id ) {
								$data = array ();
								$data ['user_id'] = $user_id;
								$data ['file_id'] = $id;
								$userFileModel->reset ()->setAttributes ( $data )->insert ();
							}
							$userFileModel->commit ();
						} else {
							$data = array ();
							$data ['user_id'] = $this->getUserId ();
							$data ['file_id'] = $id;
							$userFileModel->reset ()->setAttributes ( $data )->insert ();
						}
						$err = 'AF03';
					} else {
						if (isset ( $data ['file_path'] )) {
							@unlink ( INSTALL_PATH . $data ['file_path'] );
						}
						$err = 'AF04';
					}
				}
				UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=AdminFiles&action=Index&err=$err" );
			} else {
				$user_arr = UserModel::factory ()->where ( 'status', 'T' )->where ( 't1.role_id <> 1' )->orderBy ( 'name ASC' )->findAll ()->getData ();
				$this->set ( 'user_arr', $user_arr );
				$this->appendJs ( 'jquery.multiselect.min.js', THIRD_PARTY_PATH . 'multiselect/' );
				$this->appendCss ( 'jquery.multiselect.css', THIRD_PARTY_PATH . 'multiselect/' );
				$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
				$this->appendJs ( 'additional-methods.js', THIRD_PARTY_PATH . 'validate/' );
				$this->appendJs ( 'AdminFiles.js' );
			}
		} else {
			$this->set ( 'status', 2 );
		}
	}

	public function DeleteFile() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$response = array ();
			if ($this->isAdmin () || $this->isEditor ()) {
				$allowed = true;
				if ($this->isEditor () && ! in_array ( $_GET ['id'], $this->getAllowedFileIds ( $this->getUserId () ) )) {
					$allowed = false;
				}
				if ($allowed == true) {
					$fileModel = FileModel::factory ();
					$arr = $fileModel->find ( $_GET ['id'] )->getData ();
					if ($fileModel->reset ()->setAttributes ( array (
							'id' => $_GET ['id'] 
					) )->erase ()->getAffectedRows () == 1) {
						UserFileModel::factory ()->where ( 'file_id', $_GET ['id'] )->eraseAll ();
						$file_path = $arr ['file_path'];
						if (file_exists ( INSTALL_PATH . $file_path )) {
							@unlink ( INSTALL_PATH . $file_path );
						}
						$response ['code'] = 200;
					} else {
						$response ['code'] = 100;
					}
				} else {
					$response ['code'] = 100;
				}
			}
			AppController::jsonResponse ( $response );
		}
		exit ();
	}

	public function DeleteFileBulk() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if ($this->isAdmin () || $this->isEditor ()) {
				if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
					$fileModel = FileModel::factory ();
					$file_arr = $fileModel->whereIn ( 'id', $_POST ['record'] )->findAll ()->getData ();
					foreach ( $file_arr as $f ) {
						$file_path = $f ['file_path'];
						if (file_exists ( INSTALL_PATH . $file_path )) {
							@unlink ( INSTALL_PATH . $file_path );
						}
					}
					$fileModel->reset ()->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
					UserFileModel::factory ()->whereIn ( 'file_id', $_POST ['record'] )->eraseAll ();
				}
			}
		}
		exit ();
	}
	public function ExportFile() {
		$this->checkLogin ();
		if (isset ( $_POST ['record'] ) && is_array ( $_POST ['record'] )) {
			$arr = FileModel::factory ()->whereIn ( 'id', $_POST ['record'] )->findAll ()->getData ();
			$csv = new CSVComponent();
			$csv->setHeader ( true )->setName ( "Files-" . time () . ".csv" )->process ( $arr )->download ();
		}
		exit ();
	}

	public function GetFile() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$fileModel = FileModel::factory ()->join ( 'User', "t2.id = t1.user_id", 'left' );
			if (isset ( $_GET ['q'] ) && ! empty ( $_GET ['q'] )) {
				$q = $fileModel->escapeStr ( $_GET ['q'] );
				$q = str_replace ( array (
						'%',
						'_' 
				), array (
						'\%',
						'\_' 
				), trim ( $q ) );
				$fileModel->where ( "(t1.file_name LIKE '%$q%')" );
			}
			if (isset ( $_GET ['status'] ) && ! empty ( $_GET ['status'] ) && in_array ( $_GET ['status'], array (
					'T',
					'F' 
			) )) {
				$fileModel->where ( 't1.status', $_GET ['status'] );
			}
			if ($this->isEditor ()) {
				$file_ids_arr = $this->getAllowedFileIds ( $this->getUserId () );
				if (! empty ( $file_ids_arr )) {
					$fileModel->whereIn ( 't1.id', $file_ids_arr );
				}
			}
			$column = 'created';
			$direction = 'DESC';
			if (isset ( $_GET ['direction'] ) && isset ( $_GET ['column'] ) && in_array ( strtoupper ( $_GET ['direction'] ), array (
					'ASC',
					'DESC' 
			) )) {
				$column = $_GET ['column'];
				$direction = strtoupper ( $_GET ['direction'] );
			}
			$total = $fileModel->findCount ()->getData ();
			$rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 10;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}
			$data = $fileModel->select ( 't1.*, t2.name' )->orderBy ( "$column $direction" )->limit ( $rowCount, $offset )->findAll ()->getData ();
			foreach ( $data as $k => $v ) {
				$v ['created'] = UtilComponent::formatDate ( date ( 'Y-m-d', strtotime ( $v ['created'] ) ), 'Y-m-d', $this->option_arr ['o_date_format'] ) . ', ' . UtilComponent::formatTime ( date ( 'H:i:s', strtotime ( $v ['created'] ) ), 'H:i:s', $this->option_arr ['o_time_format'] );
				$data [$k] = $v;
			}
			AppController::jsonResponse ( compact ( 'data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction' ) );
		}
		exit ();
	}

	public function Index() {
		$this->checkLogin ();
		
	}

	public function StatusFile() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
				FileModel::factory ()->whereIn ( 'id', $_POST ['record'] )->modifyAll ( array (
						'status' => ":IF(`status`='F','T','F')" 
				) );
			}
		}
		exit ();
	}

	public function Update() {

		$this->checkLogin ();
		if ($this->isAdmin () || ($this->isEditor () && $this->isFileAllowed ())) {
			$post_max_size = UtilComponent::getPostMaxSize ();
			if ($_SERVER ['REQUEST_METHOD'] == 'POST' && isset ( $_SERVER ['CONTENT_LENGTH'] ) && ( int ) $_SERVER ['CONTENT_LENGTH'] > $post_max_size) {
				UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminFiles&action=Index&err=AF05" );
			}
			if (isset ( $_POST ['file_update'] )) {
				$fileModel = FileModel::factory ();
				$arr = $fileModel->find ( $_POST ['id'] )->getData ();
				$err = 'AF01';
				$valid = true;
				$data = array ();
				$data ['user_id'] = $this->getUserId ();
				if (isset ( $_FILES ['file'] ) && $_FILES ['file'] ['error'] == 0) {
					$path = $_FILES ['file'] ['name'];
					$extension = pathinfo ( $path, PATHINFO_EXTENSION );
					$allowed_arr = explode ( "|", $this->option_arr ['o_extension_allow'] );
					if (in_array ( $extension, $allowed_arr )) {
						$handle = new UploadComponent();
						if ($handle->load ( $_FILES ['file'] )) {
							$hash = md5 ( uniqid ( rand (), true ) );
							$file_path = UPLOAD_PATH . 'files/' . $hash . '.' . $handle->getExtension ();
							if ($handle->save ( $file_path )) {
								$data ['file_path'] = $file_path;
								$data ['file_name'] = $_FILES ['file'] ['name'];
								$data ['mime_type'] = $_FILES ['file'] ['type'];
								$data ['hash'] = $hash;
								$data ['size'] = UtilComponent::formatSizeUnits ( $_FILES ['file'] ['size'] );
								$fileModel->reset ()->set ( 'id', $_POST ['id'] )->modify ( $data );
							}
							$file_path = $arr ['file_path'];
							if (file_exists ( INSTALL_PATH . $file_path )) {
								@unlink ( INSTALL_PATH . $file_path );
							}
						}
					} else {
						$err = 'AF10';
						$valid = false;
					}
				} else if ($_FILES ['file'] ['error'] != 4) {
					$err = 'AF10';
					$valid = false;
				}
				if ($valid == true) {
					$userFileModel = UserFileModel::factory ();
					$userFileModel->where ( 'file_id', $_POST ['id'] )->eraseAll ();
					if (isset ( $_POST ['user_id'] )) {
						$userFileModel->reset ()->begin ();
						foreach ( $_POST ['user_id'] as $user_id ) {
							$data = array ();
							$data ['user_id'] = $user_id;
							$data ['file_id'] = $_POST ['id'];
							$userFileModel->reset ()->setAttributes ( $data )->insert ();
						}
						$userFileModel->commit ();
					} else {
						$data = array ();
						$data ['user_id'] = $this->getUserId ();
						$data ['file_id'] = $_POST ['id'];
						$userFileModel->reset ()->setAttributes ( $data )->insert ();
					}
				}
				if ($err == 'AF01') {
					UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminFiles&action=Index&err=$err" );
				} else {
					UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminFiles&action=Update&id=" . $_POST ['id'] . "&err=$err" );
				}
			} else {
				$allowed = true;
				if ($this->isEditor () && ! in_array ( $_GET ['id'], $this->getAllowedFileIds ( $this->getUserId () ) )) {
					$allowed = false;
				}
				if ($allowed == true) {
					$arr = FileModel::factory ()->select ( "t1.*, (SELECT GROUP_CONCAT(t2.user_id SEPARATOR '~:~') FROM `" . UserFileModel::factory ()->getTable () . "` AS t2 WHERE t2.file_id=t1.id ) AS user_ids" )->find ( $_GET ['id'] )->toArray ( 'user_ids', '~:~' )->getData ();
					if (count ( $arr ) === 0) {
						UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminFiles&action=Index&err=AF08" );
					}
					$user_arr = UserModel::factory ()->where ( 'status', 'T' )->where ( 't1.role_id <> 1' )->orderBy ( 'name ASC' )->findAll ()->getData ();
					$this->set ( 'arr', $arr );
					$this->set ( 'user_arr', $user_arr );
					$this->appendJs ( 'jquery.multiselect.min.js', THIRD_PARTY_PATH . 'multiselect/' );
					$this->appendCss ( 'jquery.multiselect.css', THIRD_PARTY_PATH . 'multiselect/' );
					$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
					$this->appendJs ( 'additional-methods.js', THIRD_PARTY_PATH . 'validate/' );
					$this->appendJs ( 'AdminFiles.js' );
				} else {
					$this->set ( 'status', 2 );
				}
			}
		} else {
			$this->set ( 'status', 2 );
		}
	}
	
	public function TemplateEditor() {
		$this->checkLogin ();
	}
}
?>