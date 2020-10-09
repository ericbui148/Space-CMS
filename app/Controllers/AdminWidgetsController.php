<?php
namespace App\Controllers;

use App\Models\WidgetModel;
use App\Models\MultiLangModel;
use App\Plugins\Locale\Models\LocaleModel;
use App\Controllers\Components\UtilComponent;
use App\Models\MenuModel;
use App\Models\SliderModel;
use App\Models\ACategoryModel;
use App\Controllers\Components\Widgets\NewProductsWidget;
use App\Controllers\Components\Widgets\FeaturedProductsWidget;
use App\Controllers\Components\Widgets\PromotionProductsWidget;
use App\Models\ItemSortModel;

class AdminWidgetsController extends AdminController
{
    const SORT_WIDGET_LIST = [
        WidgetModel::WIDGET_TYPE_NEW_PRODUCTS => 'NewProductsWidget',
        WidgetModel::WIDGET_TYPE_FEATURED_PRODUCTS => 'FeaturedProductsWidget',
        WidgetModel::WIDGET_TYPE_PRODUCT_PROMOTION => 'PromotionProductsWidget',
        WidgetModel::WIDGET_TYPE_PRODUCT_CAT => 'ProductCatWidget',
        WidgetModel::WIDGET_TYPE_LASTEST_ARTICLES => 'LastestArticlesWidget',
        WidgetModel::WIDGET_TYPE_ARTICLE_CAT => 'NewsCategoryWidget',
        WidgetModel::WIDGET_TYPE_PAGE_CAT => 'PageCategoryWidget',
    ];
	public static function getWidgetList()
	{
		return [
			WidgetModel::WIDGET_TYPE_HTML => __('widget_type_html', true),
			WidgetModel::WIDGET_TYPE_MENU => __('widget_type_menu', true),
			WidgetModel::WIDGET_TYPE_SLIDER => 'Slider hoặc Gallery',
			WidgetModel::WIDGET_TYPE_ARTICLE_CAT => __('widget_type_news_cat', true),
		    WidgetModel::WIDGET_TYPE_NEW_PRODUCTS => "Sản phẩm mới",
		    WidgetModel::WIDGET_TYPE_PRODUCT_CAT => "Danh mục sản phẩm",
		    WidgetModel::WIDGET_TYPE_FEATURED_PRODUCTS => "Sản phẩm nổi bật",
		    WidgetModel::WIDGET_TYPE_PRODUCT_PROMOTION => "Sản phẩm khuyến mãi",
			WidgetModel::WIDGET_TYPE_LASTEST_ARTICLES => 'Bài viết mới',
			WidgetModel::WIDGET_TYPE_TAG => 'Tags',
			WidgetModel::WIDGET_TYPE_SOCIAL_LINK => 'Liên kết mạng xã hội',
			WidgetModel::WIDGET_TYPE_GOOLE_MAP => 'Google Map',
			WidgetModel::WIDGET_TYPE_MULTIPLE_LANGUAGE => 'Ngôn ngữ',
			WidgetModel::WIDGET_TYPE_TESTIMONIAl => 'Đánh giá của khách hàng'
	
		];
	}

