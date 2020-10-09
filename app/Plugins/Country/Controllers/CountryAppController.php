<?php
namespace App\Plugins\Country\Controllers;

use Core\Framework\Plugin;
use Core\Framework\Registry;

class CountryAppController extends Plugin 
{
	public function __construct() {
		$this->setLayout ( 'Admin' );
	}
	public static function getConst($const) {
		$registry = Registry::getInstance ();
		$store = $registry->get ( 'Country' );
		return isset ( $store [$const] ) ? $store [$const] : NULL;
	}
	public function isCountryReady() {
		$reflector = new \ReflectionClass ( 'Core\\Framework\\Plugin' );
		try {
			$ReflectionMethod = $reflector->getMethod ( 'isCountryReady' );
			return $ReflectionMethod->invoke ( new Plugin (), 'isCountryReady' );
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}
}
?>