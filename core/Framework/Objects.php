<?php
namespace Core\Framework;

class Objects {
	const FRAMEWORK_VERSION = '1.0';
	const FRAMEWORK_BUILD = '1.0.23';
	public static function escapeString($value) {
		$registry = Registry::getInstance ();
		if ($registry->is ( 'dbo' )) {
			$dbo = $registry->get ( 'dbo' );
			if (is_object ( $dbo ) && method_exists ( $dbo, 'escapeString' )) {
				return $dbo->escapeString ( $value );
			}
		}
		$driver = function_exists ( 'mysqli_connect' ) ? 'MysqliDriver' : 'MysqlDriver';
		
		$params = array (
				'hostname' => HOST,
				'username' => USER,
				'password' => PASS,
				'database' => DB 
		);
		if (strpos ( $params ['hostname'], ":" ) !== FALSE) {
			list ( $hostname, $value ) = explode ( ":", $params ['hostname'] );
			if (preg_match ( '/\D/', $value )) {
				$params ['socket'] = $value;
			} else {
				$params ['port'] = $value;
			}
			$params ['hostname'] = $hostname;
		}
		$dbo = Singleton::getInstance ( $driver, $params );
		if (! $dbo->init ()) {
			return $value;
		}
		return $dbo->escapeString ( $value );
	}
	public static function import($type, $name) {
		$type = strtolower ( $type );
		if (! in_array ( $type, array (
				'model',
				'component',
				'plugin' 
		) )) {
			return false;
		}
		switch ($type) {
			case 'model' :
			case 'component' :
				break;
			case 'plugin' :
				if (is_array ( $name )) {
					foreach ( $name as $n ) {
						$configFile = PLUGINS_PATH . $n . '/Config/config.inc.php';
						if (is_file ( $configFile )) {
							require_once $configFile;
						}
					}
				} else {
					$configFile = PLUGINS_PATH . $name . '/Config/config.inc.php';
					if (is_file ( $configFile )) {
						require_once $configFile;
					}
				}
				break;
		}
		return;
	}
	public static function getPlugin($name) {
		$registry = Registry::getInstance ();
		if (null !== $registry->get ( $name )) {
			return $name;
		}
		$plugins = $registry->get ( 'plugins' );
		if (is_array ( $plugins )) {
			foreach ( $plugins as $plugin => $controllers ) {
				if (in_array ( $name, $controllers )) {
					return $plugin;
				}
			}
		}
		return null;
	}
	public static function getConstant($plugin, $const) {
		$registry = Registry::getInstance ();
		$config = $registry->get ( $plugin );
		return isset ( $config [$const] ) ? $config [$const] : NULL;
	}
	public static function getFrameworkVersion() {
		return self::FRAMEWORK_VERSION;
	}
	public static function getFrameworkBuild() {
		return self::FRAMEWORK_BUILD;
	}
}
?>