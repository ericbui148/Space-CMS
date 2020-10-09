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
use App\Models\ArticleModel;
use App\Models\ACategoryArticleModel;
use App\Models\UserArticleModel;
use App\Models\ArticleTagModel;
use App\Models\ACategoryModel;
use App\Models\AHistoryModel;

class AdminArticleController extends AdminController
{	
    const FRONTEND_ARTICLE_CONTROLLER = 'Site';
    
	public function Create() {
		$this->checkLogin ();
		$this->title = "Bài viết - Thêm bài viết";
		if (isset ( $_POST ['article_create'] )) {
			$articleModel = ArticleModel::factory ();
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
						$file_path = ARTICLE_AVATAR_UPLOAD_PATH . $date. $hash . '.' . $handle->getExtension ();
						if (!is_dir(ARTICLE_AVATAR_UPLOAD_PATH . $date)) {
							mkdir(ARTICLE_AVATAR_UPLOAD_PATH . $date, 0777, true);
						}
						if ($handle->save ( $file_path )) {
							//Resize image
							$Image = new ImageComponent();
							$Image->loadImage($file_path);
							if (!empty($this->option_arr['o_artile_avatar_size'])) {
							    $d = explode('x', $this->option_arr['o_artile_avatar_size']);
							    if (isset($d[1]) && is_numeric($d[1])) {
							        $Image->resizeSmart ( $d [0], $d [1] );
							    } else {
							        $Image->resizeToWidth ( $d [0] );
							    }
							}

							$Image->saveImage($file_path);
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
				$id = $articleModel->setAttributes ( $dataPost )->insert ()->getInsertId ();
				if ($id !== false && ( int ) $id > 0) {
				    $locale_arr = LocaleModel::factory()->select('t1.*')
				    ->orderBy('t1.sort ASC')
				    ->findAll()
				    ->getData();
				    $defaultLocale = LocaleModel::factory()->where('is_default', 1)->limit(1)->findAll()->first();
				    $defaultLocaleId = $defaultLocale['id'];
					if (isset ( $_POST ['category_id'] ) && count ( $_POST ['category_id'] ) > 0) {
						$aCategoryArticleModel = ACategoryArticleModel::factory ();
						$aCategoryArticleModel->begin ();
						foreach ( $_POST ['category_id'] as $category_id ) {
							$aCategoryArticleModel->reset ()->set ( 'article_id', $id )->set ( 'category_id', $category_id )->insert ();
						}
						$aCategoryArticleModel->commit ();
					}
					$dataHistory = array ();
					$dataHistory ['user_id'] = $this->getUserId ();
					$dataHistory ['article_id'] = $id;
					$dataHistory ['action'] = AHistoryModel::ACTION_ADD;
					$dataHistory ['ip'] = UtilComponent::getClientIp ();
					$dataHistory ['modified'] = date ( 'Y-m-d H:i:s' );
					AHistoryModel::factory ($dataHistory)->insert ()->getInsertId ();
					if (isset ( $dataPost ['i18n'] )) {
						MultiLangModel::factory ()->saveMultiLang ( $dataPost ['i18n'], $id, 'Article', 'data');
					}
					$userArticleModel = UserArticleModel::factory ();
					if (isset ( $dataPost ['user_id'] )) {
						$userArticleModel->begin ();
						foreach ( $dataPost ['user_id'] as $user_id ) {
							$data = array ();
							$data ['user_id'] = $user_id;
							$data ['article_id'] = $id;
							$userArticleModel->reset ()->setAttributes ( $data )->insert ();
						}
						$userArticleModel->commit ();
					} else {
						$data = array ();
						$data ['user_id'] = $this->getUserId ();
						$data ['article_id'] = $id;
						$userArticleModel->reset ()->setAttributes ( $data )->insert ();
					}
					$articleTagModel = ArticleTagModel::factory ();
					if (isset ( $_POST ['tag_id'] ) && count ( $_POST ['tag_id'] ) > 0) {
						$articleTagModel->begin ();
						foreach ( $_POST ['tag_id'] as $tag_id ) {
							$articleTagModel->reset ()->set ( 'article_id', $id )->set ( 'tag_id', $tag_id )->insert ();
						}
						$articleTagModel->commit ();
					}
					$err = 'AS03';
					$uuidNumber = $this->generateRamdomNumber(6);
					foreach ($locale_arr as $locale) {
					    $category_arr = ACategoryModel::factory ()->getNode ( $locale['id'], 1);
					    $pc_arr = ACategoryArticleModel::factory()->where('article_id', $id)->findAll()->getDataPair(NULL, 'category_id');
					    $name = $_POST['i18n'][$locale['id']]['article_name'];
					    if (empty($name)) {
					        $name = $_POST['i18n'][$defaultLocaleId]['article_name'];
					    }
					    $friendlyUrl = $this->createFriendlyUrl($category_arr, $pc_arr, $name);
					    $friendlyUrl = $locale['language_iso'].'/'.$friendlyUrl.'-'.$uuidNumber;
					    $this->createOrUpdateDynamicRouter(
					        $friendlyUrl,
					        RouterModel::TYPE_ARTICLE,
					        self::FRONTEND_ARTICLE_CONTROLLER,
					        "Article",
					        "id",
					        $id,
					        $locale['id']);
					}
	
				} else {
					$err = 'AS04';
				}
				
			}
			UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=AdminArticle&action=Index&err=$err" );
	
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
				$arr = ArticleModel::factory ()->select ( "t1.*, (SELECT GROUP_CONCAT(t2.user_id SEPARATOR '~:~') FROM `" . UserArticleModel::factory ()->getTable () . "` AS t2 WHERE t2.article_id=t1.id ) AS user_ids" )->find ( $_GET ['id'] )->toArray ( 'user_ids', '~:~' )->getData ();
				$arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $arr ['id'], 'Article' );
				$routers = RouterModel::factory()
				->where('t1.type', RouterModel::TYPE_ARTICLE)
				->where('t1.controller', self::FRONTEND_ARTICLE_CONTROLLER)
				->where('t1.action', "Article")
				->where('t1.foreign_id', $_GET['id'])
				->findAll()
				->getData();
				if (!empty($routers)) {
				    foreach ($routers as $router) {
				        $arr['i18n'][$router['locale_id']]['url'] = $router['url'];
				    }
				}
				$this->set ( 'arr', $arr );
			}
			$this->set ( 'category_arr', ACategoryModel::factory ()->getNode ( $this->getLocaleId (), 1) );
			$this->set ( 'galleries', SliderModel::factory()
					->join('MultiLang', "t2.model='Slider' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->findAll()->getData());
			$this->set('tag_arr', TagModel::factory()->select ( 't1.*, t2.content as name' )->join('MultiLang', "t2.model='Tag' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->findAll()->getData());
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
			$this->appendJs ( "AdminArticle.js" );
		}
	}
	
	public function DeleteArticle() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$response = array ();
			$allowed = true;
			ArticleModel::factory()->find((int)$_GET ['id'])->getData();
			if ($allowed == true) {
			    if (ArticleModel::factory ()->setAttributes ( array (
						'id' => $_GET ['id']
				) )->erase ()->getAffectedRows () == 1) {
					$multiLangModel = MultiLangModel::factory ();
					$AHistoryModel = AHistoryModel::factory ();
					$multiLangModel->where ( 'model', 'Article' )->where ( 'foreign_id', $_GET ['id'] )->eraseAll ();
					UserArticleModel::factory ()->where ( 'article_id', $_GET ['id'] )->eraseAll ();
					$_arr = $AHistoryModel->where ( 'article_id', $_GET ['id'] )->findAll ()->getDataPair ( 'id', 'id' );
					if (! empty ( $_arr )) {
						$multiLangModel->reset ()->where ( 'model', 'AHistory' )->whereIn ( 'foreign_id', $_arr )->eraseAll ();
					}
					$AHistoryModel->reset ()->where ( 'article_id', $_GET ['id'] )->eraseAll ();
					$response ['code'] = 200;
				} else {
					$response ['code'] = 100;
				}
			} else {
				$response ['code'] = 100;
			}
			$this->deleteRouter(RouterModel::TYPE_ARTICLE, self::FRONTEND_ARTICLE_CONTROLLER, 'Article', $_GET['id']);
			AppController::jsonResponse ( $response );
		}
		exit ();
	}
	
