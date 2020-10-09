<?php
namespace App\Controllers\Components\Widgets;

use Core\Framework\Controller;

abstract class Widget
{
	
	public abstract function init($data);
	public abstract function run($renderString = false);
	private $localeId;
	private $baseUrl;
	public $option_arr = array();
	public $controller;
	protected $data;
	protected $name;
	protected $class;
	
	public function setName($name) {
	    $this->name = $name;
	}
	
	public function setClass($class) {
	    $this->class = $class;
	}
	
	public function render($data=array())
	{

		$themePath = THEME_PATH_PUBLIC;
		if(!empty($data['template']) && is_file($themePath.'/widgets/'.$data['template'])) {
			$template = str_replace('.php', '', $data['template']);
		} elseif (!empty($data['widget_name'])) {
			$template = $data['widget_name'];
		} else {
			$template = get_class($this);
			$part = explode('\\', $template);
			$template = end($part);
		}
		
		unset($data['template']);
		extract($data);
		$_viewFile_ = $themePath.'/widgets/'.$template.'.php';
		require($_viewFile_);
	}

	protected function output($data = [])
	{
		if (!empty($data['template'])) {
		    $template = $data['template'];
		} else {
		    $template = get_class($this);
		    $part = explode('\\', $template);
		    $template = end($part).'.php';
		}
		unset($data['template']);
		extract($data);
		$themePath = THEME_PATH_PUBLIC;
		$_viewFile_ = $themePath.'/widgets/'.$template;
		ob_start();
		require($_viewFile_);
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
	
	public function setLocaleId($localeId) {
		$this->localeId = $localeId;
	}
	public function isAdmin() {
	
		$controller = new Controller();
		return $controller->getRoleId() == 1;
	}
	public function getLocaleId() {
	    if (!empty($_SESSION[FONTEND_TRANS_DICT]['locale_id'])) return $_SESSION[FONTEND_TRANS_DICT]['locale_id'];
		return $this->localeId;
	}
	
	public function getFrontendLocaleId() {
	    if (!empty($_SESSION[FONTEND_TRANS_DICT]['locale_id'])) return $_SESSION[FONTEND_TRANS_DICT]['locale_id'];
	    return $this->localeId;
	}
	
	public function getOption() {
		
	}
	public function generateCDNLink($url)
	{
		if (strpos($url, GOOGLE_STORAGE_DOMAIN) !== false) {
			$url = str_replace(GOOGLE_STORAGE_DOMAIN, CDN_GOOGLE_STORAGE_DOMAIN, $url);
		} elseif (strpos($url, $this->getBaseUrl()) !== false) {
			$url = str_replace($this->getBaseUrl(), CDN_DOMAIN, $url);
		} else {
			$url = CDN_DOMAIN. '/'. $url;
		}

		return $url;
	}
			
	public function setBaseUrl($baseUrl) {
		$this->baseUrl = $baseUrl;
	}
	
	public function getBaseUrl() {
		return $this->baseUrl;
	}

	
	public function getController()
	{
		return $this->controller;
	}
	
}