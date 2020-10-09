<?php
namespace App\Plugins\Installer\Controllers;

use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\ValidationComponent;
use Core\Framework\Components\SanitizeComponent;
use Core\Framework\Objects;
use Core\Framework\Singleton;
use Core\Framework\Components\HttpComponent;
use App\Controllers\AppController;
use App\Models\OptionModel;
use Core\Framework\Components\ToolkitComponent;
use App\Models\UserModel;
use Core\Framework\Registry;
use Core\Framework\Components\ServicesJSONComponent;
use App\Models\AppModel;

class InstallerController extends InstallerAppController
{
	public $defaultInstaller = 'Installer';
	
	public $defaultErrors = 'Errors';
	
	public function beforeFilter()
	{
		$this->appendJs('jquery-1.8.3.min.js', $this->getConst('PLUGIN_LIBS_PATH'));
		$this->appendCss('admin.css');
		$this->appendCss('install.css', $this->getConst('PLUGIN_CSS_PATH'));
		$this->appendCss('-button.css', FRAMEWORK_LIBS_PATH . '/css/');
		$this->appendCss('-form.css', FRAMEWORK_LIBS_PATH . '/css/');
	}

	private static function ImportSQL($dbo, $file, $prefix, $scriptPrefix=NULL)
	{
		if (!is_object($dbo))
		{
			return FALSE;
		}
		ob_start();
		readfile($file);
		$string = ob_get_contents();
		ob_end_clean();
		if ($string !== false)
		{
			$string = preg_replace(
				array('/(INSERT\s+INTO|INSERT\s+IGNORE\s+INTO|DROP\s+TABLE|DROP\s+TABLE\s+IF\s+EXISTS|DROP\s+VIEW|DROP\s+VIEW\s+IF\s+EXISTS|CREATE\s+TABLE|CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS|UPDATE|UPDATE\s+IGNORE|FROM|ALTER\s+TABLE|ALTER\s+IGNORE\s+TABLE|DELETE\s+(?:(?:LOW_PRIORITY\s+)?(?:QUICK\s+)?(?:IGNORE\s+)?){2}?FROM)\s+`\b(.*)\b`/'),
				array("\${1} `".$prefix.$scriptPrefix."\${2}`"),
				$string);
			
			$arr = preg_split('/;(\s+)?\n/', $string);
			
			$dbo->query("START TRANSACTION;");
			foreach ($arr as $statement)
			{
				$statement = trim($statement);
				if (!empty($statement))
				{
					if (!$dbo->query($statement))
					{
						$error = $dbo->error();
						$dbo->query("ROLLBACK");
						return $error;
					}
				}
			}
			$dbo->query("COMMIT;");
			
			return TRUE;
		}
		return FALSE;
	}

	private static function GetPaths()
	{
		$absolutepath = str_replace("\\", "/", dirname(realpath(basename(getenv("SCRIPT_NAME")))));
		$localpath = str_replace("\\", "/", dirname(getenv("SCRIPT_NAME")));
		
		$localpath = str_replace("\\", "/", $localpath);
		$localpath = preg_replace('/^\//', '', $localpath, 1) . '/';
		$localpath = !in_array($localpath, array('/', '\\')) ? $localpath : NULL;

		return array(
			'install_folder' => '/' . $localpath,
			'install_path' => $absolutepath . '/',
			'BASE_URL' => 'http://' . $_SERVER['SERVER_NAME'] . '/' . $localpath
		);
	}

	public function Index()
	{
		UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step0&install=1");
	}
	
	private static function CheckConfig($redirect=true)
	{
		$filename = 'app/config/config.inc.php';
		$content = @file_get_contents($filename);
		if (strpos($content, 'HOST') === false && strpos($content, 'BASE_URL') === false)
		{
			//Continue with installation
			return true;
		} else {
			if ($redirect)
			{
				UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step0&install=1");
			}
			return false;
		}
	}
	
	private function CheckSession()
	{
		if (!isset($_SESSION[$this->defaultInstaller]))
		{
			UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step1&install=1");
		}
	}
	
	private function CheckTables(&$dbo)
	{
		if (!is_object($dbo))
		{
			return FALSE;
		}
		ob_start();
		readfile('app/config/database.sql');
		$string = ob_get_contents();
		ob_end_clean();

		preg_match_all('/DROP\s+TABLE(\s+IF\s+EXISTS)?\s+`(\w+)`/i', $string, $match);
		if (count($match[0]) > 0)
		{
			$arr = array();
			foreach ($match[2] as $k => $table)
			{
				$result = $dbo->query(sprintf("SHOW TABLES FROM `%s` LIKE '%s'",
					$_SESSION[$this->defaultInstaller]['database'],
					$_SESSION[$this->defaultInstaller]['prefix'] . $table
				));
				if ($result !== FALSE && $dbo->numRows() > 0)
				{
					$row = $dbo->fetchAssoc()->getData();
					$row = array_values($row);
					$arr[] = $row[0];
				}
			}
			return count($arr) === 0;
		}
		return TRUE;
	}
	
	private function CheckVars()
	{
		return isset(
			$_GET['install'],
			$_SESSION[$this->defaultInstaller],
			$_SESSION[$this->defaultInstaller]['hostname'],
			$_SESSION[$this->defaultInstaller]['username'],
			$_SESSION[$this->defaultInstaller]['password'],
			$_SESSION[$this->defaultInstaller]['database'],
			$_SESSION[$this->defaultInstaller]['prefix'],
			$_SESSION[$this->defaultInstaller]['admin_email'],
			$_SESSION[$this->defaultInstaller]['admin_password'],
			$_SESSION[$this->defaultInstaller]['private_key'],
			$_SESSION[$this->defaultInstaller]['install_folder'],
			$_SESSION[$this->defaultInstaller]['install_path'],
			$_SESSION[$this->defaultInstaller]['BASE_URL'],
			$_SESSION[$this->defaultInstaller]['license_key']
		);
	}
	
