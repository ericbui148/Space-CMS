<?php
$stop = false;
if (isset($_GET['controller']) && $_GET['controller'] == 'Installer')
{
	$stop = true;
	if (isset($_GET['install']))
	{
		switch ($_GET['install'])
		{
			case 1:
				$stop = true;
				break;
			default:
				$stop = false;
				break;
		}
	}
	if (in_array($_GET['action'], array('License', 'Hash')) || strpos($_GET['action'], 'Secure') === 0)
	{
		$stop = false;
	}
}

if (!$stop)
{
	
	require ROOT_PATH . 'app/Config/config.inc.php';
	if (!defined("HOST") || preg_match('/\[hostname\]/', HOST))
	{
		header("Location: index.php?controller=Installer&action=Step1&install=1");
		exit;
	}
}

if (!defined("APP_PATH")) define("APP_PATH", ROOT_PATH . "app/");
if (!defined("CORE_PATH")) define("CORE_PATH", ROOT_PATH . "core/");
if (!defined("LIBS_PATH")) define("LIBS_PATH", "app/web/libs/");
if (!defined("THIRD_PARTY_PATH")) define("THIRD_PARTY_PATH", "app/web/third-party/");
if (!defined("FRAMEWORK_PATH")) define("FRAMEWORK_PATH", CORE_PATH . "Framework/");
if (!defined("FRAMEWORK_LIBS_PATH")) define("FRAMEWORK_LIBS_PATH", "app/web/libs/framework/libs/wp");
if (!defined("CONFIG_PATH")) define("CONFIG_PATH", APP_PATH . "Config/");
if (!defined("CONTROLLERS_PATH")) define("CONTROLLERS_PATH", APP_PATH . "Controllers/");
if (!defined("COMPONENTS_PATH")) define("COMPONENTS_PATH", APP_PATH . "Controllers/Components/");
if (!defined("MODELS_PATH")) define("MODELS_PATH", APP_PATH . "Models/");
if (!defined("PLUGINS_PATH")) define("PLUGINS_PATH", APP_PATH . "Plugins/");
if (!defined("VIEWS_PATH")) define("VIEWS_PATH", APP_PATH . "Views/");
if (!defined("THEME_PATH")) define("THEME_PATH", APP_PATH . "themes/");
if (!defined("WEB_PATH")) define("WEB_PATH", APP_PATH . "web/");
if (!defined("CSS_PATH")) define("CSS_PATH", "app/web/css/");
if (!defined("IMG_PATH")) define("IMG_PATH", "app/web/img/");
if (!defined("JS_PATH")) define("JS_PATH", "app/web/js/");
if (!defined("FONTEND_TRANS_DICT")) define("FONTEND_TRANS_DICT", "FONTEND_TRANS_DICT");

if (!defined("UPLOAD_PATH")) define("UPLOAD_PATH", "app/web/upload/");
if (!defined("GALLERY_UPLOAD_PATH")) define("GALLERY_UPLOAD_PATH", "app/web/upload/cms/galleries/");
if (!defined("SLIDER_UPLOAD_PATH")) define("SLIDER_UPLOAD_PATH", "app/web/upload/cms/sliders/");
if (!defined("ARTICLE_AVATAR_UPLOAD_PATH")) define("ARTICLE_AVATAR_UPLOAD_PATH", "app/web/upload/cms/articles/avatars/");
if (!defined("ARTICLE_CONTENT_UPLOAD_PATH")) define("ARTICLE_CONTENT_UPLOAD_PATH", "app/web/upload/cms/articles/contents/");
if (!defined("ARTICLE_CATEGORY_AVATAR_UPLOAD_PATH")) define("ARTICLE_CATEGORY_AVATAR_UPLOAD_PATH", "app/web/upload/cms/article_categories/avatars/");

if (!defined("PAGE_AVATAR_UPLOAD_PATH")) define("PAGE_AVATAR_UPLOAD_PATH", "app/web/upload/cms/pages/avatars/");
if (!defined("PAGE_CONTENT_UPLOAD_PATH")) define("PAGE_CONTENT_UPLOAD_PATH", "app/web/upload/cms/pages/contents/");
if (!defined("PAGE_CATEGORY_AVATAR_UPLOAD_PATH")) define("PAGE_CATEGORY_AVATAR_UPLOAD_PATH", "app/web/upload/cms/page_categories/avatars/");
if (!defined("PRODUCT_UPLOAD_PATH")) define("PRODUCT_UPLOAD_PATH", "app/web/upload/products/product_images/");
if (!defined("PRODUCT_CATEGORY_UPLOAD_PATH")) define("PRODUCT_CATEGORY_UPLOAD_PATH", "app/web/upload/products/categories/");

