<?php
namespace App\Controllers;

use Core\Framework\Components\ImageComponent;
use App\Controllers\Components\UtilComponent;
use App\Models\MultiLangModel;
use App\Plugins\Locale\Models\LocaleModel;
use App\Models\UserModel;
use App\Models\SliderModel;
use App\Models\TagModel;
use Core\Framework\Components\UploadComponent;
use App\Models\RouterModel;
use Core\Framework\Objects;
use App\Models\PageModel;
use App\Models\PCategoryPageModel;
use App\Models\PHistoryModel;
use App\Models\UserPageModel;
use App\Models\PCategoryModel;

class AdminPageController extends AdminController
{
    const FRONTEND_PAGE_CONTROLLER = 'Site';
    
	public function Create() {
		$this->checkLogin ();
		$this->title = "Trang - Thêm trang mới";
		if (isset ( $_POST ['page_create'] )) {
			$pageModel = PageModel::factory ();
			$dataPost = $_POST;
			$valid = true;
			if (isset ( $_FILES ['file'] ) && $_FILES ['file'] ['error'] == 0) {
				$path = $_FILES ['file'] ['name'];
				$extension = pathinfo ( $path, PATHINFO_EXTENSION );
				$allowed_arr = explode ( "|", $this->option_arr ['o_extension_allow'] );
				if (in_array ( $extension, $allowed_arr )) {
					$handle = new UploadComponent ();
					if ($handle->load ( $_FILES ['file'] )) {
						$hash = md5 ( uniqid ( rand (), true ) );
						$date = date('Y/m/d').'/';
						$file_path = PAGE_AVATAR_UPLOAD_PATH . $date. $hash . '.' . $handle->getExtension ();
						if (!is_dir(PAGE_AVATAR_UPLOAD_PATH . $date)) {
							mkdir(PAGE_AVATAR_UPLOAD_PATH . $date, 0777, true);
						}
						if ($handle->save ( $file_path )) {
							//Resize image
							$Image = new ImageComponent();
							$Image->loadImage ( $file_path );
							$d = explode ( 'x', $this->option_arr ['o_artile_avatar_size'] );
							if (isset ( $d [1] ) && is_numeric ( $d [1] )) {
								$Image->resizeSmart ( $d [0], $d [1] );
							} else {
								$Image->resizeToWidth ( $d [0] );
							}
							$Image->saveImage ( $file_path );

							
							$dataPost ['avatar_file'] = $file_path;
						}
					}
				} else {
					$err = 'AF09';
					$valid = false;
				}
			}

			if($valid) {
				$dataPost['on_date'] = date('Y-m-d', strtotime($dataPost['on_date']));
				$dataPost['uuid_code'] = uniqid();
				$id = $pageModel->setAttributes ( $dataPost )->insert ()->getInsertId ();
				if ($id !== false && ( int ) $id > 0) {
				    $locale_arr = LocaleModel::factory()->select('t1.*')
				    ->orderBy('t1.sort ASC')
				    ->findAll()
				    ->getData();
				    $defaultLocale = LocaleModel::factory()->where('is_default', 1)->limit(1)->findAll()->first();
				    $defaultLocaleId = $defaultLocale['id'];
					if (isset ( $_POST ['category_id'] ) && count ( $_POST ['category_id'] ) > 0) {
						$categoryPageModel = PCategoryPageModel::factory ();
						$categoryPageModel->begin ();
						foreach ( $_POST ['category_id'] as $category_id ) {
							$categoryPageModel->reset ()->set ( 'page_id', $id )->set ( 'category_id', $category_id )->insert ();
						}
						$categoryPageModel->commit ();
					}
					$dataHistory = array ();
					$dataHistory ['user_id'] = $this->getUserId ();
					$dataHistory ['page_id'] = $id;
					$dataHistory ['action'] = PHistoryModel::ACTION_ADD;
					$dataHistory ['ip'] = UtilComponent::getClientIp ();
					$dataHistory ['modified'] = date ( 'Y-m-d H:i:s' );
					PHistoryModel::factory ($dataHistory)->insert ()->getInsertId ();
					if (isset ( $dataPost ['i18n'] )) {
						MultiLangModel::factory ()->saveMultiLang($dataPost ['i18n'], $id, 'Page', 'data');
					}
					$userPageModel = UserPageModel::factory ();
					if (isset ( $dataPost ['user_id'] )) {
						$userPageModel->begin ();
						foreach ( $dataPost ['user_id'] as $user_id ) {
							$data = array ();
							$data ['user_id'] = $user_id;
							$data ['page_id'] = $id;
							$userPageModel->reset ()->setAttributes ( $data )->insert ();
						}
						$userPageModel->commit ();
					} else {
						$data = array ();
						$data ['user_id'] = $this->getUserId ();
						$data ['page_id'] = $id;
						$userPageModel->reset ()->setAttributes ( $data )->insert ();
					}

					$uuidNumber = $this->generateRamdomNumber(6);
					foreach ($locale_arr as $locale) {
					    $category_arr = PCategoryModel::factory ()->getNode ( $locale['id'], 1);
					    $pc_arr = PCategoryPageModel::factory()->where('page_id', $id)->findAll()->getDataPair(NULL, 'category_id');
					    $name = $_POST['i18n'][$locale['id']]['page_name'];
					    if (empty($name)) {
					        $name = $_POST['i18n'][$defaultLocaleId]['page_name'];
					    }
					    $friendlyUrl = $this->createFriendlyUrl($category_arr, $pc_arr, $name);
					    $friendlyUrl = $locale['language_iso'].'/'.$friendlyUrl.'-'.$uuidNumber;
					    $this->createOrUpdateDynamicRouter(
					        $friendlyUrl,
					        RouterModel::TYPE_PAGE,
					        self::FRONTEND_PAGE_CONTROLLER,
					        "Page",
					        "id",
					        $id,
					        $locale['id']);
					}

				} else {
					$err = 'AS04';
				}
				
				$locale_arr = LocaleModel::factory()->select('t1.*')
				->orderBy('t1.sort ASC')
				->findAll()
				->getData();
				$uuidNumber = $this->generateRamdomNumber(6);
				foreach ($locale_arr as $locale) {
				    $category_arr = PCategoryModel::factory ()->getNode ( $locale['id'], 1);
				    $pc_arr = PCategoryPageModel::factory()->where('page_id', $id)->findAll()->getDataPair(NULL, 'category_id');
				    $name = $_POST['i18n'][$locale['id']]['page_name'];
				    if (empty($name)) {
				        $name = $_POST['i18n'][1]['name'];
				    }
				    $friendlyUrl = $this->createFriendlyUrl($category_arr, $pc_arr, $name);
				    $friendlyUrl = $locale['language_iso'].'/'.$friendlyUrl.'-'.$uuidNumber;
				    $this->createOrUpdateDynamicRouter(
				        $friendlyUrl,
				        RouterModel::TYPE_PAGE,
				        self::FRONTEND_PAGE_CONTROLLER,
				        "Page",
				        "id",
				        $id,
				        $locale['id']);
				}

				UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=AdminPage&action=Index" );
			}


		} else {
			$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
			$lp_arr = array ();
			foreach ( $locale_arr as $item ) {
				$lp_arr [$item ['id'] . "_"] = $item ['file'];
			}
			$this->set ( 'lp_arr', $locale_arr );
			$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
			$user_arr = UserModel::factory ()->where ( "t1.status", "T" )->where ( 't1.role_id <> 1' )->orderBy ( "t1.name ASC" )->findAll ()->getData ();
			$this->set ( 'user_arr', $user_arr );
			if (isset ( $_GET ['id'] )) {
				$arr = PageModel::factory ()->select ( "t1.*, (SELECT GROUP_CONCAT(t2.user_id SEPARATOR '~:~') FROM `" . UserPageModel::factory ()->getTable () . "` AS t2 WHERE t2.page_id=t1.id ) AS user_ids" )->find ( $_GET ['id'] )->toArray ( 'user_ids', '~:~' )->getData ();
				$arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $arr ['id'], 'Page' );
				$this->set ( 'arr', $arr );
			}
			$this->set ( 'category_arr', PCategoryModel::factory ()->getNode ( $this->getLocaleId (), 1) );
			$this->set ( 'galleries', SliderModel::factory()
					->join('MultiLang', "t2.model='Slider' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->findAll()->getData());
			$this->set('tag_arr', TagModel::factory()->findAll()->getData());
			$this->appendJs ( 'tinymce.min.js', THIRD_PARTY_PATH . 'tiny_mce/' );
			$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
			$this->appendJs ( 'additional-methods.js', THIRD_PARTY_PATH . 'validate/' );
			$this->appendJs ( 'jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/' );
			$this->appendJs ( 'jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/' );
			$this->appendCss ( 'jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/' );
			$this->appendJs ( 'jquery.multiselect.min.js', THIRD_PARTY_PATH . 'multiselect/' );
			$this->appendCss ( 'jquery.multiselect.css', THIRD_PARTY_PATH . 'multiselect/' );
			$this->appendJs ( 'chosen.jquery.js', THIRD_PARTY_PATH . 'harvest/chosen/' );
			$this->appendCss ( 'chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
			$this->appendJs ( "AdminPage.js" );
		}
	}


	public function DeletePage() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$response = array ();
			$allowed = true;
			$page = PageModel::factory()->find((int)$_GET ['id'])->getData();
			if ($allowed == true) {
				if (PageModel::factory ()->setAttributes ( array (
						'id' => $_GET ['id']
				) )->erase ()->getAffectedRows () == 1) {
					$multiLangModel = MultiLangModel::factory ();
					$historyModel = PHistoryModel::factory ();
					$multiLangModel->where ( 'model', 'Page' )->where ( 'foreign_id', $_GET ['id'] )->eraseAll ();
					UserPageModel::factory ()->where ( 'page_id', $_GET ['id'] )->eraseAll ();
					$_arr = $historyModel->where ( 'page_id', $_GET ['id'] )->findAll ()->getDataPair ( 'id', 'id' );
					if (! empty ( $page )) {
						$multiLangModel->reset ()->where ( 'model', 'PHistory' )->whereIn ( 'foreign_id', $_arr )->eraseAll ();
					}
				    $historyModel->reset ()->where ( 'page_id', $_GET ['id'] )->eraseAll ();
					$response ['code'] = 200;
				} else {
					$response ['code'] = 100;
				}
			} else {
				$response ['code'] = 100;
			}
			$this->deleteRouter(RouterModel::TYPE_PAGE, self::FRONTEND_PAGE_CONTROLLER, 'Page', $_GET['id']);
			AppController::jsonResponse ( $response );
		}
		exit ();
	}