	public function Step0()
	{
		if (self::CheckConfig(false))
		{
			UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step1&install=1");
		}
	}
	
	public function Step1()
	{
		self::CheckConfig();
		
		if (!isset($_SESSION[$this->defaultInstaller]))
		{
			$_SESSION[$this->defaultInstaller] = array();
		}
		if (!isset($_SESSION[$this->defaultErrors]))
		{
			$_SESSION[$this->defaultErrors] = array();
		}
		
		# PHP Session check -------------------
		if (!headers_sent())
		{
			@session_start();
			$_SESSION['SESSION_CHECK'] = 1;
			@session_write_close();
			
			$_SESSION = array();
			@session_start();
			
			$session_check = isset($_SESSION['SESSION_CHECK']);
			$this->set('session_check', $session_check);
			if ($session_check)
			{
				$_SESSION['SESSION_CHECK'] = NULL;
				unset($_SESSION['SESSION_CHECK']);
			}
		}
		
		ob_start();
		phpinfo(INFO_MODULES);
		$content = ob_get_contents();
		ob_end_clean();
		
		# MySQL version -------------------
		if (!DISABLE_MYSQL_CHECK)
		{
			$drivers = array('mysql', 'mysqli');
			$mysql_version = NULL;
			foreach ($drivers as $driver)
			{
				$mysql_content = explode('name="module_'.$driver.'"', $content);
				if (count($mysql_content) > 1)
				{
					$mysql_content = explode("Client API", $mysql_content[1]);
					if (count($mysql_content) > 1)
					{
						preg_match('/<td class="v">(.*)<\/td>/', $mysql_content[1], $m);
						if (count($m) > 0)
						{
							$mysql_version = trim($m[1]);
							
							if (preg_match('/(\d+\.\d+\.\d+)/', $mysql_version, $m))
							{
								$mysql_version = $m[1];
							}
						}
					}
				}
			
				$mysql_check = true;
				if (is_null($mysql_version) || version_compare($mysql_version, '5.0.0', '<'))
				{
					$mysql_check = false;
				}
			}
			$this->set('mysql_check', $mysql_check);
		}
		
		# PHP version -------------------
		$php_check = true;
		if (version_compare(phpversion(), '5.1.0', '<'))
		{
			$php_check = false;
		}
		$this->set('php_check', $php_check);

		# File permissions
		$filename = 'app/config/config.inc.php';
		$err_arr = array();
		if (!is_writable($filename))
		{
		    $err_arr[] = sprintf('%1$s \'<span class="bold">%2$s</span>\' is not writable. %3$s \'<span class="bold">%2$s</span>\'', 'File', $filename, 'You need to set write permissions (chmod 777) to options file located at');
		}

		# Folder permissions
		$folders = array();
		foreach ($folders as $dir)
		{
			if (!is_writable($dir))
			{
				$err_arr[] = sprintf('%1$s \'<span class="bold">%2$s</span>\' is not writable. %3$s \'<span class="bold">%2$s</span>\'', 'Folder', $dir, 'You need to set write permissions (chmod 777) to directory located at');
			}
		}
		
		# Script (file/folder) permissions
		$result = $this->requestAction(array(
			'controller' => 'AppController',
			'action' => 'CheckInstall'
		), array('return'));
		
		if ($result !== NULL && isset($result['status'], $result['info']) && $result['status'] == 'ERR')
		{
			$err_arr = array_merge($err_arr, $result['info']);
		}
		
		# Plugin (file/folder) permissions
		$filename = 'app/config/options.inc.php';
		$options = @file_get_contents($filename);
		if ($options !== FALSE)
		{
			preg_match('/\$CONFIG\s*\[\s*[\'\"]plugins[\'\"]\s*\](.*);/sxU', $options, $match);
			if (!empty($match))
			{
				eval($match[0]);
			
				if (isset($CONFIG['plugins']))
				{
					if (!is_array($CONFIG['plugins']))
					{
						$CONFIG['plugins'] = array($CONFIG['plugins']);
					}
					foreach ($CONFIG['plugins'] as $plugin)
					{
						$result = $this->requestAction(array(
							'controller' => $plugin,
							'action' => 'CheckInstall'
						), array('return'));

						if ($result !== NULL && isset($result['status'], $result['info']) && $result['status'] == 'ERR')
						{
							$err_arr = array_merge($err_arr, $result['info']);
						}
					}
				}
			}
		}

		$this->set('folder_check', count($err_arr) === 0);
		$this->set('folder_arr', $err_arr);
			
		$this->appendJs('jquery.validate.min.js', $this->getConst('PLUGIN_LIBS_PATH'));
		$this->appendJs('Installer.js', $this->getConst('PLUGIN_JS_PATH'));
	}

	public function Step2()
	{
		self::CheckConfig();
		
		$this->CheckSession();
		
		if (isset($_POST['step1']))
		{
			$_SESSION[$this->defaultInstaller] = array_merge($_SESSION[$this->defaultInstaller], $_POST);
		}
		
		if (!isset($_SESSION[$this->defaultInstaller]['step1']))
		{
			UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step1&install=1");
		}
		
		$this->appendJs('jquery.validate.min.js', $this->getConst('PLUGIN_LIBS_PATH'));
		$this->appendJs('Installer.js', $this->getConst('PLUGIN_JS_PATH'));
	}
	
