<?php
namespace Core\Framework;

use Core\Framework\Components\ToolkitComponent;
use Core\Framework\Components\DependencyManagerComponent;

class Controller extends Objects {
	public $tpl = array ();
	private $js = array ();
	private $css = array ();
	public $defaultUser = 'admin_user';
	public $layout = 'Default';
	public $template = NULL;
	public $ajax = FALSE;
	public $params = array ();
	public $body = array ();
	public $query = array ();
	public $theme = NULL;
	public $title = NULL;
	public $og_description = NULL;
	public $og_image = NULL;
	public $meta_description = NULL;
	public $meta_keywords = NULL;
	public $is_front_page = FALSE;
	public function __construct() {
	}

	public function beforeFilter() {
	}

	public function beforeRender() {
	}

	public function afterFilter() {
	}

	public function afterRender() {
	}

	public function appendCss($file, $path = CSS_PATH, $remote = FALSE) {

		$this->css [] = compact ( 'file', 'path', 'remote' );
		return $this;
	}

	public function removeJs($file, $path = JS_PATH, $remote = FALSE) {
		$index = null;
		for ($i = 0; $i < count($this->js); $i++) {
			if ($this->js[$i]['file'] == $file && $this->js[$i]['path'] == $path && $this->js[$i]['remote'] == $remote) {
				unset($this->js[$i]);
				$index = $i;
				break;
			}
		}
		return $index;
	}

	public function removeCss($file, $path = CSS_PATH, $remote = FALSE) {
		$index = null;
		for ($i = 0; $i < count($this->js); $i++) {
			if ($this->js[$i]['file'] == $file && $this->js[$i]['path'] == $path && $this->js[$i]['remote'] == $remote) {
				unset($this->js[$i]);
				$index = $i;
				break;
			}
		}
		return $index;
	}

	public function appendJs($file, $path = JS_PATH, $remote = FALSE) {
		$this->js [] = compact ( 'file', 'path', 'remote' );
		return $this;
	}
	public function appendJsFromPlugin($file, $library, $pluginName, $basePath = THIRD_PARTY_PATH) {
		$dm = new DependencyManagerComponent( $basePath );
		$dependencies = Objects::getConstant ( $pluginName, 'PLUGIN_DIR' ) . 'config/dependencies.php';
		$dm->load ( $dependencies )->resolve ();
		return $this->appendJs ( $file, $dm->getPath ( $library ), FALSE, FALSE );
	}
	public function Index() {
	}
	public function AfterInstall() {
	}
	public function BeforeInstall() {
	}

	public function CheckInstall() {
	}

	public function checkLogin() {

		if (! $this->isLoged ()) {
			ToolkitComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=Login" );
		}
	}
	public function get($key = NULL) {
		if (is_null ( $key )) {
			return $this->tpl;
		}
		if (array_key_exists ( $key, $this->tpl )) {
			return $this->tpl [$key];
		}
		return FALSE;
	}

	public function getAjax() {

		return $this->ajax;
	}

	public function getCss() {

		return $this->css;
	}

	public function getJs() {
		return $this->js;
	}

	public function getLayout() {

		return $this->layout;
	}

	public function getParams() {

		return $this->params;
	}

	public function getUserId() {

		return isset ( $_SESSION [$this->defaultUser] ) && array_key_exists ( 'id', $_SESSION [$this->defaultUser] ) ? $_SESSION [$this->defaultUser] ['id'] : FALSE;
	}

	public function getRoleId() {

		return isset ( $_SESSION [$this->defaultUser] ) && array_key_exists ( 'role_id', $_SESSION [$this->defaultUser] ) ? $_SESSION [$this->defaultUser] ['role_id'] : FALSE;
	}

	public function isLoged() {
		if (isset ( $_SESSION [$this->defaultUser] ) && count ( $_SESSION [$this->defaultUser] ) > 0) {
			return TRUE;
		}
		return FALSE;
	}

	public function isAdmin() {

		return $this->getRoleId () == 1;
	}

	public function isXHR() {

		return @$_SERVER ['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}

	public function log($value) {
		$this->requestAction ( array (
				'controller' => 'Log',
				'action' => 'Logger',
				'params' => array (
						'value' => $value,
						'key' => md5 ( @$this->option_arr ['private_key'] . SALT ) 
				) 
		), array (
				'return' 
		) );
	}

	public function resetJs() {

		$this->js = array ();
		return $this;
	}

	public function resetCss() {

		$this->css = array ();
		return $this;
	}
	protected function exec_bg($cmd) {
		if (substr(php_uname(), 0, 7) == "Windows"){
			pclose(popen("start /B ". $cmd, "r"));
		}
		else {
			exec($cmd . " > /dev/null &");
		}
	}
	public function requestAction($request, $options = array()) {
		$Dispatcher = new Dispatcher();
		$dispatch = $Dispatcher->dispatch ( $request, $options );
		$v529 = in_array ( version_compare ( phpversion (), '5.2.9' ), array (
				0,
				1 
		) );
		$css = array_merge ( $this->getCss (), $Dispatcher->getController ()->getCss () );
		if ($v529) {
			$unique = array_unique ( $css, SORT_REGULAR );
		} else {
			$unique = array_unique ( $css );
		}
		$unique = array_map ( 'unserialize', array_unique ( array_map ( 'serialize', $css ) ) );
		$this->setCss ( $unique );
		$js = array_merge ( $this->getJs (), $Dispatcher->getController ()->getJs () );
		if ($v529) {
			$unique = array_unique ( $js, SORT_REGULAR );
		} else {
			$unique = array_unique ( $js );
		}
		$unique = array_map ( 'unserialize', array_unique ( array_map ( 'serialize', $js ) ) );
		$this->setJs ( $unique );
		return $dispatch;
	}
	public function set($key, $value) {
		$this->tpl [$key] = $value;
		return $this;
	}
	
	public function setAjax($value) {
	
		$this->ajax = ( bool ) $value;
		return $this;
	}

	public function setCss($value) {

		if (is_array ( $value )) {
			$this->css = $value;
		}
		return $this;
	}

	public function setJs($value) {

		if (is_array ( $value )) {
			$this->js = $value;
		}
		return $this;
	}

	public function setLayout($str) {

		$this->layout = $str;
		return $this;
	}

	public function setTemplate($controller, $template) {
		$this->template = compact ( 'controller', 'template' );
		return $this;
	}
	
	public  function  baseUrl(){
		return BASE_URL;
	}
	
		
}
?>