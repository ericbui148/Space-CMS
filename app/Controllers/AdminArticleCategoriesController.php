<?php
namespace App\Controllers;

use Core\Framework\Components\ImageComponent;
use App\Models\MultiLangModel;
use App\Controllers\Components\UtilComponent;
use App\Plugins\Locale\Models\LocaleModel;
use App\Models\RouterModel;
use App\Models\ACategoryModel;
use App\Models\ACategoryArticleModel;
use App\Models\ArticleModel;
use App\Models\ItemSortModel;

class AdminArticleCategoriesController extends AdminController
{	
    const FRONTEND_ARTICLE_CATEGORY_CONTROLLER = 'Site';
	public function Create() {
	
		$this->checkLogin ();
		$this->title = "Danh mục bài viết - Thêm danh mục";
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
						$file_path = ARTICLE_CATEGORY_AVATAR_UPLOAD_PATH . $date. $hash . '.' . $image->getExtension ();
						if (!is_dir(ARTICLE_CATEGORY_AVATAR_UPLOAD_PATH . $date)) {
							mkdir(ARTICLE_CATEGORY_AVATAR_UPLOAD_PATH . $date, 0777, true);
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

				$id = ACategoryModel::factory ()->saveNode ( $dataPost, $dataPost['parent_id']);

				if ($id !== false && ( int ) $id > 0) {
				    $locale_arr = LocaleModel::factory()->select('t1.*')
				    ->orderBy('t1.sort ASC')
				    ->findAll()
				    ->getData();
					$err = 'CMSCATO05';
					if (isset ( $dataPost ['i18n'] )) {
					    MultiLangModel::factory ()->saveMultiLang ( $dataPost ['i18n'], $id, 'ACategory');
					}
					$uuidNumber = $this->generateRamdomNumber(6);
					foreach ($locale_arr as $locale) {
					    $category_arr = ACategoryModel::factory ()->getNode ( $locale['id'], 1);
					    $pc_arr = [$id];
					    $friendlyUrl = $this->createFriendlyUrl($category_arr, $pc_arr, null);
					    $friendlyUrl = $locale['language_iso'].'/'.$friendlyUrl.'-'.$uuidNumber;
					    $this->createOrUpdateDynamicRouter(
					        $friendlyUrl,
					        RouterModel::TYPE_ARTICLE_CATEGORY,
					        self::FRONTEND_ARTICLE_CATEGORY_CONTROLLER,
					        "Articles",
					        "category_id",
					        $id,
					        $locale['id']);
					}
					$this->createItemSorts($id); 
				} else {
					$err = 'CMSCATO06';
				}
				UtilComponent::redirect ( sprintf ( "%s?controller=AdminArticleCategories&action=Index&err=%s", $_SERVER ['PHP_SELF'], $err ) );
			} else {
			    $locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left outer' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
				$lp_arr = array ();
				foreach ( $locale_arr as $v ) {
					$lp_arr [$v ['id'] . "_"] = $v ['file'];
				}
				$this->set ( 'lp_arr', $locale_arr );
				$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
				$this->set ( 'node_arr', ACategoryModel::factory ()->getNode ( $this->getLocaleId (), 1) );
				$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
				$this->appendJs ( 'jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/' );
				$this->appendJs ( 'jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/' );
				$this->appendCss ( 'jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/' );
				$this->appendJs ( 'chosen.jquery.js', THIRD_PARTY_PATH . 'harvest/chosen/' );
				$this->appendCss ( 'chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
				$this->appendJs ( "AdminArticleCategories.js" );
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
			$aCategoryModel = ACategoryModel::factory ();
			$aCategoryModel->deleteNode ( $_GET ['id'] );
			$aCategoryModel->rebuildTree ( 1, 1 );
			$aCategoryModel::factory ()->where ( 'id', $_GET ['id'] )->eraseAll ();
			$this->deleteRouter(RouterModel::TYPE_ARTICLE_CATEGORY, self::FRONTEND_ARTICLE_CATEGORY_CONTROLLER, 'Article', $_GET['id']);
			$response ['code'] = 200;
			AppController::jsonResponse ( $response );
		}
		exit ();
	}
	
	public function DeleteCategoryBulk() {
	
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
				$aCategoryModel = ACategoryModel::factory ();
				$aCategoryModel->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
				foreach ( $_POST ['record'] as $id ) {
					$aCategoryModel->deleteNode ( $id );
					$aCategoryModel->rebuildTree ( 1, 1 );
					$this->deleteRouter(RouterModel::TYPE_ARTICLE_CATEGORY, self::FRONTEND_ARTICLE_CATEGORY_CONTROLLER, 'Article', $id);
				}
				ACategoryArticleModel::factory ()->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
			}
		}
		exit ();
	}
	
	public function GetCategory() {
	
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$aCategoryModel = ACategoryModel::factory ();
			$column = 'name';
			$direction = 'ASC';
			if (isset ( $_GET ['direction'] ) && isset ( $_GET ['column'] ) && in_array ( strtoupper ( $_GET ['direction'] ), array (
					'ASC',
					'DESC'
			) )) {
				$column = $_GET ['column'];
				$direction = strtoupper ( $_GET ['direction'] );
			}
			$data = $aCategoryModel->getNode ( $this->getLocaleId (), 1);
			$total = count ( $data );
			$rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 10;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}
			$c_arr = $aCategoryModel->reset ()->select ( sprintf ( "t1.id, (SELECT COUNT(*) FROM `%s` WHERE `category_id` = `t1`.`id` LIMIT 1) AS `articles`", ACategoryArticleModel::factory ()->getTable () ) )->findAll ()->getDataPair ( 'id', 'articles' );
			$data = array_slice ( $data, $offset, $rowCount );
			$stack = array ();
			foreach ( $data as $k => $category ) {
				$data [$k] ['articles'] = ( int ) @$c_arr [$category ['data'] ['id']];
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
		$this->title = "Danh mục bài viết";
		$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
		$this->appendJs ( "AdminArticleCategories.js" );
		$this->appendJs ( 'index.php?controller=Admin&action=Messages', $this->baseUrl(), true );
	}
	
	public function SetOrder() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$aCategoryModel = ACategoryModel::factory ();
			$node = $aCategoryModel->find ( $_POST ['id'] )->getData ();
			if (count ( $node ) > 0) {
				$aCategoryModel->reset ();
				switch ($_POST ['direction']) {
					case 'up' :
						$aCategoryModel->where ( 't1.lft <', $node ['lft'] )->orderBy ( 't1.lft DESC' );
						break;
					case 'down' :
						$aCategoryModel->where ( 't1.lft >', $node ['lft'] )->orderBy ( 't1.lft ASC' );
						break;
				}
				$neighbour = $aCategoryModel->where ( 't1.id !=', $node ['id'] )->where ( 't1.parent_id', $node ['parent_id'] )->limit ( 1 )->findAll ()->getData ();
				if (count ( $neighbour ) === 1) {
					$neighbour = $neighbour [0];
					$aCategoryModel->reset ()->set ( 'id', $neighbour ['id'] )->modify ( array (
							'lft' => $node ['lft'],
							'rgt' => $node ['rgt']
					) );
					$aCategoryModel->reset ()->set ( 'id', $node ['id'] )->modify ( array (
							'lft' => $neighbour ['lft'],
							'rgt' => $neighbour ['rgt']
					) );
					$aCategoryModel->reset ()->rebuildTree ( 1, 1 );
				} else {
				}
			}
		}
		exit ();
	}
	
	public function Update() {
	
		$this->checkLogin ();
		$this->title = "Cập nhật danh mục";
		if ($this->isAdmin() || $this->isEditor()) {
			$aCategoryModel = ACategoryModel::factory ();
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
						$file_path = ARTICLE_CATEGORY_AVATAR_UPLOAD_PATH . $date. $hash . '.' . $image->getExtension ();
						if (!is_dir(ARTICLE_CATEGORY_AVATAR_UPLOAD_PATH . $date)) {
							mkdir(ARTICLE_CATEGORY_AVATAR_UPLOAD_PATH . $date, 0777, true);
						}
						if ($image->save($file_path))
						{
							$image->loadImage($file_path);
	
							$_POST['avatar'] = $file_path;
						}
					}
				}
				$data = array ();
				$aCategoryModel->updateNode ( array_merge ( $_POST, $data ) );
				if (isset ( $_POST ['i18n'] )) {
					MultiLangModel::factory ()->updateMultiLang ( $_POST ['i18n'], $_POST ['id'], 'ACategory' );
					$locale_arr = LocaleModel::factory()->select('t1.*')
					->orderBy('t1.sort ASC')
					->findAll()
					->getData();
					$uuidNumber = $this->generateRamdomNumber(6);
					foreach ($locale_arr as $locale) {
					    $category_arr = ACategoryModel::factory ()->getNode ( $locale['id'], 1);
					    $pc_arr = [$_POST['id']];
					    if (empty($_POST['i18n'][$locale['id']]['url'])) {
					        $friendlyUrl = $this->createFriendlyUrl($category_arr, $pc_arr, null);
					        $friendlyUrl = $locale['language_iso'].'/'.$friendlyUrl.'-'.$uuidNumber;
					    } else {
					        $friendlyUrl = $_POST['i18n'][$locale['id']]['url'];
					    }
					    
					    $this->createOrUpdateDynamicRouter(
					        $friendlyUrl,
					        RouterModel::TYPE_ARTICLE_CATEGORY,
					        self::FRONTEND_ARTICLE_CATEGORY_CONTROLLER,
					        "Articles",
					        "category_id",
					        $_POST['id'],
					        $locale['id']);
					}
				}
				if ($this->needCreateSort($_POST['id'])) {
				    $this->createItemSorts($_POST['id']);
				}
				UtilComponent::redirect (sprintf ("%s?controller=AdminArticleCategories&action=Index", $_SERVER ['PHP_SELF']));
			} else {
				$arr = $aCategoryModel->find ( $_GET ['id'] )->getData ();
				if (count ( $arr ) === 0) {
					UtilComponent::redirect ( sprintf ( "%s?controller=AdminArticleCategories&action=Index&err=%s", $_SERVER ['PHP_SELF'], 'AG08' ) );
				}
				$arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $arr ['id'], 'ACategory' );
				$routers = RouterModel::factory()
				->where('t1.type', RouterModel::TYPE_ARTICLE_CATEGORY)
				->where('t1.controller', self::FRONTEND_ARTICLE_CATEGORY_CONTROLLER)
				->where('t1.action', "Articles")
				->where('t1.foreign_id', $_GET['id'])
				->findAll()
				->getData();
				if (!empty($routers)) {
				    foreach ($routers as $router) {
				        $arr['i18n'][$router['locale_id']]['url'] = $router['url'];
				    }
				}
				$this->set ( 'arr', $arr );
				$this->set ( 'node_arr', ACategoryModel::factory ()->getNode ( $this->getLocaleId (), 1));
				$this->set ( 'child_arr', $aCategoryModel->reset ()->getNode ( $this->getLocaleId (), $arr ['id']) );
				$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left outer' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
				$lp_arr = array ();
				foreach ( $locale_arr as $v ) {
					$lp_arr [$v ['id'] . "_"] = $v ['file'];
				}
				$this->set ( 'lp_arr', $locale_arr );
				$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
				$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
				$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
				$this->appendJs ( 'jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/' );
				$this->appendJs ( 'jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/' );
				$this->appendCss ( 'jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/' );
				$this->appendJs ( 'chosen.jquery.js', THIRD_PARTY_PATH . 'harvest/chosen/' );
				$this->appendCss ( 'chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
				$this->appendJs ( "AdminArticleCategories.js" );
				$this->appendJs ( 'index.php?controller=Admin&action=Messages', $this->baseUrl(), true );
			}
		} else {
			$this->set ( 'status', 2 );
		}
	}
	
	protected function createItemSorts($categoryId)
	{
	    $ArticleModel = ArticleModel::factory()->select('id')
	    ->where ( 'status', ArticleModel::STATUS_ACTIVE )
	    ->orderBy ( '`id` DESC' );
	    $ArticleModel->where(sprintf("t1.id IN (SELECT `article_id` FROM `%s` WHERE `category_id` = '%u')", ACategoryArticleModel::factory()->getTable(), (int) $categoryId));
	    $articleArr = $ArticleModel->findAll()->getData();
	    if (!empty($articleArr)) {
	        $batchValues = [];
	        $sort = 1;
	        foreach ($articleArr as $article) {
	            $batchValues[] = [
	                $article['id'],
	                ItemSortModel::TYPE_ARTICLE_CATEGORY,
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
	    $record = ItemSortModel::factory()->where('foreign_type_id', $categoryId)->where('type', ItemSortModel::TYPE_ARTICLE_CATEGORY)->limit(1)->findAll()->first();
	    return empty($record);
	}
	
}
?>