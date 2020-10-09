<?php
namespace Core\Framework;

class Router extends \AltoRouter
{
	/**
	  * Create router in one call from config.
	  *
	  * @param array $routes
	  * @param string $basePath
	  * @param array $matchTypes
	  */
	 public function __construct( $routes = array(), $basePath = '', $matchTypes = array() ) 
	 {
		parent::__construct($routes, $basePath, $matchTypes);
	 }

	/**
	 * Match a given Request Url against stored routes
	 * @param string $requestUrl
	 * @param string $requestMethod
	 * @return array|boolean Array with route information on success, false on failure (no match).
	 */
	 public function match($requestUrl = null, $requestMethod = null) 
	 {
		$match = parent::match($requestUrl, $requestMethod);
		if (!$match && isset($_GET['controller'])) {
			$match['target']['controller'] = $_GET['controller'];
			$match['target']['action'] = isset($_GET['action'])? $_GET['action'] : 'Index';
			$match['params'] = [];
		}

		if (empty($match['target']) && !empty($match['params']['controller'])) {
			$match['target']['controller'] = ''.$match['params']['controller'];
			if (!empty($match['params']['action'])) {
				$match['target']['action'] = ''.$match['params']['action'];
			}
			$match['params'] = [];
		}
		return $match;
	 }
}
?>