	public function Step3()
	{
		self::CheckConfig();
		
		$this->CheckSession();
		
		if (isset($_POST['step2']))
		{
			$_POST = array_map('trim', $_POST);
			$_POST = SanitizeComponent::clean($_POST, array('encode' => false));
			$_SESSION[$this->defaultInstaller] = array_merge($_SESSION[$this->defaultInstaller], $_POST);
			
			$err = NULL;
			
			if (!isset($_POST['hostname']) || !isset($_POST['username']) || !isset($_POST['database']) ||
				!ValidationComponent::NotEmpty($_POST['hostname']) ||
				!ValidationComponent::NotEmpty($_POST['username']) ||
				!ValidationComponent::NotEmpty($_POST['database']))
			{
				$err = "Hostname, Username and Database are required and can't be empty.";
			} else {
				
				$driver = function_exists('mysqli_connect') ? 'MysqliDriver' : 'MysqlDriver';
				$params = array(
					'hostname' => $_POST['hostname'],
					'username' => $_POST['username'],
					'password' => $_POST['password'],
					'database' => $_POST['database']
				);
				if (strpos($params['hostname'], ":") !== FALSE)
				{
					list($hostname, $value) = explode(":", $params['hostname'], 2);
					if (preg_match('/\D/', $value))
					{
						$params['socket'] = $value;
					} else {
						$params['port'] = $value;
					}
					$params['hostname'] = $hostname;
				}
				$dbo = Singleton::getInstance($driver, $params);
				if (!$dbo->init())
				{
					$err = $dbo->connectError();
					if (empty($err))
					{
						$err = $dbo->error();
					}
				} else {
					if (!$this->CheckTables($dbo))
					{
						$this->set('warning', 1);
					}
					
					$tempTable = 'stivasoft_temp_install';
					
					$dbo->query("DROP TABLE IF EXISTS `$tempTable`;");
					
					if (!$dbo->query("CREATE TABLE IF NOT EXISTS `$tempTable` (`created` datetime DEFAULT NULL);"))
					{
						$err .= "CREATE command denied to current user<br />";
					} else {
						if (!$dbo->query("INSERT INTO `$tempTable` (`created`) VALUES (NOW());"))
						{
							$err .= "INSERT command denied to current user<br />";
						}
						if (!$dbo->query("SELECT * FROM `$tempTable` WHERE 1=1;"))
						{
							$err .= "SELECT command denied to current user<br />";
						}
						if (!$dbo->query("UPDATE `$tempTable` SET `created` = NOW();"))
						{
							$err .= "UPDATE command denied to current user<br />";
						}
						if (!$dbo->query("DELETE FROM `$tempTable` WHERE 1=1;"))
						{
							$err .= "DELETE command denied to current user<br />";
						}
					}
					if (!$dbo->query("DROP TABLE IF EXISTS `$tempTable`;"))
					{
						$err .= "DROP command denied to current user<br />";
					}
				}
			}
			if (!is_null($err))
			{
				$time = time();
				$_SESSION[$this->defaultErrors][$time] = $err;
				UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step2&install=1&err=" . $time);
			}
			
			$this->set('paths', self::GetPaths());
			
			$this->set('status', 'ok');
		}
		
		if (!isset($_SESSION[$this->defaultInstaller]['step2']))
		{
			UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step2&install=1");
		}
		
		/* else if (isset($_SESSION[$this->defaultInstaller])) {
			$this->set('status', 'ok');
		}*/
		$this->appendJs('jquery.validate.min.js', $this->getConst('PLUGIN_LIBS_PATH'));
		$this->appendJs('Installer.js', $this->getConst('PLUGIN_JS_PATH'));
	}

	public function Step4()
	{
		self::CheckConfig();
		
		$this->CheckSession();
		
		if (isset($_POST['step3']))
		{
			$_POST = array_map('trim', $_POST);
			
			if (!isset($_POST['install_folder']) || !isset($_POST['BASE_URL']) || !isset($_POST['install_path']) ||
				!ValidationComponent::NotEmpty($_POST['install_folder']) ||
				!ValidationComponent::NotEmpty($_POST['BASE_URL']) ||
				!ValidationComponent::NotEmpty($_POST['install_path']))
			{
				$time = time();
				$_SESSION[$this->defaultErrors][$time] = "Folder Name, Full URL and Server Path are required and can't be empty.";
				UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step3&install=1&err=" . $time);
			} else {
				$_POST = SanitizeComponent::clean($_POST, array('encode' => false));
				$_SESSION[$this->defaultInstaller] = array_merge($_SESSION[$this->defaultInstaller], $_POST);
			}
		}
		
		if (!isset($_SESSION[$this->defaultInstaller]['step3']))
		{
			UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step3&install=1");
		}
		
		$this->appendJs('jquery.validate.min.js', $this->getConst('PLUGIN_LIBS_PATH'));
		$this->appendJs('Installer.js', $this->getConst('PLUGIN_JS_PATH'));
	}
	
	public function Step5()
	{
		self::CheckConfig();
		
		$this->CheckSession();
		
		if (isset($_POST['step4']))
		{
			$_POST = array_map('trim', $_POST);
			
			if (!isset($_POST['admin_email']) || !isset($_POST['admin_password']) ||
				!ValidationComponent::NotEmpty($_POST['admin_email']) ||
				!ValidationComponent::Email($_POST['admin_email']) ||
				!ValidationComponent::NotEmpty($_POST['admin_password']))
			{
				$time = time();
				$_SESSION[$this->defaultErrors][$time] = "E-Mail and Password are required and can't be empty.";
				UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step4&install=1&err=" . $time);
			} else {
				$_POST = SanitizeComponent::clean($_POST, array('encode' => false));
				$_SESSION[$this->defaultInstaller] = array_merge($_SESSION[$this->defaultInstaller], $_POST);
			}
		}
		
		if (!isset($_SESSION[$this->defaultInstaller]['step4']))
		{
			UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step4&install=1");
		}
		
		$this->appendJs('jquery.validate.min.js', $this->getConst('PLUGIN_LIBS_PATH'));
		$this->appendJs('Installer.js', $this->getConst('PLUGIN_JS_PATH'));
	}
	
