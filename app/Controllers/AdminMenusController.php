<?php
namespace App\Controllers;

use App\Models\MenuModel;
use App\Plugins\Gallery\Models\GalleryModel;
use App\Models\MultiLangModel;
use App\Models\MenuItemModel;
use App\Plugins\Locale\Models\LocaleModel;
use App\Models\SliderModel;
use App\Models\TagModel;
use App\Controllers\Components\UtilComponent;
use App\Models\ArticleModel;
use App\Models\ACategoryModel;
use App\Models\PCategoryModel;
use App\Models\PageModel;
use App\Models\CategoryModel;

class AdminMenusController extends AdminController
{
	
	public function DeleteMenu()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			if (isset($_GET['id']) && (int) $_GET['id'] > 0 && MenuModel::factory()->set('id', $_GET['id'])->erase()->getAffectedRows() == 1)
			{
				GalleryModel::factory()->where('foreign_id', $_GET['id'])->eraseAll();
				AppController::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => ''));
			}
			AppController::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => ''));
		}
		exit;
	}
	
	public function DeleteMenuBulk()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			if (isset($_POST['record']) && !empty($_POST['record']))
			{
				MenuModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
				MultiLangModel::factory()->where('model', 'Menu')->whereIn('foreign_id', $_POST['record'])->eraseAll();
				AppController::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => ''));
			}
			AppController::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => ''));
		}
		exit;
	}
	
	public function GetMenu()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			$menuModel = MenuModel::factory();
			if (isset($_GET['q']) && !empty($_GET['q']))
			{
				$q = str_replace(array('_', '%'), array('\_', '\%'), trim($_GET['q']));
				$menuModel->where('t2.content LIKE', "%$q%");
			}

			if (isset($_GET['is_active']) && strlen($_GET['is_active']) > 0 && in_array($_GET['is_active'], array(1, 0)))
			{
				$menuModel->where('t1.status', $_GET['is_active']);
			}

			$column = 'id';
			$direction = 'ASC';
			if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
			{
				$column = $_GET['column'];
				$direction = strtoupper($_GET['direction']);
			}

			$total = $menuModel->findCount()->getData();
			$rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
			$pages = ceil($total / $rowCount);
			$page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
			$offset = ((int) $page - 1) * $rowCount;
			if ($page > $pages)
			{
				$page = $pages;
			}

			$data = $menuModel
			->select('t1.*')
			->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
				
			AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
		}
		exit;
	}
	
	private function getEventType($key) {
		$name = '';
		switch($key) {
			
		}
		
		return $name;
	}
	
	private function getTypeName($key) {
		$name = '';
		if($key == 'G') {
			$name = __('stype_statarr_ARRAY_G', false, true);
			echo $name;
		} else if($key == 'S') {
			$name = __('stype_statarr_ARRAY_S', false, true);
		}
		return $name;
	}
	
	public function Index()
	{
		$this->checkLogin();
		$this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
		$this->appendJs('AdminMenus.js');
		$this->appendJs('index.php?controller=Admin&action=Messages', $this->baseUrl(), true);
	}
	
	public function SaveMenu()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			$menuModel = MenuModel::factory();
			if (!in_array($_POST['column'], $menuModel->getI18n()))
			{
				$menuModel->set('id', $_GET['id'])->modify(array($_POST['column'] => $_POST['value']));
			} else {
				MultiLangModel::factory()->updateMultiLang(array($this->getLocaleId() => array($_POST['column'] => $_POST['value'])), $_GET['id'], 'Menu', 'data');
			}
		}
		exit;
	}
	public function Create()
	{
		$this->checkLogin();
		if (isset($_POST['menu_create']))
		{
			$data = array();
			$err = 'MENU0013';
			$data['status'] = 'T';
			$data['uuid_code'] = uniqid();
			$id = MenuModel::factory(array_merge($_POST, $data))->insert()->getInsertId();
			if ($id !== false && (int) $id > 0)
			{
				if (isset($_POST['i18n']))
				{
					MultiLangModel::factory()->saveMultiLang($_POST['i18n'], $id, 'Menu');
				}						
			} else {
				$err = 'MENU0014';
			}

			UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminMenus&action=Update&id=$id&err=$err");
		} else {
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
			$this->appendJs('AdminMenus.js');
		}
	}	
	public function Update()
	{
		$this->checkLogin();
		if (isset($_POST['menu_update']) && isset($_POST['id']) && (int) $_POST['id'] > 0)
		{
			$err = 'CON06';
			$data = array();
			$postData = $_POST;	

			MenuModel::factory()->set('id', $postData['id'])->modify(array_merge($postData, $data));
			
			if($err == 'CON06')
			{
				UtilComponent::redirect($this->baseUrl() . "index.php?controller=AdminMenus&action=Index&err=CON06");
			}else{
				UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminMenus&action=Update&id=".$postData['id']."&err=$err");
			}
			
		} else {
			$arr = MenuModel::factory()->find($_GET['id'])->getData();
			
			if (empty($arr))
			{
				UtilComponent::redirect($this->baseUrl(). "index.php?controller=AdminMenus&action=Index&err=ADEP07");
			}
			$this->set('arr', $arr);
			
			$this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
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
			$this->appendJs('AdminMenus.js');
		}

	}

	public function UpdateFrontendMenu()
	{
		$this->checkLogin();
		$this->setLayout('AdminEmptyLayout');
		if (isset($_POST['menu_update']) && isset($_POST['id']) && (int) $_POST['id'] > 0)
		{
			$err = 'CON06';
			$data = array();
			$postData = $_POST;	

			MenuModel::factory()->set('id', $postData['id'])->modify(array_merge($postData, $data));
			
			if($err == 'CON06')
			{
				UtilComponent::redirect($this->baseUrl() . "index.php?controller=AdminMenus&action=Index&err=CON06");
			}else{
				UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminMenus&action=Update&id=".$postData['id']."&err=$err");
			}
			
		} else {
			$arr = MenuModel::factory()->find($_GET['id'])->getData();
			
			if (empty($arr))
			{
				UtilComponent::redirect($this->baseUrl(). "index.php?controller=AdminMenus&action=Index&err=ADEP07");
			}
			$this->set('arr', $arr);
			
			$this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
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
			$this->appendJs('AdminMenus.js');
		}
	}
	
	
	public function AddMenuItem() {
		$this->setAjax(true);
		
		if ($this->isXHR() && $this->isLoged())
		{
				
			if (isset($_POST['menu_item_add']))
			{
				
			} else {
				$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
				$lp_arr = array ();
				foreach ( $locale_arr as $item ) {
					$lp_arr [$item ['id'] . "_"] = $item ['file'];
				}
				$this->set ( 'lp_arr', $locale_arr );
				$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
				$menuId = @$_GET['menu_id']; 
				$this->set('menu_id', $menuId);
				$this->set ( 'node_arr', MenuItemModel::factory ()->getNode ( $this->getLocaleId (), 1, $menuId) );				
				$this->set('link_type_arr', self::getLinkTypes());
			}
			
		
		}		
	}
	
	public static function getLinkTypes()
	{
		return [
					MenuItemModel::LINK_TYPE_DEFAULT => 'Link',
					MenuItemModel::LINK_TYPE_SINGLE_ARTICLE => 'Bài viết',
					MenuItemModel::LINK_TYPE_ARTICE_CATEGORY => 'Danh mục bài viết',
					MenuItemModel::LINK_TYPE_PAGE => 'Trang',
					MenuItemModel::LINK_TYPE_PAGE_CATEGORY => 'Danh mục trang',
					MenuItemModel::LINK_TYPE_PRODUCT => 'Sản phẩm',
					MenuItemModel::LINK_TYPE_PRODUCT_CATEGORY => 'Danh mục sản phẩm',
					MenuItemModel::LINK_TYPE_TAG => 'Tag'
				];
	}
	
	public function SelectLinkType() {
		$this->setAjax(true);
		if ($this->isXHR() && $this->isLoged()) {
			if (isset($_POST['link_type'])) {
				$linkType = (int)$_POST['link_type'];
				switch ($linkType) {
					case MenuItemModel::LINK_TYPE_DEFAULT:
						$this->set('link_type', MenuItemModel::LINK_TYPE_DEFAULT);
						break;
					case MenuItemModel::LINK_TYPE_SINGLE_ARTICLE:
						$this->set('link_type', MenuItemModel::LINK_TYPE_SINGLE_ARTICLE);
						$articleArr = ArticleModel::factory ()
							->select ( 't1.*, t2.content AS article_name' )
							->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Article' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'article_name'", 'left' )
							->findAll()
							->getData();
						$this->set('article_arr', $articleArr);
						break;
					case MenuItemModel::LINK_TYPE_ARTICE_CATEGORY:
						$this->set('link_type', MenuItemModel::LINK_TYPE_ARTICE_CATEGORY);
						$this->set ( 'category_arr', ACategoryModel::factory ()->getNode ( $this->getLocaleId (), 1 ) );
						break;
					case MenuItemModel::LINK_TYPE_PAGE_CATEGORY :
						$this->set ( 'link_type', MenuItemModel::LINK_TYPE_PAGE_CATEGORY );
						$this->set ( 'category_arr', PCategoryModel::factory ()->getNode ( $this->getLocaleId (), 1) );
						break;						
					case MenuItemModel::LINK_TYPE_PAGE:
						$this->set('link_type', MenuItemModel::LINK_TYPE_PAGE);
						$pageArr = PageModel::factory ()
							->select ( 't1.*, t2.content AS page_name' )
							->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Page' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'page_name'", 'left' )
							->findAll()
							->getData();
						$this->set('page_arr', $pageArr);
						break;
					case MenuItemModel::LINK_TYPE_TAG:
						$this->set('link_type', MenuItemModel::LINK_TYPE_TAG);
						$this->set ( 'tag_arr', TagModel::factory()->findAll()->getData());
						break;
					case MenuItemModel::LINK_TYPE_GALLERY:
						$this->set('link_type', MenuItemModel::LINK_TYPE_GALLERY);
						$galleryArr =  SliderModel::factory()->getData();
						$this->set('gallery_arr',$galleryArr);
						break;
					case MenuItemModel::LINK_TYPE_PRODUCT_CATEGORY:
					    $this->set('link_type', MenuItemModel::LINK_TYPE_PRODUCT_CATEGORY);
					    $this->set ( 'node_arr', CategoryModel::factory ()->getNode ( $this->getLocaleId (), 1 ) );
					    break;
				}
			}
		}
	}
	
	
	public function EditMenuItem() {
		$this->setAjax(true);
		
		if ($this->isXHR() && $this->isLoged())
		{
		
			if (isset($_POST['menu_item_edit']))
			{

			} else {
				$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
				$lp_arr = array ();
				foreach ( $locale_arr as $item ) {
					$lp_arr [$item ['id'] . "_"] = $item ['file'];
				}
				$this->set ( 'lp_arr', $locale_arr );
				$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
				$this->set('id', $_GET['id']);
				$menuId = @$_POST['menu_id'];
				$this->set('menu_id', $menuId);
				$this->set ( 'node_arr', MenuItemModel::factory ()->getNode ( $this->getLocaleId (), 1, $menuId) );
				$arr = MenuItemModel::factory ()->find((int)$_GET['id'])->getData();
				$arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $arr ['id'], 'MenuItem' );
				$this->set('arr', $arr);
				$this->set('link_type_arr', [
					MenuItemModel::LINK_TYPE_DEFAULT => 'Link',
					MenuItemModel::LINK_TYPE_SINGLE_ARTICLE => 'Bài viết',
					MenuItemModel::LINK_TYPE_ARTICE_CATEGORY => 'Danh mục bài viết',
					MenuItemModel::LINK_TYPE_PAGE => 'Trang',
				    MenuItemModel::LINK_TYPE_PAGE_CATEGORY => 'Danh mục trang',
					MenuItemModel::LINK_TYPE_PRODUCT => 'Sản phẩm',
					MenuItemModel::LINK_TYPE_PRODUCT_CATEGORY => 'Danh mục sản phẩm',
					MenuItemModel::LINK_TYPE_TAG => 'Tag'
				]);
			}
		
		}		
	}
	
	
}
?>