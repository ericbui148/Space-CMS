<?php
namespace App\Plugins\Log\Controllers;

use Core\Framework\Plugin;
use Core\Framework\Registry;

class LogAppController extends Plugin {
	public function __construct() {
		$this->setLayout ( 'Admin' );
	}
	public static function getConst($const) {
		$registry = Registry::getInstance ();
		$store = $registry->get ( 'Log' );
		return isset ( $store [$const] ) ? $store [$const] : NULL;
	}
}
?>