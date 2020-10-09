<?php
namespace Core\Framework\Components;

/**
 * Rivest/Shamir/Adelman (RSA) implementation
 *
 * @package framework.components
 *
 */
class RSAComponent extends Component
{
/**
 * Modulo
 *
 * @var int
 * @access private
 */
	private $n;
/**
 * Public key
 *
 * @var int
 * @access private
 */
	private $e;
/**
 * Private key
 *
 * @var int
 * @access private
 */
	private $d;
/**
 * Big Numbers library
 *
 * @var object
 * @access private
 */
	private $math;
/**
 * Constructor - automatically called when you create a new instance of a class with new
 *
 * @param int $n Modulo
 * @param int $e Public key
 * @param int $d Private key
 * @access public
 * @return self
 */
	public function __construct($n = 0, $e = 0, $d = 0)
	{
		$this->n = $n;
		$this->e = $e;
		$this->d = $d;

		if (extension_loaded('gmp'))
		{
			$this->math = new GMPComponent();
			
		} elseif (extension_loaded('bcmath')) {
			
			$this->math = new BCMathComponent();
			
		} elseif (extension_loaded('big_int') || extension_loaded('php_big_int')) {
			
			$this->math = new BigIntComponent();
			
		} else {
			
			//include_once dirname(__FILE__) . '/Math.component.php';
			//$this->math = new Math();
			//FIXME
		}
		
		return true;
	}
/**
 * Encryption
 *
 * @param string $m
 * @param int $s
 * @access public
 * @return string
 */
	public function encrypt($m, $s = 3)
	{
        $coded = '';
        $max = strlen($m);
        $packets = ceil($max / $s);

        for ($i = 0; $i < $packets; $i++)
        {
            $packet = substr($m, $i * $s, $s);
            $code = '0';

			for ($j = 0; $j < $s; $j++)
			{
				if (isset($packet[$j]))
				{
					$code = $this->math->add($code, $this->math->mul(ord($packet[$j]), $this->math->pow('256', $j)));
				}
			}

			$code = $this->math->powmod($code, $this->e, $this->n);
			$coded .= $code.' ';
		}

		return trim($coded);
    }
/**
 * Decryption
 *
 * @param string $c
 * @access public
 * @return string
 */
	public function decrypt($c)
	{
		$coded = explode(' ', $c);
		$message = '';
		$max = count($coded);
		for ($i = 0; $i < $max; $i++)
		{
			$code = $this->math->mod($this->math->pow($coded[$i], $this->d), $this->n);
			while ($this->math->cmp($code, '0') != 0)
			{
				$ascii = $this->math->mod($code, '256');
				$code = $this->math->div($code, '256', 0);
				$message .= chr($ascii);
			}
		}

		return $message;
    }
}
?>