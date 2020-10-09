<?php
namespace Core\Framework;
/**
 * A well known object that other ones can use to find related objects or service.
 *
 * Brief example of use:
 *
 * <code>
 * //Create an instance
 * $Registry = Registry::getInstance();
 *
 * $items = array(1,2,3,'some data');
 * //Setter
 * $Registry->set('data', $items);
 * //or
 * Registry::getInstance()->set('data', $items);
 *
 * //Getter
 * $data = $Registry->get('data');
 * //or
 * $data = Registry::getInstance()->get('data');
 * </code>
 *
 * @package framework
 * @since 1.0.0
 */
class Registry
{
/**
 * The instance of the registry
 *
 * @var object
 * @staticvar
 * @access private
 */
	private static $instance;
/**
 * Our array of objects
 *
 * @var array
 * @access private
 */
	private $objects = array();
/**
 * Private constructor to prevent it being created directly
 *
 * @access private
 */
	private function __construct()
	{
		//prevent directly access.
	}
/**
 * Prevent cloning of the object: issues an E_USER_ERROR if this is attempted
 *
 * @access public
 */
	public function __clone()
	{
		trigger_error("Clone is not allowed.", E_USER_ERROR);
	}
/**
 * Singleton method used to access the object
 *
 * @access public
 * @static
 * @staticvar Singleton $instance The Singleton instances of this class.
 * @return
 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self();
		}
		
		return self::$instance;
	}
/**
 * Gets an object from the registry
 *
 * @param string Key name as string.
 * @access public
 * @return mixed|null Returns stored value. If <var>key</var> not found returns <b>NULL</b>.
 */
	public function get($key)
	{
		if (isset($this->objects[$key]))
		{
			return $this->objects[$key];
		}
		
		return NULL;
	}
/**
 * Stores an object in the registry
 *
 * @param string Key name as string.
 * @param mixed Value to store.
 * @access public
 * @return void
 */
	public function set($key, $val)
	{
		$this->objects[$key] = $val;
	}
/**
 * Check if an object exists in registry
 *
 * @param string Key name as string.
 * @access public
 * @return boolean
 */
	public function is($key)
	{
		return ($this->get($key) !== null);
	}
}
?>