	public function Step6()
	{
		self::CheckConfig();
		
		$this->CheckSession();
		
		if (isset($_POST['step5']))
		{
			$_POST = array_map('trim', $_POST);
			
			if (!isset($_POST['license_key']) || !ValidationComponent::NotEmpty($_POST['license_key']))
			{
				$time = time();
				$_SESSION[$this->defaultErrors][$time] = "License Key is required and can't be empty.";
				UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step5&install=1&err=" . $time);
			} else {
				$_POST = SanitizeComponent::clean($_POST, array('encode' => false));
				$_SESSION[$this->defaultInstaller] = array_merge($_SESSION[$this->defaultInstaller], $_POST);
				
				$Http = new HttpComponent();
				$Http->request(base64_decode("aHR0cDovL3N1cHBvcnQuc3RpdmFzb2Z0LmNvbS8=") . 'index.php?controller=Api&action=newInstall&key=' . urlencode($_POST['license_key']) .
					"&version=". urlencode(SCRIPT_VERSION) ."&script_id=" . urlencode(SCRIPT_ID) .
					"&server_name=" . urlencode($_SERVER['SERVER_NAME']) . "&ip=" . urlencode($_SERVER['REMOTE_ADDR']) .
					"&referer=" . urlencode($_SERVER['HTTP_REFERER']));
				$resp = $Http->getResponse();
				$error = $Http->getError();
				$time = time();
				if ($resp === FALSE || (!empty($error) && $error['code'] == 109))
				{
					$_SESSION[$this->defaultErrors][$time] = "Installation key cannot be verified. Please, make sure you install on a server which is connected to the internet.";
					UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step5&install=1&err=" . $time);
				} else {
					$output = unserialize($resp);
				
					if (isset($output['hash']) && isset($output['code']) && $output['code'] == 200)
					{
						$_SESSION[$this->defaultInstaller]['private_key'] = $output['hash'];
					} else {
						$text = 'Key is wrong or not valid. Please check you data again.';
						if (isset($output['code']))
						{
							switch ((int) $output['code'])
							{
								case 101:
									$text = 'License key is not valid';
									break;
								case 106:
									$text = 'Number of installations allowed has been reached';
									break;
							}
						}

						$_SESSION[$this->defaultErrors][$time] = $text;
						UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step5&install=1&err=" . $time);
					}
				}
			}
		}
		
		if (!isset($_SESSION[$this->defaultInstaller]['step5']))
		{
			UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step5&install=1");
		}
		
		$this->appendJs('jquery.validate.min.js', $this->getConst('PLUGIN_LIBS_PATH'));
		$this->appendJs('Installer.js', $this->getConst('PLUGIN_JS_PATH'));
	}
	
	public function Step7()
	{
		$this->CheckSession();
		
		if (isset($_POST['step6']))
		{
			$_POST = SanitizeComponent::clean($_POST, array('encode' => false));
			$_SESSION[$this->defaultInstaller] = array_merge($_SESSION[$this->defaultInstaller], $_POST);
		}
		
		if (!isset($_SESSION[$this->defaultInstaller]['step6']))
		{
			UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Installer&action=Step6&install=1");
		}
		
		unset($_SESSION[$this->defaultInstaller]);
		unset($_SESSION[$this->defaultErrors]);
	}
	
