<?php
namespace Core\Framework;

use Core\Framework\Components\ToolkitComponent;

class Dispatcher extends Objects 
{
	private $controller;
	public $viewPath;
	public $templateName;
	public function __construct() {
	}
	public function dispatch(&$request, $options) {
		$request = Dispatcher::sanitizeRequest ( $request );
		$controller = $this->createController ( $request );
		if ($controller !== false) {
			if (is_object ( $controller )) {
				$controller->request = new Request();
				$controller->request->controller = $request['controller'];
				$controller->request->action = $request['action'];
				$controller->request->params = array_merge($options, $_GET);
				$data = $_REQUEST;
				unset($data['controller']);
				unset($data['action']);
				$controller->request->data = $data;
				$controller->request->server = $_SERVER;
				$controller->request->env = $_ENV;
				
				$controller->request->query = $this->parse_querystring();
				
				$this->controller = & $controller;
				$tpl = &$controller->tpl;
				$controller->body = $_POST;
				$controller->query = $_GET;
				if (isset ( $request ['action'] )) {
					$action = $request ['action'];
					if (method_exists ( $controller, $action )) {
						$controller->beforeFilter ();
						if (isset ( $request ['params'] )) {
							$controller->params = $request ['params'];
						}
						$result = $controller->$action ();
						$controller->afterFilter ();
						$controller->beforeRender ();
						$template = $action;
						$content_tpl = $this->getTemplate ( $request );
						
					} else {
						printf ( 'Method %s::%s didn\'t exists', $request ['controller'], $request ['action'] );
						exit ();
					}
				} else {
					$request ['action'] = 'Index';
					$controller->beforeFilter ();
					$controller->Index ();
					$controller->afterFilter ();
					$controller->beforeRender ();
					$content_tpl = $this->getTemplate ( $request );
				}
				if (in_array ( 'return', $options )) {
					return $result;
				} elseif (in_array ( 'output', $options )) {
					return $tpl;
				} else {
					if (! is_file ( $content_tpl )) {
					    var_dump($content_tpl);
						echo 'template not found';
						exit ();
					}
					if ($controller->getAjax ()) {
						require $content_tpl;
						$controller->afterRender ();
					} else {
						$layoutFile = VIEWS_PATH . 'Layouts/' . $controller->getLayout () . '.php';
						
						if(!is_null($this->controller->theme)) {
							if (!empty(THEME_PATH_PUBLIC)) {
								$layoutName = str_replace('', '', $controller->getLayout ());
								$layoutName = str_replace('.php', '', $layoutName);
								if(file_exists(THEME_PATH_PUBLIC.'/layouts/' . $layoutName . '.php')) {
									$layoutFile = THEME_PATH_PUBLIC.'/layouts/' . $layoutName . '.php';
								} else {
									$layoutFile = THEME_PATH_PUBLIC.'/Views/Layouts/' . $controller->getLayout () . '.php';
								}	
												
							} else {
								$layoutFile = THEME_PATH . $this->controller->theme.'/Views/Layouts/' . $controller->getLayout () . '.php';
							}

						}
						if (is_file ( $layoutFile )) {
							require $layoutFile;
						} else {
							if (null !== ($plugin = Objects::getPlugin ( $request ['controller'] ))) {
								$layoutFile = Objects::getConstant ( $plugin, 'PLUGIN_VIEWS_PATH' ) . 'Layouts/' . $controller->getLayout () . '.php';
								if (is_file ( $layoutFile )) {
									require $layoutFile;
								}
							}
						}
						$controller->afterRender ();
					}
				}
			} else {
				echo 'controller not is object';
				exit ();
			}
		} else {
			echo 'cla' . 'ss didn\'t exists';
			exit ();
		}
	}
	protected function parse_querystring()
	{
		$queries= explode("&", $_SERVER['REQUEST_URI']);
		$params = [];
		foreach ($queries as $value) {
			$p = explode("=", $value);
			$params[$p[0]] = isset($p[1])? $p[1] : null;
		}

		return $params;
	}
	public function loadController($request) {

		$request = Dispatcher::sanitizeRequest ( $request );
		$this->viewPath = VIEWS_PATH . $request ['controller'] . '/';
		if (null !== ($plugin = Objects::getPlugin ( $request ['controller'] ))) {
			$this->viewPath = PLUGINS_PATH . $plugin . '/Views/' . $request ['controller'] . '/';
		}
		return $this;
	}

	public function createController($request) {

		$request = Dispatcher::sanitizeRequest ( $request );
		$this->loadController ( $request );
		$controllerClass = ToolkitComponent::getFullControllerClassName($request ['controller'].'Controller');
		if (class_exists ($controllerClass)) {
			return new $controllerClass();
		}
		return false;
	}

	public function getController() {
		return $this->controller;
	}

	public function getTemplate($request) {
		$request = Dispatcher::sanitizeRequest ( $request );
		if (! is_null ( $this->controller->template )) {
			
			if (!empty(THEME_PATH_PUBLIC)) {
				$templateView = THEME_PATH_PUBLIC.'/templates/'.$this->controller->template ['template'];
				if (file_exists($templateView)) {
					return $templateView;
				}
			}
			if (! strpos ( $this->controller->template ['template'], ":" )) {
				return VIEWS_PATH . $this->controller->template ['controller'] . '/' . $this->controller->template ['template'] . '.php';
			} else {
				list ( $pluginController, $view ) = explode ( ":", $this->controller->template ['template'] );
				return Objects::getConstant ( $this->controller->template ['controller'], 'PLUGIN_VIEWS_PATH' ) . '/' . $pluginController . '/' . $view . '.php';
			}
		} else {
			if(!is_null($this->controller->theme)) {
				if (!empty(THEME_PATH_PUBLIC)) {
					
					$action = str_replace('', '', $request['action']);
					$templateView = THEME_PATH_PUBLIC.'/templates/'. $action . '.php';
					if (file_exists($templateView)) {
						return $templateView;
					}
					return THEME_PATH_PUBLIC.'/Views/'.$request ['controller'].'/'.$request ['action'] . '.php';
				} else {
					return THEME_PATH.$this->controller->theme.'/Views/'.$request ['controller'].'/'.$request ['action'] . '.php';
				}
				
			}
			return $this->viewPath . $request ['action'] . '.php';
		}
	}

	private static function sanitizeRequest($request) {

		if (isset ( $request ['controller'] )) {
			$request ['controller'] = basename ( $request ['controller'] );
		}
		if (isset ( $request ['action'] )) {
			$request ['action'] = basename ( $request ['action'] );
		}
		return $request;
	}
}
?>