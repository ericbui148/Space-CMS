<?php
namespace App\Controllers;

use App\Models\MultiLangModel;
use App\Controllers\Components\UtilComponent;
use App\Plugins\Locale\Models\LocaleModel;
use App\Models\RouterModel;
use Core\Framework\Components\ImageComponent;
use App\Models\PCategoryModel;
use App\Models\PCategoryPageModel;
use App\Models\PageModel;
use App\Models\ItemSortModel;

class AdminPageCategoriesController extends AdminController
{
    const FRONTEND_PAGE_CATEGORY_CONTROLLER = 'Site';
    
	public function Create() {
	
		$this->checkLogin ();
		if ($this->isAdmin() || $this->isEditor()) {
			if (isset ( $_POST ['category_create'] )) {
				if (isset($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name']))
				{
					$image = new ImageComponent();
					$image
					->setAllowedExt(array('png', 'gif', 'jpg', 'jpeg', 'jpe', 'jfif', 'jif', 'jfi'))
					->setAllowedTypes(array('image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/peg'));
					if ($image->load($_FILES['avatar']))
					{
						$hash = md5(uniqid(rand(), true));
						$date = date('Y/m/d').'/';
						$file_path = PAGE_CATEGORY_AVATAR_UPLOAD_PATH . $date. $hash . '.' . $image->getExtension ();
						if (!is_dir(PAGE_CATEGORY_AVATAR_UPLOAD_PATH . $date)) {
							mkdir(PAGE_CATEGORY_AVATAR_UPLOAD_PATH . $date, 0777, true);
						}
						if ($image->save($file_path))
						{
							$image->loadImage($file_path);
	
							$_POST['avatar'] = $file_path;
						}
						
					}
				}
	
				$dataPost = $_POST;
				$dataPost['uuid_code'] = uniqid();
				$id = PCategoryModel::factory ()->saveNode ( $dataPost, $dataPost['parent_id']);
				if ($id !== false && ( int ) $id > 0) {
				    $locale_arr = LocaleModel::factory()->select('t1.*')
				    ->orderBy('t1.sort ASC')
				    ->findAll()
				    ->getData();
					$err = 'CMSCATO05';
					if (isset ( $dataPost ['i18n'] )) {
					    MultiLangModel::factory ()->saveMultiLang ( $dataPost ['i18n'], $id, 'PCategory');
					}

					$uuidNumber = $this->generateRamdomNumber(6);
					foreach ($locale_arr as $locale) {
					    $category_arr = PCategoryModel::factory ()->getNode ( $locale['id'], 1);
					    $pc_arr = [$id];
					    $friendlyUrl = $this->createFriendlyUrl($category_arr, $pc_arr, null);
					    $friendlyUrl = $locale['language_iso'].'/'.$friendlyUrl.'-'.$uuidNumber;
					    $this->createOrUpdateDynamicRouter(
					        $friendlyUrl,
					        RouterModel::TYPE_PAGE_CATEGORY,
					        self::FRONTEND_PAGE_CATEGORY_CONTROLLER,
					        "Pages",
					        "category_id",
					        $id,
					        $locale['id']);
					}
					$this->createItemSorts($id);
	
				} else {
					$err = 'CMSCATO06';
				}
				UtilComponent::redirect ( sprintf ( "%s?controller=AdminPageCategories&action=Index&err=%s", $_SERVER ['PHP_SELF'], $err ) );
			} else {
				$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left outer' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
				$lp_arr = array ();
				foreach ( $locale_arr as $v ) {
					$lp_arr [$v ['id'] . "_"] = $v ['file'];
				}
				$this->set ( 'lp_arr', $locale_arr );
				$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
				$this->set ( 'node_arr', PCategoryModel::factory ()->getNode ( $this->getLocaleId (), 1) );
				$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
				$this->appendJs ( 'jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/' );
				$this->appendJs ( 'jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/' );
				$this->appendCss ( 'jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/' );
				$this->appendJs ( 'chosen.jquery.js', THIRD_PARTY_PATH . 'harvest/chosen/' );
				$this->appendCss ( 'chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
				$this->appendJs ( "AdminPageCategories.js" );
				$this->appendJs ( 'index.php?controller=Admin&action=Messages', $this->baseUrl(), true );
			}
		} else {
			$this->set ( 'status', 2 );
		}
	}
	
	public function DeleteCategory() {
	
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$response = array ();
			$pageCategoryModel = PCategoryModel::factory ();
			$pageCategoryModel->deleteNode ( $_GET ['id'] );
			$pageCategoryModel->rebuildTree ( 1, 1 );
			$pageCategoryModel::factory ()->where ( 'id', $_GET ['id'] )->eraseAll ();
			$this->deleteRouter(RouterModel::TYPE_PAGE_CATEGORY, self::FRONTEND_PAGE_CATEGORY_CONTROLLER, 'Page', $_GET['id']);
			$response ['code'] = 200;
			AppController::jsonResponse ( $response );
		}
		exit ();
	}
	
	public function DeleteCategoryBulk() {
	
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
				$pageCategoryModel = PCategoryModel::factory ();
				$pageCategoryModel->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
				foreach ( $_POST ['record'] as $id ) {
					$pageCategoryModel->deleteNode ( $id );
					$pageCategoryModel->rebuildTree ( 1, 1 );
					$this->deleteRouter(RouterModel::TYPE_PAGE_CATEGORY, self::FRONTEND_PAGE_CATEGORY_CONTROLLER, 'Page', $id);
				}
				PCategoryPageModel::factory ()->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
			}
		}
		exit ();
	}
	
