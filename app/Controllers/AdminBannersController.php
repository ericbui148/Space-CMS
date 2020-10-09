<?php
namespace App\Controllers;

use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\UploadComponent;
use App\Models\BannerModel;
use Core\Framework\Components\CSVComponent;

class AdminBannersController extends AdminController {

	public function Create() {
		$this->checkLogin ();
		if ($this->isAdmin () || ($this->isEditor ())) {
			$post_max_size = UtilComponent::getPostMaxSize ();
			if ($_SERVER ['REQUEST_METHOD'] == 'POST' && isset ( $_SERVER ['CONTENT_LENGTH'] ) && ( int ) $_SERVER ['CONTENT_LENGTH'] > $post_max_size) {
				UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminBanners&action=Index&err=AF05" );
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
							$date = date('Y/m/d').'/';
							$file_path = BANNER_UPLOAD_PATH . $date . $hash . '.' . $handle->getExtension ();
							if (!is_dir(BANNER_UPLOAD_PATH . $date)) {
								mkdir(BANNER_UPLOAD_PATH . $date, 0777, true);
							}
							if ($handle->save ( $file_path )) {
								$data ['name'] = $_POST['name'];
								$data ['link'] = $_POST['link'];
								$data ['position'] = $_POST['position'];
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
					$bannerModel = BannerModel::factory ();
					$id = $bannerModel->setAttributes ( $data )->insert ()->getInsertId ();
					if ($id !== false && ( int ) $id > 0) {
					} else {
						if (isset ( $data ['file_path'] )) {
							@unlink ( UPLOAD_PATH . $data ['file_path'] );
						}
						$err = 'AF04';
					}
				}
				UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=AdminBanners&action=Index&err=$err" );
			} else {
				$this->appendJs ( 'jquery.multiselect.min.js', THIRD_PARTY_PATH . 'multiselect/' );
				$this->appendCss ( 'jquery.multiselect.css', THIRD_PARTY_PATH . 'multiselect/' );
				$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
				$this->appendJs ( 'additional-methods.js', THIRD_PARTY_PATH . 'validate/' );
				$this->appendJs ( 'AdminBanners.js' );
			}
		} else {
			$this->set ( 'status', 2 );
		}
	}
	public function Index() {
		$this->checkLogin ();
		if ($this->isAdmin () || $this->isEditor ()) {
			$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
			$this->appendJs ( 'AdminBanners.js' );
		} else {
			$this->set ( 'status', 2 );
		}
	}
	public function DeleteBanner() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$response = array ();
			if ($this->isAdmin () || $this->isEditor ()) {
				$allowed = true;
				if ($this->isEditor () && ! in_array ( $_GET ['id'], $this->getAllowedFileIds ( $this->getUserId () ) )) {
					$allowed = false;
				}
				if ($allowed == true) {
					$bannerModel = BannerModel::factory ();
					$arr = $bannerModel->find ( $_GET ['id'] )->getData ();
					if ($bannerModel->reset ()->setAttributes ( array (
							'id' => $_GET ['id'] 
					) )->erase ()->getAffectedRows () == 1) {
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

	public function DeleteBannerBulk() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if ($this->isAdmin () || $this->isEditor ()) {
				if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
					$bannerModel = BannerModel::factory ();
					$file_arr = $bannerModel->whereIn ( 'id', $_POST ['record'] )->findAll ()->getData ();
					foreach ( $file_arr as $f ) {
						$file_path = $f ['file_path'];
						if (file_exists ( INSTALL_PATH . $file_path )) {
							@unlink ( INSTALL_PATH . $file_path );
						}
					}
					$bannerModel->reset ()->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
				}
			}
		}
		exit ();
	}
	public function ExportBanner() {
		$this->checkLogin ();
		if (isset ( $_POST ['record'] ) && is_array ( $_POST ['record'] )) {
			$arr = BannerModel::factory ()->whereIn ( 'id', $_POST ['record'] )->findAll ()->getData ();
			$csv = new CSVComponent();
			$csv->setHeader ( true )->setName ( "Files-" . time () . ".csv" )->process ( $arr )->download ();
		}
		exit ();
	}

