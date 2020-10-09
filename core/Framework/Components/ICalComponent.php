<?php
namespace Core\Framework\Components;

/**
 * iCal data mapper
 *
 * @package framework.components
 */
class ICalComponent extends Component
{
/**
 * Version
 *
 * @var string
 * @access private
 */
	private $version = "2.0";
/**
 * End of line
 *
 * @var string
 * @access private
 */
	private $eol = "\n";
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
 * proid
 *
 * @var string
 * @access private
 */
	private $prodid = 'Calendar';
/**
 * uiid
 *
 * @var string
 * @access private
 */
	private $uuid = 'uuid';
/**
 * created
 *
 * @var string
 * @access private
 */
	private $created = 'created';
/**
 * modified
 *
 * @var string
 * @access private
 */
	private $modified = 'modified';
/**
 * date_from
 *
 * @var string
 * @access private
 */
	private $date_from = 'date_from';
/**
 * date_to
 *
 * @var string
 * @access private
 */
	private $date_to = 'date_to';
/**
 * summary
 *
 * @var string
 * @access private
 */
	private $summary = 'c_name';
/**
 * c_name
 *
 * @var string
 * @access private
 */
	private $c_name = 'c_name';
/**
 * calendar
 *
 * @var string
 * @access private
 */
	private $location = 'location';
/**
 * timezone
 *
 * @var string
 * @access private
 */
	private $timezone = 'UTC/GMT';
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
	private $mimeType = "text/calendar";
/**
 * Constructor - automatically called when you create a new instance of a class with new
 *
 * @access public
 * @return self
 */
	public function __construct()
	{
		$this->name = time() . ".ics";
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
		$rows[] = "BEGIN:VCALENDAR";
		$rows[] = $this->version;
		$rows[] = "PRODID:-//".$this->prodid."//NONSGML Foobar//EN";
		$rows[] = "METHOD:UPDATE";
		
		foreach ($data as $item)
		{
			$cell = array();
			$sequence = null;
			$created = null;
			$cell[] = "BEGIN:VEVENT";
			$cell[] = "UID:".$item[$this->uuid];
			foreach ($item as $key => $value)
			{
				if($key == $this->modified)
				{
					if(!empty($value))
					{
						$sequence = "SEQUENCE:".strtotime($value);
					}
				}
				if($key == $this->created)
				{
					$created = "SEQUENCE:".strtotime($value);
				}
				if($key == $this->date_from)
				{
					$cell[] = "DTSTAMP:".date('Ymd',strtotime($value))."T".date('His',strtotime($value));
					if (strpos($value,':') !== false) {
						$cell[] = "DTSTART;TZID=".$this->timezone.":".date('Ymd',strtotime($value))."T".date('His',strtotime($value));
					}else{
						$cell[] = "DTSTART;TZID=".$this->timezone.":".date('Ymd',strtotime($value))."T000000";
					}
				}
				if($key == $this->date_to)
				{
					if (strpos($value,':') !== false) {
						$cell[] = "DTEND;TZID=".$this->timezone.":".date('Ymd',strtotime($value))."T".date('His',strtotime($value));
					}else{
						$cell[] = "DTEND;TZID=".$this->timezone.":".date('Ymd',strtotime($value))."T235959";
					}
				}
			}
			if($sequence == null)
			{
				$cell[] = $sequence;
			}else{
				$cell[] = $created;
			}
			$cell[] = "SUMMARY:" . stripslashes($item[$this->summary]);
			$cell[] = "DESCRIPTION: Name: ".stripslashes($item[$this->c_name]);
			$cell[] = "LOCATION:" . stripslashes($item[$this->location]);
			$cell[] = "END:VEVENT";
			$rows[] = join($this->eol, $cell);
		}
		$rows[] = "END:VCALENDAR";
		
		$this->setData(join($this->eol, $rows));
		
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
 * Set prodid
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setProdID($value)
	{
		$this->prodid = $value;
		return $this;
	}
/**
 * Set uuid
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setUUID($value)
	{
		$this->uuid = $value;
		return $this;
	}
/**
 * Set created
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setCreated($value)
	{
		$this->created = $value;
		return $this;
	}
/**
 * Set modified
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setModified($value)
	{
		$this->modified = $value;
		return $this;
	}
/**
 * Set date_from
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setDateFrom($value)
	{
		$this->date_from = $value;
		return $this;
	}
/**
 * Set date_to
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setDateTo($value)
	{
		$this->date_to = $value;
		return $this;
	}
/**
 * Set summary
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setSummary($value)
	{
		$this->summary = $value;
		return $this;
	}
/**
 * Set c_name
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setCName($value)
	{
		$this->c_name = $value;
		return $this;
	}
/**
 * Set location
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setLocation($value)
	{
		$this->location = $value;
		return $this;
	}
/**
 * Set timezone
 *
 * @param string $value
 * @access public
 * @return self
 */
	public function setTimezone($value)
	{
		$this->timezone = $value;
		return $this;
	}
}
?>