	public function DeletePageBulk() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
				$multiLangModel = MultiLangModel::factory ();
				$historyModel = PHistoryModel::factory ();
				$multiLangModel->where ( 'model', 'Page' )->whereIn ( 'foreign_id', $_POST ['record'] )->eraseAll ();
				//UserPageModel::factory ()->whereIn ( 'page_id', $_POST ['record'] )->eraseAll ();
				PageModel::factory ()->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
				$_arr = $historyModel->whereIn ( 'page_id', $_POST ['record'] )->findAll ()->getDataPair ( 'id', 'id' );
				if (! empty ( $_arr )) {
					$multiLangModel->reset ()->where ( 'model', 'PHistory' )->whereIn ( 'foreign_id', $_arr )->eraseAll ();
				}
				$historyModel->reset ()->whereIn ( 'page_id', $_POST ['record'] )->eraseAll ();
				
				foreach ($_POST['record'] as $id) {
				    $this->deleteRouter(RouterModel::TYPE_PAGE, self::FRONTEND_PAGE_CONTROLLER, 'Page', $id);
				}
			}
		}
		exit ();
	}
	public function GetPage() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$pageModel = PageModel::factory ()
			->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Page' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'page_name'", 'left' );

			if (isset ( $_GET ['is_active'] ) && ! empty ( $_GET ['is_active'] )) {
				$isActive = Objects::escapeString ( $_GET ['is_active'] );
				$pageModel->where ( 't1.status', $isActive );
			}
			if (isset ( $_GET ['q'] ) && ! empty ( $_GET ['q'] )) {
				$q = Objects::escapeString ( $_GET ['q'] );
				$pageModel->where ( 't2.content LIKE', "%$q%" );
			}

			if (isset ( $_GET ['category_id'] ) && ! empty ( $_GET ['category_id'] )) {
				$categoryId = Objects::escapeString ( $_GET ['category_id'] );
				$pageModel->where(sprintf("t1.id IN (select `page_id` from `%1\$s` where `category_id` = $categoryId )", PCategoryPageModel::factory()->getTable ()) );
			}

			if (isset ( $_GET ['status'] ) && ! empty ( $_GET ['status'] )) {
				$status = Objects::escapeString ( $_GET ['status'] );
				$pageModel->where ( 't1.status', $status);
			}
			if (isset ( $_GET ['fromDate'] ) && ! empty ( $_GET ['fromDate'] )) {
				$fromDate = Objects::escapeString ( $_GET ['fromDate'] );
				$fromDate = date('Y-m-d', strtotime($fromDate));
				$pageModel->where ( 't1.on_date >=', $fromDate );
			}

			if (isset ( $_GET ['toDate'] ) && ! empty ( $_GET ['toDate'] )) {
				$toDate = Objects::escapeString ( $_GET ['toDate'] );
				$toDate = date('Y-m-d', strtotime($toDate));
				$pageModel->where ( 't1.on_date <=', $toDate );
			}

			if (isset ( $_GET ['template'] ) && ! empty ( $_GET ['template'] )) {
				$template = Objects::escapeString ( $_GET ['template'] );
				$pageModel->where ( 't1.template', $template);
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
			$total = $pageModel->findCount ()->getData ();
			$rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 20;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}

			$data = $pageModel->select (sprintf("t1.*, t2.content AS page_name, (SELECT group_concat(ML.content  SEPARATOR '<br/>') FROM `%1\$s` as SC JOIN `%2\$s` as ML ON ML.foreign_id = SC.category_id AND ML.model = 'PCategory' AND ML.locale = '" . $this->getLocaleId () . "' AND ML.field = 'name' WHERE SC.page_id = t1.id) AS category", PCategoryPageModel::factory()->getTable (), MultiLangModel::factory()->getTable()))->orderBy ( "`$column` $direction" )->limit ( $rowCount, $offset )->findAll ()->getData ();
			foreach ( $data as $k => $v ) {
				if (empty ( $v ['url'] )) {
					$v ['url'] = 'index.php?controller=AdminPage&action=Preview&id=' . $v ['id'];
				}
				if (! empty ( $v ['modified'] )) {
					$v ['modified'] = UtilComponent::formatDate ( date ( 'Y-m-d', strtotime ( $v ['modified'] ) ), 'Y-m-d', $this->option_arr ['o_date_format'] ) . ', ' . UtilComponent::formatTime ( date ( 'H:i:s', strtotime ( $v ['modified'] ) ), 'H:i:s', $this->option_arr ['o_time_format'] );
				} else {
					$v ['modified'] = __ ( 'lblNA', true );
				}
				$data [$k] = $v;
			}
			AppController::jsonResponse ( compact ( 'data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction' ) );
		}
		exit ();
	}

	public function Index() {
		$this->checkLogin ();
		$this->title = "Trang - Danh sách trang";
		$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
		$this->appendJs ('AdminPage.js' );
		$category_arr = PCategoryModel::factory ()->getNode ( $this->getLocaleId (), 1) ;
		$this->set ( 'category_arr', $category_arr );
		$this->appendJs ( 'chosen.jquery.js', THIRD_PARTY_PATH . 'harvest/chosen/' );
		$this->appendCss ( 'chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
	}
	public function SavePage() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$pageModel = PageModel::factory ();
			if (! in_array ( $_POST ['column'], $pageModel->getI18n () )) {
				$pageModel->where ( 'id', $_GET ['id'] )->limit ( 1 )->modifyAll ( array (
						$_POST ['column'] => $_POST ['value']
				) );
			} else {
				MultiLangModel::factory ()->updateMultiLang ( array (
						$this->getLocaleId () => array (
								$_POST ['column'] => $_POST ['value']
						)
				), $_GET ['id'], 'Page', 'data' );
			}
		}
		exit ();
	}

	public function Update() {
		$this->checkLogin ();
		$this->title = "Trang - Cập nhật trang";
		$pageId = null;
		if(isset($_GET['id'])) {
			$pageId = (int)$_GET['id'];
		} elseif (isset($_POST['id'])) {
			$pageId = (int)$_POST['id'];
		}
		PageModel::factory()->find($pageId)->getData();
		if (isset ( $_POST ['page_update'] )) {
			$dataPost = $_POST;
			$valid = true;
			if (isset ( $_FILES ['file'] ) && $_FILES ['file'] ['error'] == 0) {
				$path = $_FILES ['file'] ['name'];
				$extension = pathinfo ( $path, PATHINFO_EXTENSION );
				$allowed_arr = explode ( "|", $this->option_arr ['o_extension_allow'] );
				if (in_array ( $extension, $allowed_arr )) {
					$handle = new UploadComponent();
					if ($handle->load ( $_FILES ['file'] )) {
						$hash = md5 ( uniqid ( rand (), true ) );
						$date = date('Y/m/d').'/';
						$file_path = PAGE_AVATAR_UPLOAD_PATH . $date. $hash . '.' . $handle->getExtension ();
						if (!is_dir(PAGE_AVATAR_UPLOAD_PATH . $date)) {
							mkdir(PAGE_AVATAR_UPLOAD_PATH . $date, 0777, true);
						}
						if ($handle->save ( $file_path )) {
								// Resize image
							$Image = new ImageComponent ();
							$Image->loadImage ( $file_path );
							$d = explode ( 'x', $this->option_arr ['o_artile_avatar_size'] );
							if (isset ( $d [1] ) && is_numeric ( $d [1] )) {
								$Image->resizeSmart ( $d [0], $d [1] );
							} else {
								$Image->resizeToWidth ( $d [0] );
							}
							$Image->saveImage ( $file_path );

							$dataPost ['avatar_file'] = $file_path;

						}
					}
				} else {
					$valid = false;
				}
			}

			if ($valid) {
				if(!is_null($_SESSION['page_category_type'])) {
					$dataPost['category_type'] = $_SESSION['page_category_type'];
				}
				$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();

				$dataPost['on_date'] = date('Y-m-d', strtotime($dataPost['on_date']));
				PageModel::factory ()->where ( 'id', $dataPost ['id'] )->limit ( 1 )->modifyAll ( array_merge ( $dataPost, array (
						'modified' => date ( 'Y-m-d H:i:s' )
				) ) );
				PCategoryPageModel::factory ()->where ( 'page_id', $_POST ['id'] )->eraseAll ();
				if (isset ( $_POST ['category_id'] ) && count ( $_POST ['category_id'] ) > 0) {
					$categoryPageModel = PCategoryPageModel::factory ();
					$categoryPageModel->begin ();
					foreach ( $_POST ['category_id'] as $category_id ) {
						$categoryPageModel->reset ()->set ( 'page_id', $_POST ['id'] )->set ( 'category_id', $category_id )->insert ();
					}
					$categoryPageModel->commit ();
				}
				$data = array ();
				$data ['user_id'] = $this->getUserId ();
				$data ['page_id'] = $dataPost ['id'];
				$data ['action'] = PHistoryModel::ACTION_UPDATE;
				$data ['ip'] = UtilComponent::getClientIp ();
				$data ['modified'] = date ( 'Y-m-d H:i:s' );
				$hid = PHistoryModel::factory ( $data )->insert ()->getInsertId ();
				if ($hid !== false && ( int ) $hid > 0) {
					$i18n_arr = MultiLangModel::factory ()->getMultiLang ( $dataPost ['id'], 'Page' );
					$locale_arr = LocaleModel::factory ()->select ( 't1.*' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
					foreach ( $locale_arr as $item ) {
						unset ( $i18n_arr [$item ['id']] ['page_name'] );
					}
					if (! empty ( $i18n_arr )) {
						MultiLangModel::factory ()->updateMultiLang ( $i18n_arr, $hid, 'PHistory', 'data' );
					}
				}
				if (isset ( $dataPost ['i18n'] )) {
					MultiLangModel::factory ()->saveMultiLang ( $dataPost ['i18n'], $dataPost ['id'], 'Page', 'data' );
				}
				$userPageModel = UserPageModel::factory ();
				$userPageModel->where ( 'page_id', $dataPost ['id'] )->eraseAll ();
				if (isset ( $dataPost ['user_id'] )) {
					$userPageModel->reset ()->begin ();
					foreach ( $dataPost ['user_id'] as $user_id ) {
						$data = array ();
						$data ['user_id'] = $user_id;
						$data ['page_id'] = $dataPost ['id'];
						$userPageModel->reset ()->setAttributes ( $data )->insert ();
					}
					$userPageModel->commit ();
				} else {
					$data = array ();
					$data ['user_id'] = $this->getUserId ();
					$data ['page_id'] = $dataPost ['id'];
					$userPageModel->reset ()->setAttributes ( $data )->insert ();
				}

				$locale_arr = LocaleModel::factory()->select('t1.*')
				->orderBy('t1.sort ASC')
				->findAll()
				->getData();
				$uuidNumber = $this->generateRamdomNumber(6);
				foreach ($locale_arr as $locale) {
				    $category_arr = PCategoryModel::factory ()->getNode ( $locale['id'], 1);
				    $pc_arr = PCategoryPageModel::factory()->where('page_id', $_POST['id'])->findAll()->getDataPair(null, 'category_id');
				    
				    if (empty($_POST['i18n'][$locale['id']]['url'])) {
				        $friendlyUrl = $this->createFriendlyUrl($category_arr, $pc_arr, $_POST['i18n'][$locale['id']['page_name']]);
				        $friendlyUrl = $locale['language_iso'].'/'.$friendlyUrl.'-'.$uuidNumber;
				    } else {
				        $friendlyUrl = $_POST['i18n'][$locale['id']]['url'];
				    }
				    
				    $this->createOrUpdateDynamicRouter(
				        $friendlyUrl,
				        RouterModel::TYPE_PAGE,
				        self::FRONTEND_PAGE_CONTROLLER,
				        "Page",
				        "id",
				        $_POST['id'],
				        $locale['id']);
				}
				UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminPage&action=Update&id=" . $dataPost ['id'] . "&err=AS01" );
			}


		} else {
			$arr = PageModel::factory ()->select ( "t1.*, (SELECT GROUP_CONCAT(t2.user_id SEPARATOR '~:~') FROM `" . UserPageModel::factory ()->getTable () . "` AS t2 WHERE t2.page_id=t1.id ) AS user_ids,  (SELECT COUNT(t3.page_id) FROM `" . PHistoryModel::factory ()->getTable () . "` AS t3 WHERE t3.page_id=t1.id) as changes" )->find ( $_GET ['id'] )->toArray ( 'user_ids', '~:~' )->getData ();
			if (count ( $arr ) === 0) {
				UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminPage&action=Index&err=AS08" );
			}
			$arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $arr ['id'], 'Page' );
			$routerType = RouterModel::TYPE_PAGE;
			$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
			$lp_arr = array ();
			foreach ( $locale_arr as $item ) {
				$lp_arr [$item ['id'] . "_"] = $item ['file'];
				$router = RouterModel::factory()->where('t1.type', $routerType)->where('t1.foreign_id', (int)$_GET['id'])->where('t1.locale_id', $item['id'])->findAll()->first();
				if (!empty($router)) {
				    $arr['url'][$item['id']] = $router['url'];
				}
			}
			$this->set ( 'arr', $arr );
			$this->set ( 'lp_arr', $locale_arr );
			$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
			$user_arr = UserModel::factory ()->where ( 'status', 'T' )->where ( 't1.role_id <> 1' )->orderBy ( 'name ASC' )->findAll ()->getData ();
			$this->set ( 'user_arr', $user_arr );
			$category_arr = PCategoryModel::factory ()->getNode ( $this->getLocaleId (), 1) ;
			$this->set ( 'category_arr', $category_arr );
			$this->set ( 'sc_arr', PCategoryPageModel::factory ()->where ( 't1.page_id', $arr ['id'] )->orderBy ( 't1.category_id ASC' )->findAll ()->getDataPair ( 'category_id', 'category_id' ) );
			if (! empty ( $arr ['modified'] )) {
				$_arr = PHistoryModel::factory ()->join ( 'User', "t2.id = t1.user_id", 'left' )->select ( 't1.*, t2.name' )->where ( 'page_id', $_GET ['id'] )->orderBy ( 't1.modified DESC' )->limit ( 1 )->findAll ()->getData ();
				$this->set ( 'history_arr', !empty($_arr [0])? $_arr [0] : []  );
			}
			$this->set ( 'galleries', SliderModel::factory()
					->join('MultiLang', "t2.model='Slider' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->findAll()->getData());

			$this->appendJs ( 'tinymce.min.js', THIRD_PARTY_PATH . 'tiny_mce/' );
			$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
			$this->appendJs ( 'additional-methods.js', THIRD_PARTY_PATH . 'validate/' );
			$this->appendJs ( 'jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/' );
			$this->appendJs ( 'jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/' );
			$this->appendCss ( 'jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/' );
			$this->appendJs ( 'jquery.multiselect.min.js', THIRD_PARTY_PATH . 'multiselect/' );
			$this->appendCss ( 'jquery.multiselect.css', THIRD_PARTY_PATH . 'multiselect/' );
			$this->appendJs ( 'chosen.jquery.js', THIRD_PARTY_PATH . 'harvest/chosen/' );
			$this->appendCss ( 'chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
			$this->appendJs ( "AdminPage.js" );

		}
	}
	public function Install() {
		$this->checkLogin ();
		if ($this->isAdmin () || $this->isEditor ()) {
			$allowed = true;
			if ($this->isEditor () && ! in_array ( $_GET ['id'], $this->getAllowedPageIds ( $this->getUserId () ) )) {
				$allowed = false;
			}
			if ($allowed == true) {
				$arr = PageModel::factory ()->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Page' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'page_name'", 'left' )->select ( 't1.*, t2.content as page_name' )->find ( $_GET ['id'] )->getData ();
				if (count ( $arr ) === 0) {

					UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminPage&action=Index&err=AS08" );
				}
				$this->set ( 'arr', $arr );
				$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.title' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left outer' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
				$this->set ( 'locale_arr', $locale_arr );
				$this->appendJs ('AdminPageController.js' );
			} else {
				$this->set ( 'status', 2 );
			}
		} else {
			$this->set ( 'status', 2 );
		}
	}

	public function Preview() {

		$this->setLayout ( 'Empty' );
		$allowed = true;
		if ($this->isEditor () && ! in_array ( $_GET ['id'], $this->getAllowedPageIds ( $this->getUserId () ) )) {
			$allowed = false;
		}
		if ($allowed == true) {
			$arr = PageModel::factory ()->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Page' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'page_content'", 'left' )->select ( 't1.*, t2.content as page_content' )->find ( $_GET ['id'] )->getData ();
			if (count ( $arr ) === 0) {
				UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminPage&action=Index&err=AS08" );
			}
			$this->set ( 'arr', $arr );
		}
	}

	public function PreviewHistory() {

		$this->setLayout ( 'Empty' );
		$allowed = true;
		if ($this->isEditor () && ! in_array ( $_GET ['id'], $this->getAllowedPageIds ( $this->getUserId () ) )) {
			$allowed = false;
		}
		if ($allowed == true) {
			$arr = PHistoryModel::factory ()->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'PHistory' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'page_content'", 'left' )->select ( 't1.*, t2.content as page_content' )->find ( $_GET ['id'] )->getData ();
			$this->set ( 'arr', $arr );
		}
	}
	public function History() {
		$this->checkLogin ();
		if ($this->isAdmin ()) {
			$allowed = true;
			if ($this->isEditor () && ! in_array ( $_GET ['page_id'], $this->getAllowedPageIds ( $this->getUserId () ) )) {
				$allowed = false;
			}
			if ($allowed == true) {
					
				$arr = PageModel::factory ()->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Page' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'page_name'", 'left' )->select ( 't1.*, t2.content as page_name' )->findAll ()->getData ();
				$this->set ( 'arr', $arr );
				$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
				$this->appendJs ( 'AdminPage.js' );
				$this->appendJs ( 'chosen.jquery.js', THIRD_PARTY_PATH . 'harvest/chosen/' );
				$this->appendCss ( 'chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/' );
			} else {
				$this->set ( 'status', 2 );
			}
		} else {
			$this->set ( 'status', 2 );
		}
	}

	public function GetHistory() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$historyModel = PHistoryModel::factory ()->join ( 'MultiLang', "t2.foreign_id = t1.page_id AND t2.model = 'Page' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'page_name'", 'left' )->join ( 'User', "t3.id = t1.user_id", 'left' );
			$pageRecords = PageModel::factory()
			->select('t1.id')
			->where('category_type', $_SESSION['page_category_type'])
			->findAll()
			->getData();
			$pageIds = array();
			if(!empty($pageRecords)) {
				foreach ($pageRecords as $r) {
					$pageIds[] = $r['id'];
				}
			}
			if(!empty($pageIds)) {
				$historyModel->whereIn('t1.page_id', $pageIds);
			}

			if (isset ( $_GET ['q'] ) && ! empty ( $_GET ['q'] )) {
				$q = Objects::escapeString ( $_GET ['q'] );
				$historyModel->where ( 't2.content LIKE', "%$q%" );
			}
			if (isset ( $_GET ['page_id'] ) && ! empty ( $_GET ['page_id'] )) {
				$page_id = Objects::escapeString ( $_GET ['page_id'] );
				$historyModel->where ( "(t1.page_id='" . $page_id . "')" );
			}
			$column = 'modified';
			$direction = 'DESC';
			if (isset ( $_GET ['direction'] ) && isset ( $_GET ['column'] ) && in_array ( strtoupper ( $_GET ['direction'] ), array (
					'ASC',
					'DESC'
			) )) {
				$column = $_GET ['column'];
				$direction = strtoupper ( $_GET ['direction'] );
			}
			$total = $historyModel->findCount ()->getData ();
			$rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 20;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}
			$data = $historyModel->select ( 't1.*, t2.content AS page_name, t3.name' )->orderBy ( "`$column` $direction" )->limit ( $rowCount, $offset )->findAll ()->getData ();
			foreach ( $data as $k => $v ) {
				$v ['modified'] = UtilComponent::formatDate ( date ( 'Y-m-d', strtotime ( $v ['modified'] ) ), 'Y-m-d', $this->option_arr ['o_date_format'] ) . ', ' . UtilComponent::formatTime ( date ( 'H:i:s', strtotime ( $v ['modified'] ) ), 'H:i:s', $this->option_arr ['o_time_format'] );
				$v ['title'] = __ ( 'lblPageContent', true, false ) . ' ' . __ ( 'lblBeforeChanged', true ) . ' ' . $v ['modified'] . ' ' . __ ( 'lblBy', true ) . ' ' . $v ['name'];
				$data [$k] = $v;
			}
			AppController::jsonResponse ( compact ( 'data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction' ) );
		}
		exit ();
	}

	public function DeleteHistory() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$response = array ();
			$historyModel = PHistoryModel::factory ();
			$arr = $historyModel->find ( $_GET ['id'] )->getData ();
			$allowed = true;
			if ($this->isEditor () && ! in_array ( $arr ['id'], $this->getAllowedPageIds ( $this->getUserId () ) )) {
				$allowed = false;
			}
			if ($allowed == true) {
				if ($historyModel->reset ()->setAttributes ( array (
						'id' => $_GET ['id']
				) )->erase ()->getAffectedRows () == 1) {
					MultiLangModel::factory ()->where ( 'model', 'PHistory' )->where ( 'foreign_id', $_GET ['id'] )->eraseAll ();
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

	public function DeleteHistoryBulk() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
				MultiLangModel::factory ()->where ( 'model', 'PHistory' )->whereIn ( 'foreign_id', $_POST ['record'] )->eraseAll ();
				PHistoryModel::factory ()->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
			}
		}
		exit ();
	}
	public function View() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$allowed = true;
			if ($this->isEditor () && ! in_array ( $_GET ['id'], $this->getAllowedPageIds ( $this->getUserId () ) )) {
				$allowed = false;
			}
			if ($allowed == true) {
				$arr = PHistoryModel::factory ()->find ( $_GET ['id'] )->getData ();
				$arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $arr ['id'], 'PHistory' );
				$this->set ( 'arr', $arr );
				$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file, t2.title' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
				$this->set ( 'lp_arr', $locale_arr );
			}
		}
	}
	public function Restore() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (isset ( $_GET ['id'] ) && $_GET ['page_id']) {
				$allowed = true;
				if ($this->isEditor () && ! in_array ( $_GET['page_id'], $this->getAllowedPageIds ( $this->getUserId () ) )) {
					$allowed = false;
				}
				if ($allowed == true) {
					$multiLangModel = MultiLangModel::factory ();
					$i18n_arr = $multiLangModel->getMultiLang ( $_GET ['id'], 'PHistory' );
					if (! empty ( $i18n_arr )) {
						$multiLangModel->reset ()->updateMultiLang ( $i18n_arr, $_GET ['page_id'], 'Page', 'data' );
					}
				}
			}
		}
		exit ();
	}

	public function isPageAllowed(){
		$this->setAjax (true );
		return true;
	}

	public function DeleteImage() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$response = array ();
			$pageModel = PageModel::factory ();
			$arr = $pageModel->find ( $_GET ['id'] )->getData ();
			if (! empty ( $arr )) {
				if (! empty ( $arr ['avatar_file'] )) {
					@unlink ( INSTALL_PATH . $arr ['avatar_file'] );
				}
				$data = array ();
				$data ['avatar_file'] = ':NULL';
				$pageModel->reset ()->where ( array (
						'id' => $_GET ['id']
				) )->limit ( 1 )->modifyAll ( $data );
				$response ['code'] = 200;
			} else {
				$response ['code'] = 100;
			}
			AppController::jsonResponse ( $response );
		}
	}
}