	public function SetDb()
	{
		$this->setAjax(true);

		if ($this->isXHR())
		{
			if (!self::CheckVars())
			{
				AppController::jsonResponse(array('status' => 'ERR', 'code' => 108, 'text' => 'Missing, empty or invalid parameters.'));
			}
			@set_time_limit(300); //5 minutes
			
			$resp = array();
			
			$driver = function_exists('mysqli_connect') ? 'MysqliDriver' : 'MysqlDriver';
			$params = array(
				'hostname' => $_SESSION[$this->defaultInstaller]['hostname'],
				'username' => $_SESSION[$this->defaultInstaller]['username'],
				'password' => $_SESSION[$this->defaultInstaller]['password'],
				'database' => $_SESSION[$this->defaultInstaller]['database']
			);
			if (strpos($params['hostname'], ":") !== FALSE)
			{
				list($hostname, $value) = explode(":", $params['hostname'], 2);
				if (preg_match('/\D/', $value))
				{
					$params['socket'] = $value;
				} else {
					$params['port'] = $value;
				}
				$params['hostname'] = $hostname;
			}
			$dbo = Singleton::getInstance($driver, $params);
			if (!$dbo->init())
			{
				$err = $dbo->connectError();
				if (!empty($err))
				{
					$resp['code'] = 100;
				    $resp['text'] = 'Could not connect: ' . $err;
				    self::DbError($resp);
				} else {
					$resp['code'] = 101;
				    $resp['text'] = $dbo->error();
				    self::DbError($resp);
				}
			} else {
				$idb = self::ImportSQL($dbo, 'app/config/database.sql', $_SESSION[$this->defaultInstaller]['prefix']);
				if ($idb === true)
				{
					$_GET['install'] = 2;
					require 'app/Config/options.inc.php';
					
					$result = $this->requestAction(array(
						'controller' => 'AppController',
						'action' => 'BeforeInstall'
					), array('return'));
					
					if ($result !== NULL && isset($result['code']) && $result['code'] != 200 && isset($result['info']))
					{
						$resp['text'] = join("<br>", $result['info']);
						$resp['code'] = 104;
						self::DbError($resp);
					}
					
					$optionModel = OptionModel::factory()->setPrefix($_SESSION[$this->defaultInstaller]['prefix']);
					$statement = sprintf("INSERT IGNORE INTO `%s`(`foreign_id`,`key`,`tab_id`,`value`,`type`) VALUES (:foreign_id, :key, :tab_id, NOW(), :type);", $optionModel->getTable());
					$data = array(
						'foreign_id' => $this->getForeignId(),
						'tab_id' => 99,
						'type' => 'string'
					);
					
					if (isset($CONFIG['plugins']))
					{
						if (!is_array($CONFIG['plugins']))
						{
							$CONFIG['plugins'] = array($CONFIG['plugins']);
						}
						foreach ($CONFIG['plugins'] as $plugin)
						{
							$file = PLUGINS_PATH . $plugin . '/config/database.sql';
							if (is_file($file))
							{
								$response = self::ExecuteSQL($dbo, $file, $_SESSION[$this->defaultInstaller]['prefix'], SCRIPT_PREFIX);
								if ($response['status'] == "ERR")
								{
									self::DbError($response);
								}
								
								$update_folder = PLUGINS_PATH . $plugin . '/config/updates';
								if (is_dir($update_folder))
								{
									$files = array();
									ToolkitComponent::readDir($files, $update_folder);
									foreach ($files as $path)
									{
										if (preg_match('/\.sql$/', basename($path)) && is_file($path))
										{
											$response = self::ExecuteSQL($dbo, $path, $_SESSION[$this->defaultInstaller]['prefix'], SCRIPT_PREFIX);
											if ($response['status'] == "ERR")
											{
												self::DbError($response);
											} else if ($response['status'] == "OK") {
												$data['key'] = sprintf('o_%s_%s', basename($path), md5($path));
												$optionModel->prepare($statement)->exec($data);
											}
										}
									}
								}
							}
							$modelName = Objects::getConstant($plugin, 'PLUGIN_MODEL');
							if (class_exists($modelName) && method_exists($modelName, 'Setup'))
							{
								$pluginModel = new $modelName;
								$pluginModel->begin();
								$pluginModel->Setup();
								$pluginModel->commit();
							}

							$result = $this->requestAction(array(
								'controller' => $plugin,
								'action' => 'BeforeInstall'
							), array('return'));
							
							if ($result !== NULL && isset($result['code']) && $result['code'] != 200 && isset($result['info']))
							{
								$resp['text'] = join("<br>", $result['info']);
								$resp['code'] = 104;
								self::DbError($resp);
							}
						}
					}
					
					$updates = self::GetUpdates();
					foreach ($updates as $record)
					{
						$file_path = $record['path'];
						$response = self::ExecuteSQL($dbo, $file_path, $_SESSION[$this->defaultInstaller]['prefix'], SCRIPT_PREFIX);
						if ($response['status'] == "ERR")
						{
							self::DbError($response);
						} else if ($response['status'] == "OK") {
							$data['key'] = sprintf('o_%s_%s', basename($file_path), md5($file_path));
							$optionModel->prepare($statement)->exec($data);
						}
					}
					
					if (defined("TEMPLATE_PATH"))
					{
						$updates = self::GetUpdates(TEMPLATE_PATH);
						foreach ($updates as $record)
						{
							$file_path = $record['path'];
							$response = self::ExecuteSQL($dbo, $file_path, $_SESSION[$this->defaultInstaller]['prefix'], SCRIPT_PREFIX);
							if ($response['status'] == "ERR")
							{
								self::DbError($response);
							} else if ($response['status'] == "OK") {
								$data['key'] = sprintf('o_%s_%s', basename($file_path), md5($file_path));
								$optionModel->prepare($statement)->exec($data);
							}
						}
					}
					
					$result = $this->requestAction(array(
						'controller' => 'AppController',
						'action' => 'AfterInstall'
					), array('return'));
					
					if ($result !== NULL && isset($result['code']) && $result['code'] != 200 && isset($result['info']))
					{
						$resp['text'] = join("<br>", $result['info']);
						$resp['code'] = 105;
						self::DbError($resp);
					}

					UserModel::factory()
						->setPrefix($_SESSION[$this->defaultInstaller]['prefix'])
						->setAttributes(array(
							'email' => $_SESSION[$this->defaultInstaller]['admin_email'],
							'password' => $_SESSION[$this->defaultInstaller]['admin_password'],
							'role_id' => 1,
							'name' => "Administrator",
							'ip' => $_SERVER['REMOTE_ADDR']
						))
						->insert();
					
					OptionModel::factory()
						->setPrefix($_SESSION[$this->defaultInstaller]['prefix'])
						->setAttributes(array(
							'foreign_id' => $this->getForeignId(),
							'key' => 'private_key',
							'tab_id' => 99,
							'value' => $_SESSION[$this->defaultInstaller]['private_key'],
							'type' => 'string'
						))
						->insert();
					
					if (!isset($resp['code']))
					{
						$resp['code'] = 200;
					}
				} elseif ($idb === false) {
					$resp['code'] = 102; //File not found (can't be open/read)
					$resp['text'] = "File not found (or can't be read)";
					self::DbError($resp);
				} else {
					$resp['code'] = 103; //MySQL error
					$resp['text'] = $idb;
					self::DbError($resp);
				}
			}
			
			if (isset($resp['code']) && $resp['code'] != 200)
			{
				self::DbError($resp);
			}
			AppController::jsonResponse($resp);
		}
		exit;
	}
	
	private static function DbError($resp)
	{
		@file_put_contents('app/config/config.inc.php', '');
		AppController::jsonResponse($resp);
	}
	
