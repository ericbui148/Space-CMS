<?php
use Core\Framework\Registry;

$config = array();
$config['PLUGIN_NAME'] = 'OneAdmin';
$config['PLUGIN_MODEL'] = 'OneAdminModel';
$config['PLUGIN_DIR'] = 'app/Plugins/' . $config['PLUGIN_NAME'] . '/';
$config['PLUGIN_CONTROLLERS_PATH'] = $config['PLUGIN_DIR'] . 'Controllers/';
$config['PLUGIN_MODELS_PATH'] = $config['PLUGIN_DIR'] . 'Models/';
$config['PLUGIN_VIEWS_PATH'] = $config['PLUGIN_DIR'] . 'Views/';
$config['PLUGIN_COMPONENTS_PATH'] = $config['PLUGIN_CONTROLLERS_PATH'] . 'Components/';
$config['PLUGIN_WEB_PATH'] = $config['PLUGIN_DIR'] . 'web/';
$config['PLUGIN_IMG_PATH'] = $config['PLUGIN_WEB_PATH'] . 'img/';
$config['PLUGIN_CSS_PATH'] = $config['PLUGIN_WEB_PATH'] . 'css/';
$config['PLUGIN_JS_PATH'] = $config['PLUGIN_WEB_PATH'] . 'js/';

$config['PLUGIN_ID'] = "105";
$config['PLUGIN_VERSION'] = "1.0";
$config['PLUGIN_BUILD'] = "1.0.6";

$registry = Registry::getInstance();
$registry->set($config['PLUGIN_NAME'], $config);
$plugins = $registry->get('Plugins');
if (is_null($plugins))
{
	$plugins = array();
}
$plugins[$config['PLUGIN_NAME']] = array('OneAdmin');
$registry->set('Plugins', $plugins);

unset($config);
unset($plugins);
?>