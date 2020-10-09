<?php
namespace App\Controllers;

use Core\Framework\Registry;
use App\Controllers\Components\Widgets\WidgetFactory;
use App\Controllers\Components\UtilComponent;
use App\Models\AppModel;
use App\Models\OptionModel;
use App\Plugins\Locale\Models\LocaleModel;
use App\Models\CmsSettingModel;
use App\Models\MultiLangModel;
use Core\Framework\Components\ServicesJSONComponent;
use Core\Framework\Components\HttpComponent;
use App\Models\UserModel;
use Core\Framework\Controller;
use App\Models\WidgetModel;
use App\Models\RouterModel;
use App\Models\AHistoryModel;

class AppController extends Controller {
	public $request;
	public $defaultTax = 'SCart_Tax';
	public $defaultHash = 'SCart_Hash';
	public $cart = NULL;
	public $models = array ();
	public $defaultLocale = 'admin_locale_id';
	private $layoutRange = array (
			1,
			2,
			3
	);
	public $frontendLocale = 'frontend_locale_id';
	public $defaultFields = 'fields';
	public $defaultFieldsIndex = 'fields_index';
	protected function loadSetFields($force = FALSE) {

		$registry = Registry::getInstance ();
		if ($force || ! isset ( $_SESSION [$this->defaultFieldsIndex] ) || $_SESSION [$this->defaultFieldsIndex] != @$this->option_arr ['o_fields_index'] || ! isset ( $_SESSION [$this->defaultFields] ) || empty ( $_SESSION [$this->defaultFields] )) {
			AppController::setFields ( $this->getLocaleId () );
			if ($registry->is ( 'fields' )) {
				$_SESSION [$this->defaultFields] = $registry->get ( 'fields' );
			}
			$_SESSION [$this->defaultFieldsIndex] = @$this->option_arr ['o_fields_index'];
		}
		if (isset ( $_SESSION [$this->defaultFields] ) && ! empty ( $_SESSION [$this->defaultFields] )) {
			$registry->set ( 'fields', $_SESSION [$this->defaultFields] );
		}
		$this->set('cms_setting', CmsSettingModel::factory()->limit(1)->findAll()->first());
		return TRUE;
	}
	
	public function getModel($key) {
		if (array_key_exists ( $key, $this->models )) {
			return $this->models [$key];
		}
		return false;
	}

	public function renderWidget($type = null, $name = null, $params = array()) {
		$widget = WidgetFactory::getInstance($type);
		if(!is_null($name)) {
			$widget->setName($name);
		}
		$params = array_merge($params, array(
			'locale_id' => $this->getLocaleId() == NULL? 1 : $this->getLocaleId(),
			'base_url' => $this->baseUrl(),
			'option_arr' => $this->tpl['option_arr'],
			'controller' => $this
		));
		$widget->init($params);
		$widget->run();
		
	}

	public function renderWidgetByName($name)
	{
		$widgetRecord = WidgetModel::factory()->where('t1.name', $name)->limit(1)->findAll()->getData();
		if (!empty($widgetRecord[0])) {
			$type = $widgetRecord[0]['type'];
			$widget = WidgetFactory::getInstance($type);
			$widget->setName($name);
			$params = [
				'locale_id' => $this->getLocaleId() == NULL? 1 : $this->getLocaleId(),
				'base_url' => $this->baseUrl(),
				'option_arr' => $this->tpl['option_arr'],
				'controller' => $this,
				'widget' => $widgetRecord[0]
			];
			$widget->init($params);
			$widget->run();
		}
	}
	

	public function getLayoutRange() {
	
		return $this->layoutRange;
	}
	public function GetLocale() {
		return isset ( $_SESSION [$this->defaultLocale] ) && ( int ) $_SESSION [$this->defaultLocale] > 0 ? ( int ) $_SESSION [$this->defaultLocale] : FALSE;
	}
	

	public function isOneAdminReady() {
		return $this->isAdmin ();
	}
	