	public function SetConfig()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (!self::CheckConfig(false))
			{
				AppController::jsonResponse(array('code' => 107, 'text' => 'Product is already installed. If you need to re-install it empty app/config/config.inc.php file.'));
			}
			$resp = array();
			
			$sample = 'app/config/config.sample.php';
			$filename = 'app/config/config.inc.php';
			ob_start();
			readfile($sample);
			$string = ob_get_contents();
			ob_end_clean();
			if ($string === FALSE)
			{
				$resp['code'] = 100;
				$resp['text'] = "An error occurs while reading 'app/config/config.sample.php'";
			} else {
				if (!self::CheckVars())
				{
					AppController::jsonResponse(array('status' => 'ERR', 'code' => 108, 'text' => 'Missing, empty or invalid parameters.'));
				}
				$string = str_replace('[hostname]', $_SESSION[$this->defaultInstaller]['hostname'], $string);
				$string = str_replace('[username]', $_SESSION[$this->defaultInstaller]['username'], $string);
				$string = str_replace('[password]', str_replace(
						array('$'),
						array('\$'),
						$_SESSION[$this->defaultInstaller]['password']
					), $string);
				$string = str_replace('[database]', $_SESSION[$this->defaultInstaller]['database'], $string);
				$string = str_replace('[prefix]', $_SESSION[$this->defaultInstaller]['prefix'], $string);
				$string = str_replace('[install_folder]', $_SESSION[$this->defaultInstaller]['install_folder'], $string);
				$string = str_replace('[install_path]', $_SESSION[$this->defaultInstaller]['install_path'], $string);
				$string = str_replace('[BASE_URL]', $_SESSION[$this->defaultInstaller]['BASE_URL'], $string);
				$string = str_replace('[salt]', UtilComponent::getRandomPassword(8), $string);
					
				$Http = new HttpComponent();
				$Http->request(base64_decode("aHR0cDovL3N1cHBvcnQuc3RpdmFzb2Z0LmNvbS8=") . 'index.php?controller=Api&action=getInstall'.
					"&key=" . urlencode($_SESSION[$this->defaultInstaller]['license_key']) .
					"&modulo=". urlencode(RSA_MODULO) .
					"&private=" . urlencode(RSA_PRIVATE) .
					"&server_name=" . urlencode($_SERVER['SERVER_NAME']));
				$response = $Http->getResponse();
				$output = unserialize($response);
				
				if (isset($output['hash']) && isset($output['code']) && $output['code'] == 200)
				{
					$string = str_replace('[installation]', $output['hash'], $string);
				
					if (is_writable($filename))
					{
					    if (!$handle = @fopen($filename, 'wb'))
					    {
							$resp['code'] = 103;
							$resp['text'] = "'app/config/config.inc.php' open fails";
					    } else {
						    if (fwrite($handle, $string) === FALSE)
						    {
								$resp['code'] = 102;
								$resp['text'] = "An error occurs while writing to 'app/config/config.inc.php'";
						    } else {
					    		fclose($handle);
					    		$resp['code'] = 200;
						    }
					    }
					} else {
						$resp['code'] = 101;
						$resp['text'] = "'app/config/config.inc.php' do not exists or not writable";
					}
				} else {
					$resp['code'] = 104;
					$resp['text'] = "Security vulnerability detected";
				}
			}
			AppController::jsonResponse($resp);
		}
		exit;
	}
	
	public function License()
	{
		$arr = OptionModel::factory()
			->where('t1.foreign_id', $this->getForeignId())
			->where('t1.key', 'private_key')
			->limit(1)
			->findAll()
			->getData();

		$hash = NULL;
		if (count($arr) === 1)
		{
			$hash = $arr[0]['value'];
		}
		UtilComponent::redirect(base64_decode("aHR0cDovL3N1cHBvcnQuc3RpdmFzb2Z0LmNvbS9jaGVja2xpY2Vuc2Uv") . $hash);
	}

	public function Version()
	{
		if ($this->isLoged() && $this->isAdmin())
		{
			printf('SCRIPT_ID: %s<br>', SCRIPT_ID);
			printf('SCRIPT_BUILD: %s<br><br>', SCRIPT_BUILD);
			
			$plugins = Registry::getInstance()->get('plugins');
			foreach ($plugins as $plugin => $whtvr)
			{
				printf("%s: %s<br>", $plugin, Objects::getConstant($plugin, 'PLUGIN_BUILD'));
			}
			if (method_exists('Objects', 'getFrameworkBuild'))
			{
				printf("<br>Framework: %s<br>", Objects::getFrameworkBuild());
			}
		}
		exit;
	}
	
	public function Hash()
	{
		@set_time_limit(0);
		
		if (!function_exists('md5_file'))
		{
			die("Function <b>md5_file</b> doesn't exists");
		}
		
		require 'app/Config/config.inc.php';
		
		# Origin hash -------------
		if (!is_file(CONFIG_PATH . 'files.check'))
		{
			die("File <b>files.check</b> is missing");
		}
		$json = @file_get_contents(CONFIG_PATH . 'files.check');
		$Services_JSON = new ServicesJSONComponent();
		$data = $Services_JSON->decode($json);
		if (is_null($data))
		{
			die("File <b>files.check</b> is empty or broken");
		}
		$origin = get_object_vars($data);
				
		# Current hash ------------
		$data = array();
		UtilComponent::readDir($data, INSTALL_PATH);
		$current = array();
		foreach ($data as $file)
		{
			$current[str_replace(INSTALL_PATH, '', $file)] = md5_file($file);
		}
		
		$html = '<style type="text/css">
		table{border: solid 1px #000; border-collapse: collapse; font-family: Verdana, Arial, sans-serif; font-size: 14px}
		td{border: solid 1px #000; padding: 3px 5px; background-color: #fff; color: #000}
		.diff{background-color: #0066FF; color: #fff}
		.miss{background-color: #CC0000; color: #fff}
		</style>
		<table cellpadding="0" cellspacing="0">
		<tr><td><strong>Filename</strong></td><td><strong>Status</strong></td></tr>
		';
		foreach ($origin as $file => $hash)
		{
			if (isset($current[$file]))
			{
				if ($current[$file] == $hash)
				{
					
				} else {
					$html .= '<tr><td>'. $file . '</td><td class="diff">changed</td></tr>';
				}
			} else {
				$html .= '<tr><td>'. $file . '</td><td class="miss">missing</td></tr>';
			}
		}
		$html .= '<table>';
		echo $html;
		exit;
	}
	
	private static function SortUpdates($haystack)
	{
		$_time = array();
		$_name = array();
		# Set some timezone just to prevent E_NOTICE/E_WARNING message
		date_default_timezone_set('America/Chicago');
		foreach ($haystack as $key => $item)
		{
			if (preg_match('/(20\d\d)_(0[1-9]|1[012])_(0[1-9]|[12][0-9]|3[01])_([01][0-9]|[2][0-3])_([0-5][0-9])_([0-5][0-9]).sql$/', $item['name'], $m))
			{
				$_time[$key] = mktime($m[4], $m[5], $m[6], $m[2], $m[3], $m[1]);
				$_name[$key] = $item['name'];
			}
		}

		if (!empty($haystack))
		{
			array_multisort($_time, SORT_ASC, SORT_NUMERIC, $_name, SORT_ASC, SORT_STRING, $haystack);
		}
		
		return $haystack;
	}
	
	private static function GetUpdates($update_folder='app/config/updates', $override_data=array())
	{
		if (!is_dir($update_folder))
		{
			return array();
		}

		$files = array();
		ToolkitComponent::readDir($files, $update_folder);
		
		$data = array();
		foreach ($files as $path)
		{
			$name = basename($path);
			if (preg_match('/(20\d\d)_(0[1-9]|1[012])_(0[1-9]|[12][0-9]|3[01])_([01][0-9]|[2][0-3])_([0-5][0-9])_([0-5][0-9]).sql$/', $name))
			{
				$data[] = array_merge(array(
					'name' => $name,
					'path' => $path
				), $override_data);
			}
		}

		return self::SortUpdates($data);
	}
	
	private static function ExecuteSQL($dbo, $file_path, $prefix=PREFIX, $scriptPrefix=SCRIPT_PREFIX)
	{
		$name = basename($file_path);
				
		$pdb = self::ImportSQL($dbo, $file_path, $prefix, $scriptPrefix);
		
		if ($pdb === false)
		{
			$text = sprintf("File '%s' not found (or can't be read).", $name);
			return array('status' => 'ERR', 'code' => 102, 'text' => $text);
		} elseif ($pdb === true) {
			$text = sprintf("File '%s' have been executed.", $name);
			return array('status' => 'OK', 'code' => 200, 'text' => $text);
		} else {
			$text = sprintf("File '%s': %s", $name, $pdb);
			return array('status' => 'ERR', 'code' => 103, 'text' => $text);
		}
	}
	
	public function SecureSetUpdate()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged() && $this->isAdmin())
		{
			# Next will init dbo
			AppModel::factory();
			
			$dbo = NULL;
			$registry = Registry::getInstance();
			if ($registry->is('dbo'))
			{
				$dbo = $registry->get('dbo');
			}
			
			if (!isset($_REQUEST['module']))
			{
				AppController::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => 'Module parameter is missing.'));
			}
			
			if (isset($_POST['path']) && !empty($_POST['path']))
			{
			switch ($_REQUEST['module'])
			{
				case 'template':
						$pattern = defined('TEMPLATE_PATH') ? sprintf('|^%s(.*)/updates|', TEMPLATE_PATH) : '|^templates/(.*)/updates|';
					break;
				case 'plugin':
					$pattern = '|^'.str_replace('\\', '/', PLUGINS_PATH).'|';
					break;
				case 'script':
				default:
					$pattern = '|^app/config/updates|';
					break;
			}
			
				if (preg_match($pattern, str_replace('\\', '/', $_POST['path'])))
				{
					$response = self::ExecuteSQL($dbo, $_POST['path']);
					if ($response['status'] == "OK")
					{
						$key = sprintf('o_%s_%s', basename($_POST['path']), md5($_POST['path']));
						$optionModel = OptionModel::factory()
							->where('t1.foreign_id', $this->getForeignId())
							->where('t1.key', $key);
						if (0 != $optionModel->findCount()->getData())
						{
							$optionModel
								->reset()
								->where('foreign_id', $this->getForeignId())
								->where('`key`', $key)
								->modifyAll(array('value' => ':NOW()'));
						} else {
							$optionModel
								->reset()
								->setAttributes(array(
									'foreign_id' => $this->getForeignId(),
									'key' => $key,
									'tab_id' => 99,
									'value' => ':NOW()',
									'type' => 'string'
								))
								->insert();
						}
					}
					AppController::jsonResponse($response);
				} else {
					AppController::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => 'Filename pattern doesn\'t match.'));
				}
			}
			
			if (isset($_POST['record']) && !empty($_POST['record']))
			{
				$optionModel = OptionModel::factory();
				foreach ($_POST['record'] as $k => $record)
				{
					switch ($_REQUEST['module'][$k])
					{
						case 'template':
							$pattern = defined('TEMPLATE_PATH') ? sprintf('|^%s(.*)/updates|', TEMPLATE_PATH) : '|^templates/(.*)/updates|';
							break;
						case 'plugin':
							$pattern = '|^'.str_replace('\\', '/', PLUGINS_PATH).'|';
							break;
						case 'script':
						default:
							$pattern = '|^app/config/updates|';
							break;
					}
					
					if (!preg_match($pattern, str_replace('\\', '/', $record)))
					{
						continue;
					}
					$response = self::ExecuteSQL($dbo, $record);
					if ($response['status'] == 'ERR')
					{
						AppController::jsonResponse($response);
					} elseif ($response['status'] == 'OK') {
						$key = sprintf('o_%s_%s', basename($record), md5($record));
						$optionModel
							->reset()
							->where('t1.foreign_id', $this->getForeignId())
							->where('t1.key', $key);
						if (0 != $optionModel->findCount()->getData())
						{
							$optionModel
								->reset()
								->where('foreign_id', $this->getForeignId())
								->where('`key`', $key)
								->modifyAll(array('value' => ':NOW()'));
						} else {
							$optionModel
								->reset()
								->setAttributes(array(
									'foreign_id' => $this->getForeignId(),
									'key' => $key,
									'tab_id' => 99,
									'value' => ':NOW()',
									'type' => 'string'
								))
								->insert();
						}
					}
				}
				
				AppController::jsonResponse($response);
			}
		}
		exit;
	}
	
	public function SecureGetUpdate()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged() && $this->isAdmin())
		{
			# Build data
			$data = self::BuildUpdates();
			
			# Sort data
			$data = self::SortUpdates($data);
			
			$keys = array();
			
			foreach ($data as &$item)
			{
				$item['base'] = base64_encode($item['path']);
				$keys[] = sprintf('o_%s_%s', $item['name'], md5($item['path']));
			}
			
			if (!empty($keys))
			{
				$options = OptionModel::factory()
					->select('t1.key, t1.value')
					->where('t1.foreign_id', $this->getForeignId())
					->whereIn('t1.key', $keys)
					->groupBy('t1.key')
					->findAll()
					->getDataPair('key', 'value');
				
				# Set some timezone just to prevent E_NOTICE/E_WARNING message
				date_default_timezone_set('America/Chicago');
				foreach ($data as &$item)
				{
					$index = sprintf('o_%s_%s', $item['name'], md5($item['path']));
					if (isset($options[$index]) && !empty($options[$index]))
					{
						$item['date'] = date("d/m/Y, H:i a", strtotime($options[$index]));
						$item['is_new'] = 0;
					} else {
						$item['date'] = "new DB update";
						$item['is_new'] = 1;
					}
				}
			}
			
			$total = count($data);
			$rowCount = $total;
			$pages = 1;
			$page = 1;
			$offset = 0;
						
			AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
		}
		exit;
	}
	
	public function SecureUpdate()
	{
		if ($this->isLoged() && $this->isAdmin())
		{
			$this->appendJs('jquery-1.8.3.min.js', $this->getConst('PLUGIN_LIBS_PATH'));
	    	$this->appendJs('jquery-ui.custom.min.js', THIRD_PARTY_PATH . 'jquery_ui/js/');
			$this->appendCss('jquery-ui.min.css', THIRD_PARTY_PATH . 'jquery_ui/css/smoothness/');
			$this->appendCss('table.css', FRAMEWORK_LIBS_PATH . '/css/');
			
			$this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
			$this->appendJs('InstallerUpdate.js', $this->getConst('PLUGIN_JS_PATH'));
			$this->appendJs('index.php?controller=Admin&action=Messages', "");
		} else {
			$this->set('status', 2);
		}
		
		$this->appendCss('secure.css', $this->getConst('PLUGIN_CSS_PATH'));
	}
	
	public function SecureView()
	{
		if ($this->isLoged() && $this->isAdmin())
		{
			if (isset($_GET['p']) && !empty($_GET['p']))
			{
				$path = base64_decode($_GET['p']);
				
				if (!preg_match('/\.sql$/', $path))
				{
					exit;
				}
				
				$data = self::BuildUpdates();
				$in_array = FALSE;
				foreach ($data as $item)
				{
					if ($item['path'] == $path)
					{
						$in_array = TRUE;
						break;
					}
				}
				
				if (!$in_array)
				{
					exit;
				}
				
				$string = @file_get_contents($path);
				if ($string !== FALSE)
				{
					header("Content-Type: text/plain; charset=utf-8");
					echo $string;
				}
			}
		}
		exit;
	}
	
	private static function BuildUpdates()
	{
		# Script
		$data1 = self::GetUpdates('app/config/updates', array('module' => 'script', 'label' => 'script'));
			
		# Plugins
		$data2 = array();
		if (isset($GLOBALS['CONFIG']['plugins']))
		{
			if (!is_array($GLOBALS['CONFIG']['plugins']))
			{
				$GLOBALS['CONFIG']['plugins'] = array($GLOBALS['CONFIG']['plugins']);
			}
			foreach ($GLOBALS['CONFIG']['plugins'] as $plugin)
			{
				$data2 = array_merge($data2, self::GetUpdates(PLUGINS_PATH . $plugin . '/config/updates', array('module' => 'plugin', 'label' => 'plugin '.$plugin)));
			}
		}
								
		# Templates
		$data3 = array();
		if (defined('TEMPLATE_PATH'))
		{
			$data3 = self::GetUpdates(TEMPLATE_PATH, array('module' => 'template'));
			foreach ($data3 as &$item)
			{
				$item['label'] = basename(dirname(dirname($item['path'])));
			}
		}

		return array_merge($data1, $data2, $data3);
	}
}
?>