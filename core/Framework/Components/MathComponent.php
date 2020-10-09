<?php
namespace Core\Framework\Components;

define("MATH_BIGINTEGER_MODE", 1);
/**
 * Math wrapper
 *
 * @package framework.components
 * @since 1.0.4
 */
class MathComponent extends Component
{
/**
 * Add two arbitrary precision numbers
 *
 * @param string $a The left operand, as a string.
 * @param string $b The right operand, as a string.
 * @access public
 * @return string The sum of the two operands, as a string
 */
	public function add($a, $b)
	{
		$a = new MathBigIntegerComponent($a);
		$b = new MathBigIntegerComponent($b);
		$c = $a->add($b);
		
		return $c->toString();
	}
/**
 * Multiply two arbitrary precision number
 *
 * @param string $a The left operand, as a string.
 * @param string $b The right operand, as a string.
 * @access public
 * @return string Returns the result as a string.
 */
	public function mul($a, $b)
	{
		$a = new MathBigIntegerComponent($a);
		$b = new MathBigIntegerComponent($b);
		$c = $a->multiply($b);
		
		return $c->toString();
	}
/**
 * Raise an arbitrary precision number to another
 *
 * @param string $base The left operand, as a string.
 * @param string $exp The right operand, as a string.
 * @access public
 * @return string Returns the result as a string.
 */
	public function pow($base, $exp)
	{
		//FIXME
		return gmp_strval(gmp_pow($base, $exp));
	}
/**
 * Raise an arbitrary precision number to another, reduced by a specified modulus
 *
 * @param string $base The left operand, as a string.
 * @param string $exp The right operand, as a string.
 * @param string $mod The modulus, as a string.
 * @access public
 * @return string|null Returns the result as a string, or <b>NULL</b> if modulus is 0.
 */
	public function powmod($base, $exp, $mod)
	{
		//FIXME
		$base = new MathBigIntegerComponent($base);
		$exp = new MathBigIntegerComponent($exp);
		$mod = new MathBigIntegerComponent($mod);
		$mod = $base->modPow($exp, $mod);
		
		return $mod->toString();
	}
/**
 * Divide two arbitrary precision numbers
 *
 * @param string $a The left operand, as a string.
 * @param string $b The right operand, as a string.
 * @access public
 * @return string|null Returns the result of the division as a string, or <b>NULL</b> if right_operand is 0.
 */
	public function div($a, $b)
	{
		$a = new MathBigIntegerComponent($a);
		$b = new MathBigIntegerComponent($b);
		list($quotient, ) = $a->divide($b);

		return $quotient->toString();
	}
/**
 * Get modulus of an arbitrary precision number
 *
 * @param string $n The left operand, as a string.
 * @param string $d The modulus, as a string.
 * @access public
 * @return string Returns the modulus as a string, or <b>NULL</b> if modulus is 0.
 */
	public function mod($n, $d)
	{
		$n = new MathBigIntegerComponent($n);
		$d = new MathBigIntegerComponent($d);
		
		return $n->_mod2($d);
	}
/**
 * Compare two arbitrary precision numbers
 *
 * @param string $a The left operand, as a string.
 * @param string $b The right operand, as a string.
 * @access public
 * @return int Returns 0 if the two operands are equal, 1 if the left_operand is larger than the right_operand, -1 otherwise.
 */
	public function cmp($a, $b)
	{
		$a = new MathBigIntegerComponent($a);
		$b = new MathBigIntegerComponent($b);
		$c = $a->compare($b);
		
		return $c;
	}
}
?>