	public function setModel($key, $value) {
		$this->models [$key] = $value;
		return true;
	}
	
	public static function setTimezone($timezone = "UTC") {

		if (in_array ( version_compare ( phpversion (), '5.1.0' ), array (
				0,
				1 
		) )) {
			date_default_timezone_set ( $timezone );
		} else {
			$safe_mode = ini_get ( 'safe_mode' );
			if ($safe_mode) {
				putenv ( "TZ=" . $timezone );
			}
		}
	}
	public static function setMySQLServerTime($offset = "-0:00") {

		AppModel::factory ()->prepare ( "SET SESSION time_zone = :offset;" )->exec ( compact ( 'offset' ) );
	}

	public function setTime() {

		if (isset ( $this->option_arr ['o_timezone'] )) {
			$offset = $this->option_arr ['o_timezone'] / 3600;
			if ($offset > 0) {
				$offset = "-" . $offset;
			} elseif ($offset < 0) {
				$offset = "+" . abs ( $offset );
			} elseif ($offset === 0) {
				$offset = "+0";
			}
			AppController::setTimezone ( 'Etc/GMT' . $offset );
			if (strpos ( $offset, '-' ) !== false) {
				$offset = str_replace ( '-', '+', $offset );
			} elseif (strpos ( $offset, '+' ) !== false) {
				$offset = str_replace ( '+', '-', $offset );
			}
			AppController::setMySQLServerTime ( $offset . ":00" );
		}
	}

	public function beforeFilter() {

		$this->appendJs ( 'jquery-1.8.2.min.js', THIRD_PARTY_PATH . 'jquery/' );
		$this->appendJs ( 'AdminCore.js' );
		$this->appendCss ( 'reset.css' );
		$this->appendJs ( 'jquery-ui.custom.min.js', THIRD_PARTY_PATH . 'jquery_ui/js/' );
		$this->appendCss ( 'jquery-ui.min.css', THIRD_PARTY_PATH . 'jquery_ui/css/smoothness/' );
		$this->appendCss ( 'all.css', FRAMEWORK_LIBS_PATH . '/css/' );
		$this->appendCss ( 'admin.css' );

		if ($this->request->controller != 'Installer') {
			$this->models ['Option'] = OptionModel::factory ();
			$this->option_arr = $this->models ['Option']->getPairs ( $this->getForeignId () );
			$this->set ( 'option_arr', $this->option_arr );
			$this->setTime ();
			if (! isset ( $_SESSION [$this->defaultLocale] )) {
				$locale_arr = LocaleModel::factory ()->where ( 'is_default', 1 )->limit ( 1 )->findAll ()->getData ();
				if (count ( $locale_arr ) === 1) {
					$this->setLocaleId ( $locale_arr [0] ['id'] );
				}
			}
			if (! in_array ( $this->request->action, array (
					'Preview' 
			) )) {
				$this->loadSetFields (true);
			}
		}

		$this->clearCropImageCache();
	}

	public static function clearCropImageCache()
	{
		if (!empty($_SESSION['crop_cache_images'])) {
			$imgs = $_SESSION['crop_cache_images'];
			foreach($imgs as $img)
			{
				@unlink($img);
			}
			unset($_SESSION['crop_cache_images']);
		}
	}
	
	protected function checkMaintainMode()
	{
		//Maintain mode
		$cmsSetting = CmsSettingModel::factory()->limit ( 1 )->findAll ()->getData();
		$isMaintainMode = isset($cmsSetting[0]['is_maintain']) && $cmsSetting[0]['is_maintain'] == 'T' && $this->is_front_page;
		if ($isMaintainMode) {
			if (!empty($cmsSetting[0]['maintain_url'])) {
				UtilComponent::redirect($cmsSetting[0]['maintain_url']);
			} else {
				$this->set('page', 'maintain');
				UtilComponent::redirect('/maintain');
			}
				
		}
	}

	public function isEditor() {

		return $this->getRoleId () == 2;
	}

