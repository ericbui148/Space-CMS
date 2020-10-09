<?php
namespace App\Plugins\Installer\Controllers;

use Core\Framework\Registry;
use Core\Framework\Plugin;

class InstallerAppController extends Plugin
{
	public function __construct()
	{
		$this->setLayout('Install');
	}
	
	public static function getConst($const)
	{
		$registry = Registry::getInstance();
		$store = $registry->get('Installer');
		return isset($store[$const]) ? $store[$const] : NULL;
	}
}
?>