<?php
namespace App\Controllers\Components;

class CaptchaComponent extends AppComponent
{
/**
 * Whether to draw a border or not
 *
 * @access private
 * @var bool
 */
	var $border = true;
/**
 * Path to font file
 *
 * @access private
 * @var string
 */
	var $font = null;
/**
 * Font size in pixels
 *
 * @access private
 * @var int
 */
	var $fontSize = 12;
/**
 * Image height in pixels
 *
 * @access private
 * @var int
 */
	var $height = 35;
/**
 * Path to image background
 *
 * @access private
 * @var string
 */
	var $image = null;
/**
 * Length of word
 *
 * @access private
 * @var int
 */
	var $length = null;
/**
 * Session variable name
 *
 * @access private
 * @var string
 */
	var $sessionVariable = null;
/**
 * Image width in pixels
 *
 * @access private
 * @var int
 */
	var $width = 79;
/**
 * Constructor
 *
 * @param string $fontPath Path to font file
 * @param string $sessionVariable Session variable name
 * @param int $length Length of word
 * @access public
 * @return void
 */
	function Captcha($fontPath, $sessionVariable, $length = 4)
	{
		$this->font = $fontPath;
		$this->sessionVariable = $sessionVariable;
		$this->length = intval($length);
	}
/**
 * Output image to browser
 *
 * @param mixed $renew
 * @access public
 * @return mixed
 */
	function init($renew=null)
	{
    	if (!is_null($renew))
    	{
    		$_SESSION[$this->sessionVariable] = NULL;
    	}

		if (!isset($_SESSION[$this->sessionVariable]) || empty($_SESSION[$this->sessionVariable]))
		{
			$operators = array('+', '-');
			$first = rand(1, 99);
			$second = $first > 9 ? rand(1, 9) : rand(10, 99);
			$operator = '+'; //$operators[rand(0, 1)];
			$stack = array($first, $operator, $second);
			$_SESSION[$this->sessionVariable] = $first + $second;
			$rand_code = join(" ", $stack) . " = ?";
			/*
			$str = "";
			for ($i = 0; $i < $this->length; $i++)
			{
				//this numbers refer to numbers of the ascii table (small-caps)
				// 97 - 122 (small-caps)
				// 65 - 90 (all-caps)
				// 48 - 57 (digits 0-9)
				$mix = array(chr(rand(97, 122)), chr(rand(65, 90)), chr(rand(48, 57)));
				$str .= $mix[rand(0, 2)];
			}
			$_SESSION[$this->sessionVariable] = $str;
			$rand_code = $_SESSION[$this->sessionVariable];*/
		} else {
			$rand_code = $_SESSION[$this->sessionVariable];
		}

		if (!is_null($this->image))
		{
			$image = imagecreatefrompng($this->image);
		} else {
			$image = imagecreatetruecolor($this->width, $this->height);
			
			$backgr_col = imagecolorallocate($image, 204, 204, 204);
			imagefilledrectangle($image, 0, 0, $this->width, $this->height, $backgr_col);
		}
		if ($this->border)
		{
			$border_col = imagecolorallocate($image, 153, 153, 153);
			imagerectangle($image, 0, 0, $this->width - 1, $this->height - 1, $border_col);
		}
		
		$text_col = imagecolorallocate($image, 68, 68, 68);

		$angle = rand(-10, 10);
		$box = imagettfbbox($this->fontSize, $angle, $this->font, $rand_code);
		$x = (int)($this->width - $box[4]) / 2;
		$y = (int)($this->height - $box[5]) / 2;
		imagettftext($image, $this->fontSize, $angle, $x, $y, $text_col, $this->font, $rand_code);
		
		header("Content-type: image/png");
		imagepng($image);
		imagedestroy ($image);
	}
/**
 * Set path to font file
 *
 * @param string $fontPath Path to font file
 * @access public
 * @return void
 */
	function setFont($fontPath)
	{
		$this->font = $fontPath;
		return $this;
	}
/**
 * Set length of word
 *
 * @param int $length Length of word
 * @access public
 * @return void
 */
	function setLength($length)
	{
		if ((int) $length > 0)
		{
			$this->length = intval($length);
		}
		return $this;
	}
/**
 * Set session variable name
 *
 * @param string $sessionVariable Session variable name
 * @access public
 * @return void
 */
	function setSessionVariable($sessionVariable)
	{
		$this->sessionVariable = $sessionVariable;
		return $this;
	}
/**
 * Set image height
 *
 * @param int $height Image height in pixels
 * @access public
 * @return void
 */
	function setHeight($height)
	{
		if ((int) $height > 0)
		{
			$this->height = intval($height);
		}
		return $this;
	}
/**
 * Set image width
 *
 * @param int $width Image width in pixels
 * @access public
 * @return void
 */
	function setWidth($width)
	{
		if ((int) $width > 0)
		{
			$this->width = intval($width);
		}
		return $this;
	}
/**
 * Set font size
 *
 * @param int $fontSize Font size in pixels
 * @access public
 * @return void
 */
	function setFontSize($fontSize)
	{
		if ((int) $fontSize > 0)
		{
			$this->fontSize = intval($fontSize);
		}
		return $this;
	}
/**
 * Set image background
 *
 * @param string $image Path to image file
 * @access public
 * @return void
 */
	function setImage($image)
	{
		$this->image = $image;
		return $this;
	}
	
	function setBorder($value)
	{
		$this->border = (bool) $value;
		return $this;
	}
}
?>