	public function getForeignId() {

		return 1;
	}

	public static function setFields($locale) {

		if (isset ( $_SESSION ['lang_show_id'] ) && ( int ) $_SESSION ['lang_show_id'] == 1) {
			$fields = MultiLangModel::factory ()->select ( 'CONCAT(t1.content, CONCAT(":", t2.id, ":")) AS content, t2.key' )->join ( 'Field', "t2.id=t1.foreign_id", 'inner' )->where ( 't1.locale', $locale )->where ( 't1.model', 'Field' )->where ( 't1.field', 'title' )->findAll ()->getDataPair ( 'key', 'content' );
		} else {
			$fields = MultiLangModel::factory ()->select ( 't1.content, t2.key' )->join ( 'Field', "t2.id=t1.foreign_id", 'inner' )->where ( 't1.locale', $locale )->where ( 't1.model', 'Field' )->where ( 't1.field', 'title' )->findAll ()->getDataPair ( 'key', 'content' );
		}
		$registry = Registry::getInstance ();
		$tmp = array ();
		if ($registry->is ( 'fields' )) {
			$tmp = $registry->get ( 'fields' );
		}
		$arrays = array ();
		foreach ( $fields as $key => $value ) {
			if (strpos ( $key, '_ARRAY_' ) !== false) {
				list ( $prefix, $suffix ) = explode ( "_ARRAY_", $key );
				if (! isset ( $arrays [$prefix] )) {
					$arrays [$prefix] = array ();
				}
				$arrays [$prefix] [$suffix] = $value;
			}
		}
		$fields = array_merge ( $tmp, $fields, $arrays );
		$registry->set ( 'fields', $fields );
	}

	public static function jsonDecode($str) {
		$Services_JSON = new ServicesJSONComponent();
		return $Services_JSON->decode ( $str );
	}
	public static function jsonEncode($arr) {

		$Services_JSON = new ServicesJSONComponent ();
		return $Services_JSON->encode ( $arr );
	}

	public static function jsonResponse($arr) {

		header ( "Content-Type: application/json; charset=utf-8" );
		echo AppController::jsonEncode ( $arr );
		exit ();
	}

	public function getLocaleId() {

		return isset ( $_SESSION [$this->defaultLocale] ) && ( int ) $_SESSION [$this->defaultLocale] > 0 ? ( int ) $_SESSION [$this->defaultLocale] : false;
	}
	
	public function getFrontendLocaleId() {
	    return isset($_SESSION[FONTEND_TRANS_DICT]['locale_id'])? (int)$_SESSION[FONTEND_TRANS_DICT]['locale_id'] : false;
	}
	
	public function setFrontendLocaleId($locale_id) {
		$_SESSION [$this->frontendLocale] = ( int ) $locale_id;
	}

	public function setLocaleId($locale_id) {

		$_SESSION [$this->defaultLocale] = ( int ) $locale_id;
	}

	public function CheckInstall() {

		$this->setLayout ( 'Empty' );
		$result = array (
				'status' => 'OK',
				'code' => 200,
				'text' => 'Operation succeeded',
				'info' => array () 
		);
		$folders = array (
				'app/web/upload',
				'app/web/upload/products' 
		);
		foreach ( $folders as $dir ) {
			if (! is_writable ( $dir )) {
				$result ['status'] = 'ERR';
				$result ['code'] = 101;
				$result ['text'] = 'Permission requirement';
				$result ['info'] [] = sprintf ( 'Folder \'<span class="bold">%1$s</span>\' is not writable. You need to set write permissions (chmod 777) to directory located at \'<span class="bold">%1$s</span>\'', $dir );
			}
		}
		return $result;
	}

	public static function friendlyURL($str, $divider = '-') {
		return UtilComponent::post_slug($str, $divider);
	}