	public function GetCategory() {
	
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$pageCategoryModel = PCategoryModel::factory ();
			$column = 'name';
			$direction = 'ASC';
			if (isset ( $_GET ['direction'] ) && isset ( $_GET ['column'] ) && in_array ( strtoupper ( $_GET ['direction'] ), array (
					'ASC',
					'DESC'
			) )) {
				$column = $_GET ['column'];
				$direction = strtoupper ( $_GET ['direction'] );
			}
			$data = $pageCategoryModel->getNode ( $this->getLocaleId (), 1);
			$total = count ( $data );
			$rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 10;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}
			$c_arr = $pageCategoryModel->reset ()->select ( sprintf ( "t1.id, (SELECT COUNT(*) FROM `%s` WHERE `category_id` = `t1`.`id` LIMIT 1) AS `pages`", PCategoryPageModel::factory ()->getTable () ) )->findAll ()->getDataPair ( 'id', 'pages' );
			$data = array_slice ( $data, $offset, $rowCount );
			$stack = array ();
			foreach ( $data as $k => $category ) {
				$data [$k] ['pages'] = ( int ) @$c_arr [$category ['data'] ['id']];
				$data [$k] ['up'] = 0;
				$data [$k] ['down'] = 0;
				$data [$k] ['id'] = ( int ) $category ['data'] ['id'];
				if (! isset ( $stack [$category ['deep'] . "|" . $category ['data'] ['parent_id']] )) {
					$stack [$category ['deep'] . "|" . $category ['data'] ['parent_id']] = 0;
				}
				$stack [$category ['deep'] . "|" . $category ['data'] ['parent_id']] += 1;
				if ($stack [$category ['deep'] . "|" . $category ['data'] ['parent_id']] > 1) {
					$data [$k] ['up'] = 1;
				}
				if (isset ( $data [$k + 1] ) && $data [$k + 1] ['deep'] == $category ['deep'] || $stack [$category ['deep'] . "|" . $category ['data'] ['parent_id']] < $category ['siblings']) {
					$data [$k] ['down'] = 1;
				}
			}
			AppController::jsonResponse ( compact ( 'data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction' ) );
		}
		exit ();
	}
	
	public function Index() {
		$this->checkLogin ();
		$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
		$this->appendJs ( "AdminPageCategories.js" );
		$this->appendJs ( 'index.php?controller=Admin&action=Messages', $this->baseUrl(), true );
	}
	
	public function SetOrder() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$pageCategoryModel = PCategoryModel::factory ();
			$node = $pageCategoryModel->find ( $_POST ['id'] )->getData ();
			if (count ( $node ) > 0) {
				$pageCategoryModel->reset ();
				switch ($_POST ['direction']) {
					case 'up' :
						$pageCategoryModel->where ( 't1.lft <', $node ['lft'] )->orderBy ( 't1.lft DESC' );
						break;
					case 'down' :
						$pageCategoryModel->where ( 't1.lft >', $node ['lft'] )->orderBy ( 't1.lft ASC' );
						break;
				}
				$neighbour = $pageCategoryModel->where ( 't1.id !=', $node ['id'] )->where ( 't1.parent_id', $node ['parent_id'] )->limit ( 1 )->findAll ()->getData ();
				if (count ( $neighbour ) === 1) {
					$neighbour = $neighbour [0];
					$pageCategoryModel->reset ()->set ( 'id', $neighbour ['id'] )->modify ( array (
							'lft' => $node ['lft'],
							'rgt' => $node ['rgt']
					) );
					$pageCategoryModel->reset ()->set ( 'id', $node ['id'] )->modify ( array (
							'lft' => $neighbour ['lft'],
							'rgt' => $neighbour ['rgt']
					) );
					$pageCategoryModel->reset ()->rebuildTree ( 1, 1 );
				} else {
				}
			}
		}
		exit ();
	}
	
	public function Update() {
	
		$this->checkLogin ();

		if ($this->isAdmin() || $this->isEditor()) {
			$pageCategoryModel = PCategoryModel::factory ();
			if (isset ( $_POST ['category_update'] )) {
				if (isset($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name']))
				{
					$image = new ImageComponent();
					$image
					->setAllowedExt(array('png', 'gif', 'jpg', 'jpeg', 'jpe', 'jfif', 'jif', 'jfi'))
					->setAllowedTypes(array('image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/peg'));
					if ($image->load($_FILES['avatar']))
					{
						$hash = md5(uniqid(rand(), true));
						$date = date('Y/m/d').'/';
						$file_path = PAGE_CATEGORY_AVATAR_UPLOAD_PATH . $date. $hash . '.' . $image->getExtension ();
						if (!is_dir(PAGE_CATEGORY_AVATAR_UPLOAD_PATH . $date)) {
							mkdir(PAGE_CATEGORY_AVATAR_UPLOAD_PATH . $date, 0777, true);
						}
						if ($image->save($file_path))
						{
							$image->loadImage($file_path);
	
							$_POST['avatar'] = $file_path;
						}
					}
				}
				$data = array ();
				$pageCategoryModel->updateNode ( array_merge ( $_POST, $data ) );
				if (isset ( $_POST ['i18n'] )) {
				    MultiLangModel::factory ()->updateMultiLang ( $_POST ['i18n'], $_POST ['id'], 'PCategory' );
				    $locale_arr = LocaleModel::factory()->select('t1.*')
				    ->orderBy('t1.sort ASC')
				    ->findAll()
				    ->getData();
				    $uuidNumber = $this->generateRamdomNumber(6);
				    foreach ($locale_arr as $locale) {
				        $category_arr = PCategoryModel::factory ()->getNode ( $locale['id'], 1);
				        $pc_arr = [$_POST['id']];
				        if (empty($_POST['i18n'][$locale['id']]['url'])) {
				            $friendlyUrl = $this->createFriendlyUrl($category_arr, $pc_arr, null);
				            $friendlyUrl = $locale['language_iso'].'/'.$friendlyUrl.'-'.$uuidNumber;
				        } else {
				            $friendlyUrl = $_POST['i18n'][$locale['id']]['url'];
				        }
				        
				        $this->createOrUpdateDynamicRouter(
				            $friendlyUrl,
				            RouterModel::TYPE_PAGE_CATEGORY,
				            self::FRONTEND_PAGE_CATEGORY_CONTROLLER,
				            "Pages",
				            "category_id",
				            $_POST['id'],
				            $locale['id']);
				    }
				}
				if ($this->needCreateSort($_POST['id'])) {
				    $this->createItemSorts($_POST['id']);
				}
	
				UtilComponent::redirect ( sprintf ( "%s?controller=AdminPageCategories&action=Update&id=%s", $_SERVER ['PHP_SELF'], $_POST['id'] ) );
			} else {
				$arr = $pageCategoryModel->find ( $_GET ['id'] )->getData ();
				if (count ( $arr ) === 0) {
					UtilComponent::redirect ( sprintf ( "%s?controller=AdminPageCategories&action=Index&err=%s", $_SERVER ['PHP_SELF'], 'AG08' ) );
				}
				$arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $arr ['id'], 'PCategory' );
				$routers = RouterModel::factory()
				->where('t1.type', RouterModel::TYPE_PAGE_CATEGORY)
				->where('t1.controller', self::FRONTEND_PAGE_CATEGORY_CONTROLLER)
				->where('t1.action', "Pages")
				->where('t1.foreign_id', $_GET['id'])
				->findAll()
				->getData();
				if (!empty($routers)) {
				    foreach ($routers as $router) {
				        $arr['i18n'][$router['locale_id']]['url'] = $router['url'];
				    }
				}
				$this->set ( 'arr', $arr );
				$this->set ( 'node_arr', PCategoryModel::factory ()->getNode ( $this->getLocaleId (), 1));
				$this->set ( 'child_arr', $pageCategoryModel->reset ()->getNode ( $this->getLocaleId (), $arr ['id']) );
				$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left outer' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
				$lp_arr = array ();
				foreach ( $locale_arr as $v ) {
					$lp_arr [$v ['id'] . "_"] = $v ['file'];
				}
				$this->set ( 'lp_arr', $locale_arr );
				$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
				$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
				$this->appendJs ( 'jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/' );
				$this->appendJs ( 'jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/' );
				$this->appendCss ( 'jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/' );
				$this->appendJs ( 'chosen.jquery.js', THIRD_PARTY_PATH . 'harvest/chosen/' );
				$this->appendCss ( 'chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
				$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
				$this->appendJs ( "AdminPageCategories.js" );
				$this->appendJs ( 'index.php?controller=Admin&action=Messages', $this->baseUrl(), true );
			}
		} else {
			$this->set ( 'status', 2 );
		}
		
	}
	
	protected function createItemSorts($categoryId)
	{
	    $pageModel = PageModel::factory()->select('id')
	    ->where ( 'status', PageModel::STATUS_ACTIVE )
	    ->orderBy ( '`id` DESC' );
	    $pageModel->where(sprintf("t1.id IN (SELECT `page_id` FROM `%s` WHERE `category_id` = '%u')", PCategoryPageModel::factory()->getTable(), (int) $categoryId));
	    $articleArr = $pageModel->findAll()->getData();
	    if (!empty($articleArr)) {
	        $batchValues = [];
	        $sort = 1;
	        foreach ($articleArr as $article) {
	            $batchValues[] = [
	                $article['id'],
	                ItemSortModel::TYPE_PAGE_CATEGORY,
	                $categoryId,
	                $sort
	            ];
	            $sort++;
	        }
	        ItemSortModel::factory()->setBatchFields([
	            'foreign_id', 'type', 'foreign_type_id', 'sort'
	        ])->setBatchRows($batchValues)->insertBatch()->getAffectedRows();
	    }
	}
	
	protected function needCreateSort($categoryId)
	{
	    $record = ItemSortModel::factory()->where('foreign_type_id', $categoryId)->where('type', ItemSortModel::TYPE_PAGE_CATEGORY)->limit(1)->findAll()->first();
	    return empty($record);
	}
}
?>