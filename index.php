<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
if (!headers_sent())
{
	session_name('FoodDelivery');
	@session_start();
}
if (isset($_GET["reporting"]) && $_GET["reporting"] == '0') 
{
	$_SESSION["error_reporting"] = '0';
} else if (isset($_GET["reporting"]) && $_GET["reporting"]== '1') {
	$_SESSION["error_reporting"] = '1';
}
if (isset($_SESSION["error_reporting"]) && $_SESSION["error_reporting"]=='1')
{
	ini_set("display_errors", "On");
	error_reporting(E_ALL|E_STRICT);
} else {
	error_reporting(0);
}
if (!defined("ROOT_PATH"))
{
	define("ROOT_PATH", dirname(__FILE__) . '/');
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once ROOT_PATH . 'app/Config/options.inc.php';
require_once FRAMEWORK_PATH . 'global.func.php';
use Core\Framework\Observer;
$Observer = Observer::factory();
$Observer->init();
?>