	public function getCoords($str) {
		if (! is_array ( $str )) {
			$_address = preg_replace ( '/\s+/', '+', $str );
			$_address = urlencode ( $_address );
		} else {
			$address = array ();
			$address [] = $str ['d_zip'];
			$address [] = $str ['d_address_1'];
			$address [] = $str ['d_city'];
			$address [] = $str ['d_state'];
			foreach ( $address as $k => $v ) {
				$tmp = preg_replace ( '/\s+/', '+', $v );
				$address [$k] = $tmp;
			}
			$_address = join ( ",+", $address );
		}
		$api = sprintf ( "https://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false", $_address );
		$http = new HttpComponent();
		$http->request ( $api );
		$response = $http->getResponse ();
		$geoObj = AppController::jsonDecode ( $response );
		$data = array ();
		if ($geoObj->status == 'OK') {
			$data ['lat'] = $geoObj->results [0]->geometry->location->lat;
			$data ['lng'] = $geoObj->results [0]->geometry->location->lng;
		} else {
			$data ['lat'] = array (
					'NULL' 
			);
			$data ['lng'] = array (
					'NULL' 
			);
		}
		return $data;
	}

	public function getAdminEmail() {
		$arr = UserModel::factory ()->findAll ()->orderBy ( "t1.id ASC" )->limit ( 1 )->getData ();
		return ! empty ( $arr ) ? $arr [0] ['email'] : null;
	}
	public function getAdminPhone() {

		$arr = UserModel::factory ()->findAll ()->orderBy ( "t1.id ASC" )->limit ( 1 )->getData ();
		return ! empty ( $arr ) ? (! empty ( $arr [0] ['phone'] ) ? $arr [0] ['phone'] : null) : null;
	}
	
	public function getI18n()
	{
		return isset($this->i18n) ? $this->i18n : array();
	}
	
	public function addRule($field)
	{
		if (!isset($this->validate['rules']))
		{
			$this->validate['rules'] = array();
		}
	
		if (!isset($this->validate['rules'][$field])
				&& isset($this->rules[$field]))
		{
			$this->validate['rules'][$field] = $this->rules[$field];
			return TRUE;
		}
	
		return FALSE;
	}
	
	public function removeRule($field, $rule=NULL)
	{
		if (isset($this->validate['rules'], $this->validate['rules'][$field]))
		{
			if (is_null($rule))
			{
				unset($this->validate['rules'][$field]);
				return TRUE;
			}
				
			if (is_array($this->validate['rules'][$field])
					&& is_string($rule)
					&& isset($this->validate['rules'][$field][$rule]))
			{
				unset($this->validate['rules'][$field][$rule]);
				return TRUE;
			}
		}
	
		return FALSE;
	}
	
	public static function addToHistory($record_id, $user_id, $table, $before, $after) {
	
		return AHistoryModel::factory ()->setAttributes ( array (
				'record_id' => $record_id,
				'user_id' => $user_id,
				'table_name' => $table,
				'before' => base64_encode ( serialize ( $before ) ),
				'after' => base64_encode ( serialize ( $after ) ),
				'ip' => $_SERVER ['REMOTE_ADDR']
		) )->insert ()->getInsertId ();
	}


	public function getLocaleFlagInfo() {
		$langFlags = [
			1 => [
				'text' => 'Việt Nam',
				'src' => 'core/framework/libs/wp/img/flags/vi.png'
			],
			2 => [
				'text' => 'English',
				'src' => 'core/framework/libs/wp/img/flags/gb.png'
			],
			3 => [
				'text' => 'Russian',
				'src' => 'core/framework/libs/wp/img/flags/ru.png'				
			],
			4 => [
				'text' => 'China',
				'src' => 'core/framework/libs/wp/img/flags/cn.png'				
			]			
		];
		$locale = $this->getLocaleId();
		return [
			'display_flag' => $langFlags[$locale],
			'lang_flags' => $langFlags
		];

	}
	
	/**
	 * Get front translation
	 * @return array Front translation
	 */
	protected function getFrontTranslation()
	{
		$locale = $this->getFrontendLocaleId();
		$themPath = THEME_PATH_PUBLIC;
		$translations = [];
		if (!empty($themPath)) {
			$string = file_get_contents($themPath.'languages/'.$locale.".json");
			$translations = json_decode($string, true);
		}
		
		return $translations;
	}