if (!defined("WIDGET_UPLOAD_PATH")) define("WIDGET_UPLOAD_PATH", "app/web/upload/cms/widgets/");
if (!defined("LOGO_UPLOAD_PATH")) define("LOGO_UPLOAD_PATH", "app/web/upload/cms/logos/");

if (!defined("FORUM_QUESTION_UPLOAD_PATH")) define("FORUM_QUESTION_UPLOAD_PATH", "app/web/upload/forums/questions/");
if (!defined("FORUM_AVATAR_UPLOAD_PATH")) define("FORUM_AVATAR_UPLOAD_PATH", "app/web/upload/forums/avatars/");
if (!defined("FORUM_FILE_UPLOAD_PATH")) define("FORUM_FILE_UPLOAD_PATH", "app/web/upload/forums/files/");
if (!defined("BANNER_UPLOAD_PATH")) define("BANNER_UPLOAD_PATH", "app/web/upload/cms/banners/");
if (!defined("FORUM_REPLY_CONTENT_UPLOAD_PATH")) define("FORUM_REPLY_CONTENT_UPLOAD_PATH", "app/web/upload/forums/replies/");

if (!defined("IMAGE_USE_THUMBNAIL")) define("IMAGE_USE_THUMBNAIL", true);
if (!defined("GALLERY_SMALL")) define("GALLERY_SMALL", "80,80");
if (!defined("GALLERY_MEDIUM")) define("GALLERY_MEDIUM", "800");
if (!defined("GALLERY_FILL_COLOR")) define("GALLERY_FILL_COLOR", "255,255,255");
if (!defined("GALLERY_CROP")) define("GALLERY_CROP", true);

if (!defined("FORUM_IMAGE_SIZE")) define("FORUM_IMAGE_SIZE", "800xauto");
if (!defined("INVOICE_PLUGIN")) define("INVOICE_PLUGIN", 'index.php?controller=AdminOrders&action=Update&uuid={ORDER_ID}');
if (!defined("SCRIPT_VERSION")) define("SCRIPT_VERSION", "2.1");
if (!defined("SCRIPT_ID")) define("SCRIPT_ID", "115");
if (!defined("SCRIPT_BUILD")) define("SCRIPT_BUILD", "2.1.1");
if (!defined("SCRIPT_PREFIX")) define("SCRIPT_PREFIX", "");
if (!defined("TEST_MODE")) define("TEST_MODE", false);
if (!defined("DISABLE_MYSQL_CHECK")) define("DISABLE_MYSQL_CHECK", false);
if (!defined("RSA_MODULO")) define("RSA_MODULO", '1481520313354086969195005236818182195268088406845365735502215319550493699869327120616729967038217547');
if (!defined("RSA_PRIVATE")) define("RSA_PRIVATE", '7');
if (!defined("NUMBER_SECTION_DASHBOARD")) define("NUMBER_RECORD_DASHBOARD", 10);
if (!defined("NUMBER_RECORD_PER_PAGE")) define("NUMBER_RECORD_PER_PAGE", 10);

// BDS module constants
require_once ROOT_PATH.'vendor/autoload.php';
$CONFIG = array();
$CONFIG['plugins'] = array('Locale', 'Backup', 'Log', 'Installer', 'OneAdmin', 'Country', 'Paypal', 'Authorize', 'Sms', 'Gallery', 'Invoice');
foreach ($CONFIG['plugins'] as $plugin) {
	$optionContantPath2 = PLUGINS_PATH . $plugin . '/Config/config.inc.php';
	if (file_exists($optionContantPath2)) {
		require_once $optionContantPath2;
	}
	$optionContantPath = PLUGINS_PATH . $plugin . '/Config/options.inc.php';
	if (file_exists($optionContantPath)) {
		require_once $optionContantPath;
	}
}
?>