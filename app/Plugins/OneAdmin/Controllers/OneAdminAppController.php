<?php
namespace App\Plugins\OneAdmin\Controllers;

use Core\Framework\Plugin;
use Core\Framework\Registry;

class OneAdminAppController extends Plugin {
	public function __construct() {
		$this->setLayout ( 'Admin' );
	}


	public static function getConst($const) {

		$registry = Registry::getInstance ();
		$store = $registry->get ( 'OneAdmin' );
		return isset ( $store [$const] ) ? $store [$const] : NULL;
	}

	public function isOneAdminReady() {

		$reflector = new \ReflectionClass( 'Plugin' );
		try {
			$ReflectionMethod = $reflector->getMethod ( 'isOneAdminReady' );
			return $ReflectionMethod->invoke ( new Plugin (), 'isOneAdminReady' );
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}
}
?>