	public function getTheme()
	{
		return THEME_NAME;
	}

    /**
	 * Create dynamic router
	 * @param string $url
	 * @param int $foreignId
	 * @param string $controller
	 * @param string $action
	 */
	protected function updateDynamicRouter($type, $url, $foreignId, $controller, $action, $key = null)
	{
		$hashUrl = hash('md5', $url);
		$routerRecord = RouterModel::factory()->where('t1.type', $type)->where('t1.foreign_id', $foreignId)->findAll()->getData();
		if (empty($routerRecord[0])) {
			$routerData = [
				'url' => $url,
				'hash' => $hashUrl,
				'controller' => $controller,
				'action' => $action,
				'type' => $type,
				'foreign_id' => $foreignId
			];
			if (!empty($key)) {
				$routerData ['params'] = "$key=" . (int)$foreignId;
			} else {
				$routerData ['params'] = 'id=' . (int)$foreignId;
			}
			RouterModel::factory()->setAttributes($routerData)->insert()->getInsertId();
		} else {
			$routerData['url'] = $url;
			$routerData['hash'] = $hashUrl;
			RouterModel::factory ()->where ( 'id', $routerRecord[0]['id'] )->limit ( 1 )->modifyAll($routerData);	
		}
	}

	/**
	 * Create product slug
	 * @param array $category_arr
	 * @param array $pc_arr
	 * @return string
	 */
	protected function createFriendlyUrl($category_arr, $pc_arr, $name = null)
	{
	    $slug = '';
	    if (!empty($category_arr)) {
	        $arr = array();
	        $category_id = max($pc_arr);
	        UtilComponent::getBreadcrumbTree($arr, $category_arr, $category_id);
	        krsort($arr);
	        $arr = array_values($arr);
	        $category_slug = [];
	        foreach ($arr as $category)
	        {
	            $category_slug[] = AppController::friendlyURL($category['data']['name']);
	        }
	        if (!empty($category_slug))
	        {
	            $slug = join("/", $category_slug);
	        }
	    }

	    if (!empty($name)) {
	        if (!empty($slug)) {
	            $slug =  $slug .'/'. UtilComponent::post_slug($name);
	        } else {
				$slug .= UtilComponent::post_slug($name);
			}
	        
	    }
	    
	    return $slug;
	}
	
	
    /**
     * Create dynamic router
     * @param string $url
     * @param int $type
     * @param string $controllerName
     * @param string $action
     * @param int $id
     */
	protected function createOrUpdateDynamicRouter($url, $type, $controllerName, $action, $key, $foreignId, $localeId)
	{
	    $hashUrl = hash('md5', $url);
	    $routerRecordExisted = RouterModel::factory()
	    ->where('t1.hash', $hashUrl)
	    ->findAll()
	    ->first();
	    if ($routerRecordExisted) return;
	    
	    $routerRecord = RouterModel::factory()
    	    ->where('t1.type', $type)
    	    ->where('t1.controller', $controllerName)
    	    ->where('t1.action', $action)
    	    ->where('t1.foreign_id', $foreignId)
    	    ->where('t1.locale_id', $localeId)
    	    ->findAll()
    	    ->first();
    	if (empty($routerRecord)) {
	        $routerData = [
	            'url' => $url,
	            'hash' => $hashUrl,
	            'controller' => $controllerName,
	            'action' => $action,
	            'type' => $type,
	            'foreign_id' => $foreignId,
	            'locale_id' => $localeId
	        ];
	        if (!empty($key)) {
	            $routerData ['params'] = "$key=" . (int)$foreignId;
	        } else {
	            $routerData ['params'] = 'id=' . (int)$foreignId;
	        }
	        RouterModel::factory()->setAttributes($routerData)->insert()->getInsertId();
    	} else {
    	    RouterModel::factory()
    	    ->set('id', $routerRecord['id'])
    	    ->modify([
    	        'url' => $url,
    	        'hash' => $hashUrl
    	    ]);
    	}
	}
	