	public function DeleteWidget()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			if (isset($_GET['id']) && (int) $_GET['id'] > 0 && WidgetModel::factory()->set('id', $_GET['id'])->erase()->getAffectedRows() == 1)
			{
				AppController::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => ''));
			}
			AppController::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => ''));
		}
		exit;
	}
	
	public function DeleteWidgetBulk()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			if (isset($_POST['record']) && !empty($_POST['record']))
			{
				WidgetModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
				MultiLangModel::factory()->where('model', 'Widget')->whereIn('foreign_id', $_POST['record'])->eraseAll();
				AppController::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => ''));
			}
			AppController::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => ''));
		}
		exit;
	}
	
	public function GetWidget()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			$widgetModel = WidgetModel::factory();
			
			if (isset($_GET['q']) && !empty($_GET['q']))
			{
				$q = str_replace(array('_', '%'), array('\_', '\%'), trim($_GET['q']));
				$widgetModel->where('t1.name LIKE', "%$q%");
			}

			if (isset($_GET['is_active']) && strlen($_GET['is_active']) > 0 && in_array($_GET['is_active'], array(1, 0)))
			{
				$widgetModel->where('t1.status', $_GET['is_active']);
			}

			$column = 'id';
			$direction = 'DESC';
			if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
			{
				$column = $_GET['column'];
				$direction = strtoupper($_GET['direction']);
			}

			$total = $widgetModel->findCount()->getData();
			$rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
			$pages = ceil($total / $rowCount);
			$page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
			$offset = ((int) $page - 1) * $rowCount;
			if ($page > $pages)
			{
				$page = $pages;
			}

			$data = $widgetModel
			->select('t1.*')
			->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
			$widgetList = $this->getWidgetList();
			foreach($data as &$widget) {
				$widget['type_name'] =  !empty($widgetList[$widget['type']])? $widgetList[$widget['type']] : null;
			}
				
			AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
		}
		exit;
	}
	
	public function Index()
	{
		$this->checkLogin();
		$this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
		$this->appendJs('AdminWidgets.js');
		$this->appendJs('index.php?controller=Admin&action=Messages', $this->baseUrl(), true);
	}
	
	public function SaveWidget()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			$widgetModel = WidgetModel::factory();
			if (!in_array($_POST['column'], $widgetModel->getI18n()))
			{
				$widgetModel->set('id', $_GET['id'])->modify(array($_POST['column'] => $_POST['value']));
			} else {
				MultiLangModel::factory()->updateMultiLang(array($this->getLocaleId() => array($_POST['column'] => $_POST['value'])), $_GET['id'], 'Widget', 'data');
			}
		}
		exit;
	}
	
	public function SaveHtmlWidget()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			$postData = $_POST;
			$record = WidgetModel::factory()->where('t1.name', $postData['name'])->limit(1)->findAll()->getData();
			if(!empty($record[0])) {
				$postData['id'] = $record[0]['id'];
				WidgetModel::factory()->set('id', $postData['id'])->modify($postData);
				$postData ['i18n'][$postData['locale']]['content'] = $postData['content'];
				if (isset ( $postData ['i18n'] )) {
					MultiLangModel::factory ()->updateMultiLang ( $postData ['i18n'], $postData ['id'], 'Widget', 'data' );
				}
			}
		}
		exit;
	}
	
	public function Create()
	{
		$this->checkLogin();
		if (isset($_POST['widget_create']))
		{
			$data = array();
			$err = 'WIDGET0013';
			$type = $_POST['type'];
			$widgetValues = [
			    WidgetModel::WIDGET_TYPE_MENU => @$_POST['menu_id'],
			    WidgetModel::WIDGET_TYPE_SLIDER => @$_POST['slider_id'],
			    WidgetModel::WIDGET_TYPE_ARTICLE_CAT => @$_POST['article_cat_id'],
			    WidgetModel::WIDGET_TYPE_PAGE_CAT => @$_POST['page_cat_id'],
			    WidgetModel::WIDGET_TYPE_PRODUCT_CAT => @$_POST['product_cat_id'],
			    
			];
			if (!empty($widgetValues[$type])) {
			    $data['value'] = $widgetValues[$type];
			}
			$data['status'] = 'T';
			$data['uuid_code'] = uniqid();
			$id = WidgetModel::factory(array_merge($_POST, $data))->insert()->getInsertId();
			if ($id !== false && (int) $id > 0)
			{
				if (isset($_POST['i18n']))
				{
					MultiLangModel::factory()->saveMultiLang($_POST['i18n'], $id, 'Widget');
				}
				$sortWidgetList = self::SORT_WIDGET_LIST;
				if (!empty($sortWidgetList[$type])) {
				    $class = __NAMESPACE__.'\\Components\\Widgets\\'.$sortWidgetList[$type];
				    $class::createItemSorts($id);
				}

			} else {
				$err = 'WIDGET0014';
			}
			
			if($err == 'WIDGET0013' || $err == 'WIDGET0014')
			{
				UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminWidgets&action=Index&err=$err");
			}else{
				UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminWidgets&action=Update&id=$id&err=$err");
			}
		} else {
			$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
			$lp_arr = array ();
			foreach ( $locale_arr as $item ) {
				$lp_arr [$item ['id'] . "_"] = $item ['file'];
			}
			$this->set ( 'lp_arr', $locale_arr );
			$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
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
			$this->appendJs ( 'codemirror.js', THIRD_PARTY_PATH . 'codemirror/lib/');
			$this->appendCss ( 'codemirror.css', THIRD_PARTY_PATH . 'codemirror/lib/');
			$this->appendJs ( 'xml.js', THIRD_PARTY_PATH . 'codemirror/mode/xml/');
			$this->appendJs ( 'javascript.js', THIRD_PARTY_PATH . 'codemirror/mode/javascript/');
			$this->appendJs ( 'css.js', THIRD_PARTY_PATH . 'codemirror/mode/css/');
			$this->appendJs ( 'htmlmixed.js', THIRD_PARTY_PATH . 'codemirror/mode/htmlmixed/');
			$this->appendJs ( 'matchbrackets.js', THIRD_PARTY_PATH . 'codemirror/addon/edit/');
			$this->appendJs('AdminWidgets.js');
		}
	}	
	public function Update()
	{
		$this->checkLogin();
				
		if (isset($_POST['widget_update']) && isset($_POST['id']) && (int) $_POST['id'] > 0)
		{
			$err = 'WIDGET06';
			$data = array();
			$postData = $_POST;
			$type = $_POST['type'];
			$widgetValues = [
			    WidgetModel::WIDGET_TYPE_MENU => @$_POST['menu_id'],
			    WidgetModel::WIDGET_TYPE_SLIDER => @$_POST['slider_id'],
			    WidgetModel::WIDGET_TYPE_ARTICLE_CAT => @$_POST['article_cat_id'],
			    WidgetModel::WIDGET_TYPE_PAGE_CAT => @$_POST['page_cat_id'],
			    WidgetModel::WIDGET_TYPE_PRODUCT_CAT => @$_POST['product_cat_id'],
			    
			];
			if (!empty($widgetValues[$type])) {
			    $data['value'] = $widgetValues[$type];
			}
			WidgetModel::factory()->set('id', $postData['id'])->modify(array_merge($postData, $data));
			if (isset ( $_POST ['i18n'] )) {
				MultiLangModel::factory ()->updateMultiLang ( $_POST ['i18n'], $_POST ['id'], 'Widget', 'data' );
			}
			if ($this->needCreateSort($postData['id'], $type, ItemSortModel::TYPE_WIDGET_PRODUCTS)) {
			    $sortWidgetList = self::SORT_WIDGET_LIST;
			    $class = __NAMESPACE__.'\\Components\\Widgets\\'.$sortWidgetList[$type];
			    $class::createItemSorts($postData['id']);
			}
			UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminWidgets&action=Update&id=".$postData['id']."&err=$err");
			
		} else {
			$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
			$lp_arr = array ();
			foreach ( $locale_arr as $item ) {
				$lp_arr [$item ['id'] . "_"] = $item ['file'];
			}
			$this->set ( 'lp_arr', $locale_arr );
			$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
			$arr = WidgetModel::factory()->find($_GET['id'])->getData();
			if (empty($arr))
			{
				UtilComponent::redirect($this->baseUrl(). "index.php?controller=AdminWidgets&action=Index&err=ADEP07");
			}
			$arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $arr ['id'], 'Widget' );
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
			$this->appendCss ( 'codemirror.css', THIRD_PARTY_PATH . 'codemirror/lib/');
			$this->appendJs ( 'codemirror.js', THIRD_PARTY_PATH . 'codemirror/lib/');
			$this->appendJs ( 'xml.js', THIRD_PARTY_PATH . 'codemirror/mode/xml/');
			$this->appendJs ( 'javascript.js', THIRD_PARTY_PATH . 'codemirror/mode/javascript/');
			$this->appendJs ( 'css.js', THIRD_PARTY_PATH . 'codemirror/mode/css/');
			$this->appendJs ( 'htmlmixed.js', THIRD_PARTY_PATH . 'codemirror/mode/htmlmixed/');
			$this->appendJs ( 'matchbrackets.js', THIRD_PARTY_PATH . 'codemirror/addon/edit/');			
			$this->appendJs('AdminWidgets.js');
		}
	}
	
	protected function needCreateSort($widgetId, $widgetType, $itemSortType)
	{
	    $record = ItemSortModel::factory()->where('foreign_type_id', $widgetId)->where('type', $itemSortType)->findAll()->first();
	    $sortWidgetList = self::SORT_WIDGET_LIST;
	    return !empty($sortWidgetList[$widgetType]) && empty($record);
	}

	public function UpdateFrontendWidget()
	{
		$this->checkLogin();
		$this->setLayout('AdminEmptyLayout');

		if (isset($_POST['widget_update']) && isset($_POST['id']) && (int) $_POST['id'] > 0)
		{
			$data = array();
			$postData = $_POST;	
			$type = $_POST['type'];
			switch ($type) {
				case WidgetModel::WIDGET_TYPE_HTML:
					break;
				case WidgetModel::WIDGET_TYPE_MENU:
					$data['value'] = $_POST['menu_id'];
					break;
				case WidgetModel::WIDGET_TYPE_SLIDER:
					$data['value'] = $_POST['slider_id'];
					break;
				case WidgetModel::WIDGET_TYPE_ARTICLE_CAT:
					$data['value'] = $_POST['news_cat_id'];
					break;
				case WidgetModel::WIDGET_TYPE_PRODUCT_CAT:
					$data['value'] = $_POST['product_cat_id'];
					break;						
			}
			WidgetModel::factory()->set('id', $postData['id'])->modify(array_merge($postData, $data));
			if (isset ( $_POST ['i18n'] )) {
				MultiLangModel::factory ()->updateMultiLang ( $_POST ['i18n'], $_POST ['id'], 'Widget', 'data' );
			}

			UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminWidgets&action=UpdateFrontendWidget&id=".$postData['id']);
			
		} else {
			$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
			$lp_arr = array ();
			foreach ( $locale_arr as $item ) {
				$lp_arr [$item ['id'] . "_"] = $item ['file'];
			}
			$this->set ( 'lp_arr', $locale_arr );
			$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
			$arr = WidgetModel::factory()->find($_GET['id'])->getData();
			
			if (empty($arr))
			{
				UtilComponent::redirect($this->baseUrl(). "index.php?controller=AdminWidgets&action=Index&err=ADEP07");
			}
			$arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $arr ['id'], 'Widget' );
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
			$this->appendJs('AdminWidgets.js');
		}
	}
	
	public function SelectWidgetType() {
		$this->setAjax(true);
		if ($this->isXHR() && $this->isLoged()) {
			if (isset($_POST['widget_type'])) {
				$widgetType = (int)$_POST['widget_type'];
				switch ($widgetType) {
					case WidgetModel::WIDGET_TYPE_HTML:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_HTML);
						if(isset($_POST['widget_id'])) {
							$arr = WidgetModel::factory()->find($_POST['widget_id'])->getData();
							$arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $arr ['id'], 'Widget' );
							$this->set('arr', $arr);
						}
						break;
					case WidgetModel::WIDGET_TYPE_MENU:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_MENU);
						$menuArr = MenuModel::factory()
							->findAll()
							->getData();
						$this->set('menu_arr', $menuArr);
						break;
					case WidgetModel::WIDGET_TYPE_SLIDER:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_SLIDER);
						$sliderArr = SliderModel::factory()
							->whereIn('t1.type', ['SLIDER', 'GALLERY'])
							->findAll()
							->getData();
						$this->set('slider_arr', $sliderArr);
						break;
					case WidgetModel::WIDGET_TYPE_ARTICLE_CAT :
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_ARTICLE_CAT);
						$this->set ( 'node_arr', ACategoryModel::factory ()->getNode ( $this->getLocaleId (), 1) );
						break;					
					case WidgetModel::WIDGET_TYPE_BEST_SELLING_PRODUCTS:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_BEST_SELLING_PRODUCTS);
						break;
					case WidgetModel::WIDGET_TYPE_FEATURED_PRODUCT:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_FEATURED_PRODUCT);
						break;
					case WidgetModel::WIDGET_TYPE_DAILY_PRODUCT:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_DAILY_PRODUCT);
						break;
					case WidgetModel::WIDGET_TYPE_LASTEST_PRODUCT:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_LASTEST_PRODUCT);
						break;
					case WidgetModel::WIDGET_TYPE_RELATED_PRODUCT:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_RELATED_PRODUCT);
						break;
					case WidgetModel::WIDGET_TYPE_LASTEST_ARTICLES:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_LASTEST_ARTICLES);
						break;
					case WidgetModel::WIDGET_TYPE_FEATURED_NEWS:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_FEATURED_NEWS);
						break;	
					case WidgetModel::WIDGET_TYPE_CONTACT_FORM:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_CONTACT_FORM);
						break;
					case WidgetModel::WIDGET_TYPE_SOCIAL_LINK:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_SOCIAL_LINK);
						break;	
					case WidgetModel::WIDGET_TYPE_LINK:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_LINK);
						break;	
					case WidgetModel::WIDGET_TYPE_VIDEO:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_VIDEO);
						break;
					case WidgetModel::WIDGET_TYPE_ADDRESS:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_ADDRESS);
						break;	
					case WidgetModel::WIDGET_TYPE_SUBSCRIBER:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_SUBSCRIBER);
						break;
					case WidgetModel::WIDGET_TYPE_GALLERY:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_GALLERY);
						break;
					case WidgetModel::WIDGET_TYPE_IMAGE:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_IMAGE);
						break;	
					case WidgetModel::WIDGET_TYPE_LOGO:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_LOGO);
						break;
					case WidgetModel::WIDGET_TYPE_TESTIMONIAl:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_TESTIMONIAl);
						break;	
					case WidgetModel::WIDGET_TYPE_LIVECHAT:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_LIVECHAT);
						break;	
					case WidgetModel::WIDGET_TYPE_CART:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_CART);
						break;	
					case WidgetModel::WIDGET_TYPE_POPUP_FORM:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_POPUP_FORM);
						break;
					case WidgetModel::WIDGET_TYPE_SURVEY_FORM:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_SURVEY_FORM);
						break;
					case WidgetModel::WIDGET_TYPE_LANGUAGE:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_LANGUAGE);
						break;
					case WidgetModel::WIDGET_TYPE_SUPPORT_ONLINE:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_SUPPORT_ONLINE);
						break;	
					case WidgetModel::WIDGET_TYPE_MARQUEEN:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_MARQUEEN);
						break;
					case WidgetModel::WIDGET_TYPE_IFRAME:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_IFRAME);
						break;
					case WidgetModel::WIDGET_TYPE_TAG:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_TAG);
						break;
					case WidgetModel::WIDGET_TYPE_ADVERTISEMENT:
						$this->set('widget_type', WidgetModel::WIDGET_TYPE_ADVERTISEMENT);
						break;						
				}
			}
		}
		
		$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
		$lp_arr = array ();
		foreach ( $locale_arr as $item ) {
			$lp_arr [$item ['id'] . "_"] = $item ['file'];
		}
		$this->set ( 'lp_arr', $locale_arr );
		$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
		
	}
	
	
	
}
?>