	public function DeleteArticleBulk() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
				$multiLangModel = MultiLangModel::factory ();
				$AHistoryModel = AHistoryModel::factory ();
				$multiLangModel->where ( 'model', 'Article' )->whereIn ( 'foreign_id', $_POST ['record'] )->eraseAll ();
				UserArticleModel::factory ()->whereIn ( 'article_id', $_POST ['record'] )->eraseAll ();
				ArticleModel::factory ()->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
				$_arr = $AHistoryModel->whereIn ( 'article_id', $_POST ['record'] )->findAll ()->getDataPair ( 'id', 'id' );
				if (! empty ( $_arr )) {
					$multiLangModel->reset ()->where ( 'model', 'AHistory' )->whereIn ( 'foreign_id', $_arr )->eraseAll ();
				}
				$AHistoryModel->reset ()->whereIn ( 'article_id', $_POST ['record'] )->eraseAll ();
				foreach ($_POST['record'] as $id) {
				    $this->deleteRouter(RouterModel::TYPE_ARTICLE, self::FRONTEND_ARTICLE_CONTROLLER, 'Article', $id);
				}
			}
		}
		exit ();
	}
	
	public function GetArticle() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$articleModel = ArticleModel::factory ()
			->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Article' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'article_name'", 'left' );
				
				
			if (isset ( $_GET ['is_active'] ) && ! empty ( $_GET ['is_active'] )) {
				$isActive = Objects::escapeString ( $_GET ['is_active'] );
				$articleModel->where ( 't1.status', $isActive );
			}
			if (isset ( $_GET ['q'] ) && ! empty ( $_GET ['q'] )) {
				$q = Objects::escapeString ( $_GET ['q'] );
				$articleModel->where ( 't2.content LIKE', "%$q%" );
			}
	
			if (isset ( $_GET ['category_id'] ) && ! empty ( $_GET ['category_id'] )) {
				$categoryId = Objects::escapeString ( $_GET ['category_id'] );
				$articleModel->where(sprintf("t1.id IN (select `article_id` from `%1\$s` where `category_id` = $categoryId )", ACategoryArticleModel::factory()->getTable ()) );
			}
	
			if (isset ( $_GET ['status'] ) && ! empty ( $_GET ['status'] )) {
				$status = Objects::escapeString ( $_GET ['status'] );
				$articleModel->where ( 't1.status', $status);
			}
			if (isset ( $_GET ['fromDate'] ) && ! empty ( $_GET ['fromDate'] )) {
				$fromDate = Objects::escapeString ( $_GET ['fromDate'] );
				$fromDate = date('Y-m-d', strtotime($fromDate));
				$articleModel->where ( 't1.on_date >=', $fromDate );
			}
	
			if (isset ( $_GET ['toDate'] ) && ! empty ( $_GET ['toDate'] )) {
				$toDate = Objects::escapeString ( $_GET ['toDate'] );
				$toDate = date('Y-m-d', strtotime($toDate));
				$articleModel->where ( 't1.on_date <=', $toDate );
			}
	
			if (isset ( $_GET ['template'] ) && ! empty ( $_GET ['template'] )) {
				$template = Objects::escapeString ( $_GET ['template'] );
				$articleModel->where ( 't1.template', $template);
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
			$total = $articleModel->findCount ()->getData ();
			$rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 20;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}
			$data = $articleModel->select (sprintf("t1.*, t2.content AS article_name, (SELECT group_concat(ML.content  SEPARATOR '<br/>') FROM `%1\$s` as SC JOIN `%2\$s` as ML ON ML.foreign_id = SC.category_id AND ML.model = 'ACategory' AND ML.locale = '" . $this->getLocaleId () . "' AND ML.field = 'name' WHERE SC.article_id = t1.id) AS category", ACategoryArticleModel::factory()->getTable (), MultiLangModel::factory()->getTable()))->orderBy ( "`$column` $direction" )->limit ( $rowCount, $offset )->findAll ()->getData ();
			foreach ( $data as $k => $v ) {
				if (empty ( $v ['url'] )) {
					$v ['url'] = 'index.php?controller=AdminArticle&action=Preview&id=' . $v ['id'];
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
		$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );

		$this->appendJs ('AdminArticle.js' );
		$this->title = "Bài viết - Danh sách bài viết";
		$category_arr = ACategoryModel::factory ()->getNode ( $this->getLocaleId (), 1) ;
		$this->set ( 'category_arr', $category_arr );
		$this->appendJs ( 'chosen.jquery.js', THIRD_PARTY_PATH . 'harvest/chosen/' );
		$this->appendCss ( 'chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
	}
	public function SaveArticle() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$articleModel = ArticleModel::factory ();
			if (! in_array ( $_POST ['column'], $articleModel->getI18n () )) {
				$articleModel->where ( 'id', $_GET ['id'] )->limit ( 1 )->modifyAll ( array (
						$_POST ['column'] => $_POST ['value']
				) );
			} else {
				MultiLangModel::factory ()->updateMultiLang ( array (
						$this->getLocaleId () => array (
								$_POST ['column'] => $_POST ['value']
						)
				), $_GET ['id'], 'Article', 'data' );
			}
		}
		exit ();
	}
	
	public function Update() {
		$this->checkLogin ();
		if (isset ( $_POST ['article_update'] )) {
			$dataPost = $_POST;
			$valid = true;
			if (isset ( $_FILES ['file'] ) && $_FILES ['file'] ['error'] == 0) {
				$path = $_FILES ['file'] ['name'];
				$extension = strtolower(pathinfo ( $path, PATHINFO_EXTENSION ));
				$allowed_arr = explode ( "|", $this->option_arr ['o_extension_allow'] );
				if (in_array ( $extension, $allowed_arr )) {
				   
					$handle = new UploadComponent();
					if ($handle->load ( $_FILES ['file'] )) {
						$hash = md5 ( uniqid ( rand (), true ) );
						$date = date('Y/m/d').'/';
						$file_path = ARTICLE_AVATAR_UPLOAD_PATH . $date . $hash . '.' . $handle->getExtension ();
						if (!is_dir(ARTICLE_AVATAR_UPLOAD_PATH . $date)) {
							mkdir(ARTICLE_AVATAR_UPLOAD_PATH . $date, 0777, true);
						}
						if ($handle->save ( $file_path )) {
							//Resize image
							$Image = new ImageComponent();
							$Image->loadImage($file_path);
							$d = explode('x', $this->option_arr['o_artile_avatar_size']);
							if (isset($d[1]) && is_numeric($d[1])) {
								$Image->resizeSmart ( $d [0], $d [1] );
							} else {
								$Image->resizeToWidth ( $d [0] );
							}
							$Image->saveImage($file_path);
							
							$dataPost ['avatar_file'] = $file_path;
								
						}
					}
				} else {
					$valid = false;
				}
			}
			if ($valid) {

				$dataPost['on_date'] = date('Y-m-d', strtotime($dataPost['on_date']));
				ArticleModel::factory ()->where ( 'id', $dataPost ['id'] )->limit ( 1 )->modifyAll ( array_merge ( $dataPost, array (
						'modified' => date ( 'Y-m-d H:i:s' )
				) ) );
				ACategoryArticleModel::factory ()->where ( 'article_id', $_POST ['id'] )->eraseAll ();
				if (isset ( $_POST ['category_id'] ) && count ( $_POST ['category_id'] ) > 0) {
				    $aCategoryArticleModel = ACategoryArticleModel::factory ();
					$aCategoryArticleModel->begin ();
					foreach ( $_POST ['category_id'] as $category_id ) {
						$aCategoryArticleModel->reset ()->set ( 'article_id', $_POST ['id'] )->set ( 'category_id', $category_id )->insert ();
					}
					$aCategoryArticleModel->commit ();
				}
				$data = array ();
				$data ['user_id'] = $this->getUserId ();
				$data ['article_id'] = $dataPost ['id'];
				$data ['action'] = AHistoryModel::ACTION_UPDATE;
				$data ['ip'] = UtilComponent::getClientIp ();
				$data ['modified'] = date ( 'Y-m-d H:i:s' );
				$hid = AHistoryModel::factory ( $data )->insert ()->getInsertId ();
				if ($hid !== false && ( int ) $hid > 0) {
					$i18n_arr = MultiLangModel::factory ()->getMultiLang ( $dataPost ['id'], 'Article' );
					$locale_arr = LocaleModel::factory ()->select ( 't1.*' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
					foreach ( $locale_arr as $item ) {
						unset ( $i18n_arr [$item ['id']] ['article_name'] );
					}
					if (! empty ( $i18n_arr )) {
						MultiLangModel::factory ()->updateMultiLang ( $i18n_arr, $hid, 'AHistory', 'data' );
					}
				}
				if (isset ( $dataPost ['i18n'] )) {
					MultiLangModel::factory ()->saveMultiLang ( $dataPost ['i18n'], $dataPost ['id'], 'Article', 'data' );
				}
				$userArticleModel = UserArticleModel::factory ();
				$userArticleModel->where ( 'article_id', $dataPost ['id'] )->eraseAll ();
				if (isset ( $dataPost ['user_id'] )) {
					$userArticleModel->reset ()->begin ();
					foreach ( $dataPost ['user_id'] as $user_id ) {
						$data = array ();
						$data ['user_id'] = $user_id;
						$data ['article_id'] = $dataPost ['id'];
						$userArticleModel->reset ()->setAttributes ( $data )->insert ();
					}
					$userArticleModel->commit ();
				} else {
					$data = array ();
					$data ['user_id'] = $this->getUserId ();
					$data ['article_id'] = $dataPost ['id'];
					$userArticleModel->reset ()->setAttributes ( $data )->insert ();
				}
				$articleTagModel = ArticleTagModel::factory ();
				$articleTagModel->where ( 'article_id', $_POST ['id'] )->eraseAll ();
				if (isset ( $_POST ['tag_id'] ) && count ( $_POST ['tag_id'] ) > 0) {
					$articleTagModel->begin ();
					foreach ( $_POST ['tag_id'] as $tag_id ) {
						$articleTagModel->reset ()->set ( 'article_id', $_POST ['id'] )->set ( 'tag_id', $tag_id )->insert ();
					}
					$articleTagModel->commit ();
				}
				$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
				$lp_arr = array ();
				foreach ( $locale_arr as $item ) {
				    $lp_arr [$item ['id'] . "_"] = $item ['file'];
				}
				$this->set ( 'lp_arr', $locale_arr );
				$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
				$uuidNumber = $this->generateRamdomNumber(6);
				foreach ($locale_arr as $locale) {
				    $category_arr = ACategoryModel::factory ()->getNode ( $locale['id'], 1);
				    $pc_arr = ACategoryArticleModel::factory()->where('article_id', $_POST['id'])->findAll()->getDataPair(null, 'category_id');
				    
				    if (empty($_POST['i18n'][$locale['id']]['url'])) {
				        $friendlyUrl = $this->createFriendlyUrl($category_arr, $pc_arr, $_POST['i18n'][$locale['id']['article_name']]);
				        $friendlyUrl = $locale['language_iso'].'/'.$friendlyUrl.'-'.$uuidNumber;
				    } else {
				        $friendlyUrl = $_POST['i18n'][$locale['id']]['url'];
				    }
				    
				    $this->createOrUpdateDynamicRouter(
				        $friendlyUrl,
				        RouterModel::TYPE_ARTICLE,
				        self::FRONTEND_ARTICLE_CONTROLLER,
				        "Article",
				        "id",
				        $_POST['id'],
				        $locale['id']);
				}
				UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminArticle&action=Update&id=" . $dataPost ['id'] . "&err=AS01" );
			}
			
	
		} else {
			$arr = ArticleModel::factory ()->select ( "t1.*, (SELECT GROUP_CONCAT(t2.user_id SEPARATOR '~:~') FROM `" . UserArticleModel::factory ()->getTable () . "` AS t2 WHERE t2.article_id=t1.id ) AS user_ids,  (SELECT COUNT(t3.article_id) FROM `" . AHistoryModel::factory ()->getTable () . "` AS t3 WHERE t3.article_id=t1.id) as changes" )->find ( $_GET ['id'] )->toArray ( 'user_ids', '~:~' )->getData ();
			if (count ( $arr ) === 0) {
				UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminArticle&action=Index&err=AS08" );
			}
			$arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $arr ['id'], 'Article' );
			$routerType = RouterModel::TYPE_ARTICLE;
			
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
			$category_arr = ACategoryModel::factory ()->getNode ( $this->getLocaleId (), 1) ;
			$this->set ( 'category_arr', $category_arr );
			$this->set ( 'sc_arr', ACategoryArticleModel::factory ()->where ( 't1.article_id', $arr ['id'] )->orderBy ( 't1.category_id ASC' )->findAll ()->getDataPair ( 'category_id', 'category_id' ) );
			$this->set('tag_arr', TagModel::factory()->select("t1.*, t2.content as name")->join('MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Tag' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'name'", 'left' )->findAll()->getData());
			$this->set ( 'mc_arr', ArticleTagModel::factory ()->where ( 't1.article_id', $arr ['id'] )->orderBy ( 't1.tag_id ASC' )->findAll ()->getDataPair ( 'tag_id', 'tag_id' ) );
			if (! empty ( $arr ['modified'] )) {
				$_arr = AHistoryModel::factory ()->join ( 'User', "t2.id = t1.user_id", 'left' )->select ( 't1.*, t2.name' )->where ( 'article_id', $_GET ['id'] )->orderBy ( 't1.modified DESC' )->limit ( 1 )->findAll ()->getData ();
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
			$this->appendJs ( "AdminArticle.js" );
	
		}
	}
	public function Install() {
		$this->checkLogin ();
		if ($this->isAdmin () || $this->isEditor ()) {
			$allowed = true;
			if ($this->isEditor () && ! in_array ( $_GET ['id'], $this->getAllowedArticleIds ( $this->getUserId () ) )) {
				$allowed = false;
			}
			if ($allowed == true) {
				$arr = ArticleModel::factory ()->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Article' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'article_name'", 'left' )->select ( 't1.*, t2.content as article_name' )->find ( $_GET ['id'] )->getData ();
				if (count ( $arr ) === 0) {
						
					UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminArticle&action=Index&err=AS08" );
				}
				$this->set ( 'arr', $arr );
				$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.title' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left outer' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
				$this->set ( 'locale_arr', $locale_arr );
				$this->appendJs ('AdminArticleController.js' );
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
		if ($this->isEditor () && ! in_array ( $_GET ['id'], $this->getAllowedArticleIds ( $this->getUserId () ) )) {
			$allowed = false;
		}
		if ($allowed == true) {
			$arr = ArticleModel::factory ()->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Article' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'article_content'", 'left' )->select ( 't1.*, t2.content as article_content' )->find ( $_GET ['id'] )->getData ();
			if (count ( $arr ) === 0) {
				UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=AdminArticle&action=Index&err=AS08" );
			}
			$this->set ( 'arr', $arr );
		}
	}
	
	public function PreviewHistory() {
	
		$this->setLayout ( 'Empty' );
		$allowed = true;
		if ($this->isEditor () && ! in_array ( $_GET ['id'], $this->getAllowedArticleIds ( $this->getUserId () ) )) {
			$allowed = false;
		}
		if ($allowed == true) {
			$arr = AHistoryModel::factory ()->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'AHistory' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'article_content'", 'left' )->select ( 't1.*, t2.content as article_content' )->find ( $_GET ['id'] )->getData ();
			$this->set ( 'arr', $arr );
		}
	}
	public function History() {
		$this->checkLogin ();
		$this->title = "Bài viết - Lịch sử bài viết";
		if ($this->isAdmin ()) {
			$allowed = true;
			if ($this->isEditor () && ! in_array ( $_GET ['article_id'], $this->getAllowedArticleIds ( $this->getUserId () ) )) {
				$allowed = false;
			}
			if ($allowed == true) {
					
				$arr = ArticleModel::factory ()->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Article' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'article_name'", 'left' )->select ( 't1.*, t2.content as article_name' )->findAll ()->getData ();
				$this->set ( 'arr', $arr );
				$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
				$this->appendJs ('AdminArticle.js' );
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
			$AHistoryModel = AHistoryModel::factory ()->join ( 'MultiLang', "t2.foreign_id = t1.article_id AND t2.model = 'Article' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'article_name'", 'left' )->join ( 'User', "t3.id = t1.user_id", 'left' );
			$articleRecords = ArticleModel::factory()
			->select('t1.id')
			->findAll()
			->getData();
			$articleIds = array();
			if(!empty($articleRecords)) {
				foreach ($articleRecords as $r) {
					$articleIds[] = $r['id'];
				}
			}
			if(!empty($articleIds)) {
				$AHistoryModel->whereIn('t1.article_id', $articleIds);
			}
				
			if (isset ( $_GET ['q'] ) && ! empty ( $_GET ['q'] )) {
				$q = Objects::escapeString ( $_GET ['q'] );
				$AHistoryModel->where ( 't2.content LIKE', "%$q%" );
			}
			if (isset ( $_GET ['article_id'] ) && ! empty ( $_GET ['article_id'] )) {
				$article_id = Objects::escapeString ( $_GET ['article_id'] );
				$AHistoryModel->where ( "(t1.article_id='" . $article_id . "')" );
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
			$total = $AHistoryModel->findCount ()->getData ();
			$rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 20;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}
			$data = $AHistoryModel->select ( 't1.*, t2.content AS article_name, t3.name' )->orderBy ( "`$column` $direction" )->limit ( $rowCount, $offset )->findAll ()->getData ();
			foreach ( $data as $k => $v ) {
				$v ['modified'] = UtilComponent::formatDate ( date ( 'Y-m-d', strtotime ( $v ['modified'] ) ), 'Y-m-d', $this->option_arr ['o_date_format'] ) . ', ' . UtilComponent::formatTime ( date ( 'H:i:s', strtotime ( $v ['modified'] ) ), 'H:i:s', $this->option_arr ['o_time_format'] );
				$v ['title'] = __ ( 'lblArticleContent', true, false ) . ' ' . __ ( 'lblBeforeChanged', true ) . ' ' . $v ['modified'] . ' ' . __ ( 'lblBy', true ) . ' ' . $v ['name'];
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
			$AHistoryModel = AHistoryModel::factory ();
			$arr = $AHistoryModel->find ( $_GET ['id'] )->getData ();
			$allowed = true;
			if ($this->isEditor () && ! in_array ( $arr ['id'], $this->getAllowedArticleIds ( $this->getUserId () ) )) {
				$allowed = false;
			}
			if ($allowed == true) {
				if ($AHistoryModel->reset ()->setAttributes ( array (
						'id' => $_GET ['id']
				) )->erase ()->getAffectedRows () == 1) {
					MultiLangModel::factory ()->where ( 'model', 'AHistory' )->where ( 'foreign_id', $_GET ['id'] )->eraseAll ();
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
				MultiLangModel::factory ()->where ( 'model', 'AHistory' )->whereIn ( 'foreign_id', $_POST ['record'] )->eraseAll ();
				AHistoryModel::factory ()->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
			}
		}
		exit ();
	}
	public function View() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$allowed = true;
			if ($this->isEditor () && ! in_array ( $_GET['id'], $this->getAllowedArticleIds ( $this->getUserId () ) )) {
				$allowed = false;
			}
			if ($allowed == true) {
				$arr = AHistoryModel::factory ()->find ( $_GET ['id'] )->getData ();
				$arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $arr ['id'], 'AHistory' );
				$this->set ( 'arr', $arr );
				$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file, t2.title' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
				$this->set ( 'lp_arr', $locale_arr );
			}
		}
	}
	public function Restore() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (isset ( $_GET ['id'] ) && $_GET ['article_id']) {
				$allowed = true;
				if ($this->isEditor () && ! in_array ( $_GET ['article_id'], $this->getAllowedArticleIds ( $this->getUserId () ) )) {
					$allowed = false;
				}
				if ($allowed == true) {
					$multiLangModel = MultiLangModel::factory ();
					$i18n_arr = $multiLangModel->getMultiLang ( $_GET ['id'], 'AHistory' );
					if (! empty ( $i18n_arr )) {
						$multiLangModel->reset ()->updateMultiLang ( $i18n_arr, $_GET ['article_id'], 'Article', 'data' );
					}
				}
			}
		}
		exit ();
	}
	
	public function isArticleAllowed(){
		$this->setAjax (true );
		return true;
	}
	
	public function DeleteImage() {
	
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$response = array ();
			$articleModel = ArticleModel::factory ();
			$arr = $articleModel->find ( $_GET ['id'] )->getData ();
			if (! empty ( $arr )) {
				if (! empty ( $arr ['avatar_file'] )) {
					@unlink ( INSTALL_PATH . $arr ['avatar_file'] );
				}
				$data = array ();
				$data ['avatar_file'] = ':NULL';
				$articleModel->reset ()->where ( array (
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