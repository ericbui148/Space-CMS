<?php
namespace App\Plugins\Locale\Controllers;

use Core\Framework\Registry;
use Core\Framework\Plugin;

class LocaleAppController extends Plugin {
	public function __construct() {
		$this->setLayout ( 'Admin' );
	}
	public static function getConst($const) {
		$registry = Registry::getInstance ();
		$store = $registry->get ( 'Locale' );
		return isset ( $store [$const] ) ? $store [$const] : NULL;
	}
}
?>