	/**
	 * Generate ramdom number
	 * @param number $length
	 * @return string
	 */
	protected function generateRamdomNumber($length = 6) {
	    $characters = '0123456789';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}
	
	/**
	 * Delete router
	 * @param int $type
	 * @param string $controllerName
	 * @param string $action
	 * @param int $foreignId
	 */
	protected function deleteRouter($type, $controllerName, $action, $foreignId)
	{
	    RouterModel::factory()
	    ->where('type', $type)
	    ->where('controller', $controllerName)
	    ->where('action', $action)
	    ->where('foreign_id', $foreignId)
	    ->eraseAll();
	}
	
	public function getFrontPageCss() {
	    $theme = $this->getTheme();
	    if (!empty(THEME_PATH_PUBLIC)) {
	        $jsonPath = THEME_PATH_PUBLIC.'/'.$theme.".json";
	        if(file_exists(THEME_PATH_PUBLIC."/config.json")) {
	            $jsonPath = THEME_PATH_PUBLIC."/config.json";
	        }
	        if(file_exists(THEME_PATH_PUBLIC."/configs/config.json")) {
	            $jsonPath = THEME_PATH_PUBLIC."/configs/config.json";
	        }
	    } else {
	        $themeDir =  THEME_PATH;
	        $jsonPath = $themeDir.$theme."/".$theme.".json";
	        if(file_exists($themeDir.$theme."/config.json")) {
	            $jsonPath = $themeDir.$theme."/config.json";
	        }
	    }
	    
	    $string = file_get_contents($jsonPath);
	    $json_arr = json_decode($string, true);
	    if(isset($json_arr['css'])) {
	        return $json_arr['css'];
	    } else {
	        return array();
	    }
	}
	
	public function getFrontPageJs() {
	    $themeDir =  THEME_PATH;
	    $theme = $this->getTheme();
	    if (!empty(THEME_PATH_PUBLIC)) {
	        $jsonPath = THEME_PATH_PUBLIC.'/'.$theme.".json";
	        if(file_exists(THEME_PATH_PUBLIC."/config.json")) {
	            $jsonPath = THEME_PATH_PUBLIC."/config.json";
	        }
	        if(file_exists(THEME_PATH_PUBLIC."/configs/config.json")) {
	            $jsonPath = THEME_PATH_PUBLIC."/configs/config.json";
	        }
	    } else {
	        $themeDir =  THEME_PATH;
	        $jsonPath = $themeDir.$theme."/".$theme.".json";
	        if(file_exists($themeDir.$theme."/config.json")) {
	            $jsonPath = $themeDir.$theme."/config.json";
	        }
	    }
	    $string = file_get_contents($jsonPath);
	    $json_arr = json_decode($string, true);
	    if(isset($json_arr['javascript'])) {
	        return $json_arr['javascript'];
	    } else {
	        return array();
	    }
	}
	
	public function isInvoiceReady()
	{
	    return $this->isAdmin();
	}
	
	
	public function isCountryReady()
	{
	    return $this->isAdmin();
	}
	
	protected function loadDefaultFrontendLang() {
	    if (!isset($_SESSION[FONTEND_TRANS_DICT]['locale_id'])) {
	        $jsonPath = THEME_PATH_PUBLIC.'/configs/config.json';
	        $string = file_get_contents($jsonPath);
	        $json_arr = json_decode($string, true);
	        $defaultLang = $json_arr['default_lang'];
	        $locale = LocaleModel::factory()->where('language_iso', $defaultLang)->findAll()->limit(1)->first();
	        $_SESSION[FONTEND_TRANS_DICT]['locale_id'] = $locale['id'];
	        $_SESSION[FONTEND_TRANS_DICT]['lang'] = $locale['language_iso'];
	    }
	}

}
?>