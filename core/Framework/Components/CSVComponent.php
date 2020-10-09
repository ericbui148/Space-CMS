<?php
namespace Core\Framework\Components;

/**
 * CSV data mapper
 *
 * @package framework.components
 *
 */
class CSVComponent extends Component
{
/**
 * Delimiter
 *
 * @var string
 * @access private
 */
	private $delimiter = ",";
/**
 * End of line
 *
 * @var string
 * @access private
 */
	private $eol = "\n";
/**
 * Enclose character
 *
 * @var string
 * @access private
 */
	private $enclose = '"';
/**
 * Escape character
 *
 * @var string
 * @access private
 */
	private $escape = '"';
/**
 * Flag indicates whether to include column names in the first row
 *
 * @var boolean
 * @access private
 */
	private $header = false;
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
 * Column names
 *
 * @var array
 * @access private
 */
	private $fields = array();
/**
 * Constructor - automatically called when you create a new instance of a class with new
 *
 * @access public
 * @return self
 */
	public function __construct()
	{
		$this->name = time() . ".csv";
	}
/**
 * Force browser to download the data as file
 *
 * @access public
 * @return void
 */
	public function download()
	{
		ToolkitComponent::download($this->data, $this->name, 'text/csv');
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
 * Make data CSV-ready
 *
 * @param array $data
 * @access public
 * @return self
 */
	public function process($data=array())
	{
		$i = 0;
		$keys = $rows = array();
		foreach ($data as $item)
		{
			if ($i === 0)
			{
				$keys = array_keys($item);
			}
			$cells = array();
			foreach ($item as $value)
			{
				$cells[] = $this->enclose . preg_replace('/'.$this->enclose.'/', $this->escape . $this->enclose, $value) . $this->enclose;
			}
			$rows[] = join($this->delimiter, $cells);
			$i++;
		}
		if ($this->header)
		{
			array_unshift($rows, join($this->delimiter, $keys));
		}
		$this->data = join($this->eol, $rows);
		
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
 * Upload and parse CSV file
 *
 * @param array $file
 * @access public
 * @return boolean
 */
	public function load($file)
	{
		$UploadComponent = new UploadComponent();
		$UploadComponent->setAllowedExt(array('csv'));

		$data = array();
		if ($UploadComponent->load($file))
		{
			$filename = $UploadComponent->getFile('tmp_name');
			$i = 1;
			if (($handle = fopen($filename, "r")) !== FALSE)
			{
				while (($values = fgetcsv($handle, 1000, ",")) !== FALSE)
				{
					if ($i == 1)
					{
						$keys = $values;
					} else {
						$data[] = array_combine($keys, $values);
					}
					$i++;
				}
				fclose($handle);
				$this->data = $data;
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
		if (is_array($this->data) && count($this->data) > 0)
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
 * Set delimiter
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setDelimiter($value)
	{
		$this->delimiter = $value;
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
 * Set enclose character
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setEnclose($value)
	{
		$this->enclose = $value;
		return $this;
	}
/**
 * Set escape character
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setEscape($value)
	{
		$this->escape = $value;
		return $this;
	}
/**
 * Set flag which indicated whether to include column names in the first row
 *
 * @param boolean $value
 * @access public
 * @return self
 */
	public function setHeader($value)
	{
		$this->header = (bool) $value;
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
 * Set column names
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