	public function GetBanner() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$bannerModel = BannerModel::factory ();
			if (isset ( $_GET ['q'] ) && ! empty ( $_GET ['q'] )) {
				$q = $bannerModel->escapeStr ( $_GET ['q'] );
				$q = str_replace ( array (
						'%',
						'_' 
				), array (
						'\%',
						'\_' 
				), trim ( $q ) );
				$bannerModel->where ( "(t1.name LIKE '%$q%')" );
			}
			if (isset ( $_GET ['status'] ) && ! empty ( $_GET ['status'] ) && in_array ( $_GET ['status'], array (
					'T',
					'F' 
			) )) {
				$bannerModel->where ( 't1.status', $_GET ['status'] );
			}
			if ($this->isEditor ()) {
				$file_ids_arr = $this->getAllowedFileIds ( $this->getUserId () );
				if (! empty ( $file_ids_arr )) {
					$bannerModel->whereIn ( 't1.id', $file_ids_arr );
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
			$total = $bannerModel->findCount ()->getData ();
			$rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 10;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}
			$data = $bannerModel->select ( 't1.*' )->orderBy ( "$column $direction" )->limit ( $rowCount, $offset )->findAll ()->getData ();
			foreach ( $data as $k => $v ) {
				$v ['created'] = UtilComponent::formatDate ( date ( 'Y-m-d', strtotime ( $v ['created'] ) ), 'Y-m-d', $this->option_arr ['o_date_format'] ) . ', ' . UtilComponent::formatTime ( date ( 'H:i:s', strtotime ( $v ['created'] ) ), 'H:i:s', $this->option_arr ['o_time_format'] );
				$data [$k] = $v;
			}
			AppController::jsonResponse ( compact ( 'data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction' ) );
		}
		exit ();
	}

	public function StatusBanner() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
				BannerModel::factory ()->whereIn ( 'id', $_POST ['record'] )->modifyAll ( array (
						'status' => ":IF(`status`='F','T','F')" 
				) );
			}
		}
		exit ();
	}

	public function Update() {

		$this->checkLogin ();
		if ($this->isAdmin () || $this->isEditor () ) {
			$post_max_size = UtilComponent::getPostMaxSize ();
			if ($_SERVER ['REQUEST_METHOD'] == 'POST' && isset ( $_SERVER ['CONTENT_LENGTH'] ) && ( int ) $_SERVER ['CONTENT_LENGTH'] > $post_max_size) {
				UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminBanners&action=Index&err=AF05" );
			}
			if (isset ( $_POST ['file_update'] )) {
				$bannerModel = BannerModel::factory ();
				$arr = $bannerModel->find ( $_POST ['id'] )->getData ();
				$err = 'AF01';
				$data = array ();
				if (isset ( $_FILES ['file'] ) && $_FILES ['file'] ['error'] == 0) {
					$path = $_FILES ['file'] ['name'];
					$extension = pathinfo ( $path, PATHINFO_EXTENSION );
					$allowed_arr = explode ( "|", $this->option_arr ['o_extension_allow'] );
					if (in_array ( $extension, $allowed_arr )) {
						$handle = new UploadComponent();
						if ($handle->load ( $_FILES ['file'] )) {
							$hash = md5 ( uniqid ( rand (), true ) );
							$date = date('Y/m/d').'/';
							$file_path = BANNER_UPLOAD_PATH . $date . $hash . '.' . $handle->getExtension ();
							if (!is_dir(BANNER_UPLOAD_PATH . $date)) {
								mkdir(BANNER_UPLOAD_PATH . $date, 0777, true);
							}
							if ($handle->save ( $file_path )) {
								$data ['file_path'] = $file_path;
								$data ['file_name'] = $_FILES ['file'] ['name'];
								$data ['mime_type'] = $_FILES ['file'] ['type'];
								$data ['hash'] = $hash;
								$data ['size'] = UtilComponent::formatSizeUnits ( $_FILES ['file'] ['size'] );
					
							}
							$file_path = $arr ['file_path'];
							if (file_exists ( INSTALL_PATH . $file_path )) {
								@unlink ( INSTALL_PATH . $file_path );
							}
						}
					} else {
						$err = 'AF10';
					}
				} else if ($_FILES ['file'] ['error'] != 4) {
					$err = 'AF10';
				}
				$data ['name'] = $_POST['name'];
				$data ['link'] = $_POST['link'];
				$data ['position'] = $_POST['position'];
				$bannerModel->reset ()->set ( 'id', $_POST ['id'] )->modify ( $data );
				if ($err == 'AF01') {
					UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminBanners&action=Index&err=$err" );
				} else {
					UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminBanners&action=Update&id=" . $_POST ['id'] . "&err=$err" );
				}
			} else {
				$arr = BannerModel::factory ()->select ( "t1.*" )->find ( $_GET ['id'] )->toArray ( 'user_ids', '~:~' )->getData ();
				if (count ( $arr ) === 0) {
					UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminBanners&action=Index&err=AF08" );
				}
				$this->set ( 'arr', $arr );
				$this->appendJs ( 'jquery.multiselect.min.js', THIRD_PARTY_PATH . 'multiselect/' );
				$this->appendCss ( 'jquery.multiselect.css', THIRD_PARTY_PATH . 'multiselect/' );
				$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
				$this->appendJs ( 'additional-methods.js', THIRD_PARTY_PATH . 'validate/' );
				$this->appendJs ( 'AdminBanners.js' );
			}
		} else {
			$this->set ( 'status', 2 );
		}
	}
}
?>