<?php
namespace Core\Framework\Components;

/**
 * XML data mapper
 *
 * @package framework.components
 */
class XMLComponent extends Component
{
/**
 * Version
 *
 * @var string
 * @access private
 */
	private $version = "1.0";
/**
 * End of line
 *
 * @var string
 * @access private
 */
	private $eol = "\n";
/**
 * Encoding
 *
 * @var string
 * @access private
 */
	private $encoding = "UTF-8";
/**
 * Data
 *
 * @var array
 * @access private
 */
	private $data = NULL;
/**
 * File name
 *
 * @var string
 * @access private
 */
	private $name = NULL;
/**
 * Item node name
 *
 * @var string
 * @access private
 */
	private $record = 'item';
/**
 * Root node name
 *
 * @var string
 * @access private
 */
	private $root = 'items';
/**
 * Fields
 *
 * @var array
 * @access private
 */
	private $fields = array();
/**
 * Content type
 *
 * @var string
 * @access private
 */
	private $mimeType = "text/xml";
/**
 * Constructor - automatically called when you create a new instance of a class with new
 *
 * @access public
 * @return self
 */
	public function __construct()
	{
		$this->name = time() . ".xml";
	}
/**
 * Force browser to download the data as file
 *
 * @access public
 * @return void
 */
	public function download()
	{
		ToolkitComponent::download($this->data, $this->name, $this->mimeType);
	}
/**
 * Make data XML-ready
 *
 * @param array $data
 * @access public
 * @return self
 */
	public function process($data=array())
	{
		$rows = array();
		$rows[] = '<?xml version="'.$this->version.'" encoding="'.$this->encoding.'"?>';
		$rows[] = '<' . $this->root . '>';
		foreach ($data as $item)
		{
			$cells = array();
			$cells[] = "\t<" . $this->record . ">";
			foreach ($item as $key => $value)
			{
				$cells[] = "\t\t<" . $key . ">" . SanitizeComponent::html($value) . "</" . $key . ">";
			}
			$cells[] = "\t</" . $this->record . ">";
			
			$rows[] = join($this->eol, $cells);
		}
		$rows[] = "</" . $this->root . ">";
		$this->setData(join($this->eol, $rows));
		
		return $this;
	}
/**
 * Write data to a file
 *
 * @access public
 * @return self
 */
	public function write()
	{
		file_put_contents($this->name, $this->data);
		return $this;
	}
/**
 * Upload and parse XML file
 *
 * @param array $file
 * @access public
 * @return boolean
 */
	public function load($file)
	{
		$UploadComponent = new UploadComponent();
		$UploadComponent->setAllowedExt(array('xml'));

		$data = array();
		if ($UploadComponent->load($file))
		{
			$filename = $UploadComponent->getFile('tmp_name');
			if (function_exists('simplexml_load_file'))
			{
				$xml = simplexml_load_file($filename);
				
				$xml = (array) $xml;
				$xml = array_values($xml);
				foreach ($xml[0] as $item)
				{
					$item = (array) $item;
					foreach ($item as $k => $v)
					{
						$item[$k] = strval($v);
					}
					$data[] = $item;
				}
				
				$this->setData($data);
				return true;
			}
		}
		return false;
	}
/**
 * Import data to given model. Runs SQL INSERT queries
 *
 * @param string $modelName
 * @access public
 * @return self
 */
	public function import($modelName)
	{
		if (is_array($this->data) && !empty($this->data))
		{
			$modelName .= 'Model';
			$model = new $modelName;
			if (is_object($model))
			{
				$model->begin();
				foreach ($this->data as $data)
				{
					if (count($this->fields) > 0)
					{
						foreach ($data as $k => $v)
						{
							if (!array_key_exists($k, $this->fields))
							{
								unset($data[$k]);
							}
						}
					}
					$model->reset()->setAttributes($data)->insert();
				}
				$model->commit();
			}
		}
		
		return $this;
	}
/**
 * Get data
 *
 * @access public
 * @return array
 */
	public function getData()
	{
		return $this->data;
	}
/**
 * Set data
 *
 * @param array $value
 * @access public
 * @return self
 */
	public function setData($value)
	{
		$this->data = $value;
		return $this;
	}
/**
 * Set version
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setVersion($value)
	{
		$this->version = $value;
		return $this;
	}
/**
 * Set end of line
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setEol($value)
	{
		$this->eol = $value;
		return $this;
	}
/**
 * Set encoding
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setEncoding($value)
	{
		$this->encoding = $value;
		return $this;
	}
/**
 * Set file name
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setName($value)
	{
		$this->name = $value;
		return $this;
	}
/**
 * Set root node
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setRoot($value)
	{
		$this->root = $value;
		return $this;
	}
/**
 * Set item node
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setRecord($value)
	{
		$this->record = $value;
		return $this;
	}
/**
 * Set conten type
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setMimeType($value)
	{
		$this->mimeType = $value;
		return $this;
	}
/**
 * Set fields
 *
 * @param array $value
 * @access public
 * @return self
 */
	public function setFields($value)
	{
		if (is_array($value))
		{
			$this->fields = $value;
		}
		return $this;
	}
}
?>