<?php
namespace Core\Framework;

use Core\Framework\Components\ToolkitComponent;
use App\Models\RouterModel;

/**
 * PHP Framework
 *
 * @copyright Copyright 2013, StivaSoft, Ltd. (http://stivasoft.com)
 * @link      http://www.phabbers.com/
 * @package   framework
 * @version   1.0.11
 */
/**
 * Used to invoke the Dispatcher
 *
 * @package framework
 * @since 1.0.0
 */
class Observer
{
/**
 * @var object
 * @access private
 */
	private $controller;
/**
 * The Factory pattern allows for the instantiation of objects at runtime.
 *
 * @param array Array with parameters passed to class constructor.
 * @access public
 * @static
 * @return self Instance of a `Observer`
 */
	public static function factory($attr=array())
	{
		return new Observer($attr);
	}
/**
 * Initialize
 *
 * @access public
 * @return void
 */
	public function init()
	{
		$match = $this->getDynamicRouter();
		
		if (empty($match['target'])) {
			$routerConfig = include(ROOT_PATH . 'app/Config/routers.php');
			$router = new Router($routerConfig);
			$router->setBasePath(INSTALL_FOLDER);
			$match = $router->match();
		}

		$target = $match['target'];
		$params = $match['params'];
		if (empty($target)) {
			ToolkitComponent::redirect('/404');
		}
		$Dispatcher = new Dispatcher();
		$Dispatcher->dispatch($target, $params);
		$this->controller = $Dispatcher->getController();
	}
	
	protected function getDefaultRouter()
	{
		$requestUri = str_replace(INSTALL_FOLDER, '', $_SERVER['REQUEST_URI']);
		$uris = explode('/', $requestUri);
		$router = [
			'target' => [],
			'params' => []
		];
		if (!empty($uris[0]) && (bool)preg_grep('Admin.*', $uris[0])) {
			$router['target']['controller'] = $uris[0];
			if (!empty($uris[1])) {
				$router['target']['action'] = $uris[1];
			}
			$count = count($uris);
			if ($count > 2) {
				for ($i = 2; $i < $count; ){
					if (isset($uris[$i+1])) {
						$router['target']['params'][$uris[$i]] = $uris[$i+1];
					}
					$i = $i + 2;
				}
			}
		}
	
		
		return $router;
	}
	/**
	 * Gets the controller object
	 *
	 * @access public
	 * @return object Instance of a requested controller
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Get dynamic router
	 */
	protected function getDynamicRouter()
	{
	    $requestUri = $_SERVER['REQUEST_URI'];
	    if (INSTALL_FOLDER != "/") {
	        $requestUri = str_replace(INSTALL_FOLDER, '', $_SERVER['REQUEST_URI']);
	    }
		$uris = explode('?', $requestUri);
		$uri = substr($uris[0], 1);
		if (empty($uri)) {
			return null;
		}
		$hash = hash('md5', $uri);
		$router = RouterModel::factory()->where('t1.hash', $hash)->orderBy('t1.id desc')->findAll()->first();
		if (empty($router)) {
			return null;
		}
		if (!empty($uris[1])) {
		    $router['params'] .= $uris[1];
		}
		
		$params = $this->parseParams($router['params']);
		return [
			'target' => [
				'controller' => $router['controller'],
				'action' => $router['action']
			],
			'params' => $params
		];
	}

	/**
	 * Parse param string
	 */
	protected function parseParams($paramString)
	{
		$queries= explode("&", $paramString);
		$params = [];
		foreach ($queries as $value) {
			$p = explode("=", $value);
			$params[$p[0]] = isset($p[1])? $p[1] : null;
		}

		return $params;
	}
}
?>