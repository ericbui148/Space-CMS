<?php
namespace App\Models;

use App\Controllers\Components\UtilComponent;

class VoucherModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'vouchers';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'code', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'type', 'type' => 'enum', 'default' => ':NULL'),
		array('name' => 'discount', 'type' => 'decimal', 'default' => ':NULL'),
		array('name' => 'valid', 'type' => 'enum', 'default' => ':NULL'),
		array('name' => 'date_from', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'date_to', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'time_from', 'type' => 'time', 'default' => ':NULL'),
		array('name' => 'time_to', 'type' => 'time', 'default' => ':NULL'),
		array('name' => 'every', 'type' => 'enum', 'default' => ':NULL')
	);
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
	
	public function getVoucher($code, $date, $time)
	{
		$sql = sprintf("SELECT *, TIME_TO_SEC(`time_from`) AS `sec_from`, TIME_TO_SEC(`time_to`) AS `sec_to` FROM `%s` WHERE `code` = '%s' LIMIT 1", $this->getTable(), $code);
		$arr = $this->execute($sql);
		if (count($arr) == 1)
		{
			$arr = $arr[0];
			$sec = UtilComponent::hoursToSeconds($time);
			switch ($arr['type'])
			{
				case 'period':
					if ($date >= $arr['date_from'] && $date <= $arr['date_to'] && $sec >= $arr['sec_from'] && $sec <= $arr['sec_to'])
					{
						// OK
					} else {
						$arr = array();
					}
					break;
				case 'fixed':
					if ($arr['date_from'] == $date && $sec >= $arr['sec_from'] && $sec <= $arr['sec_to'])
					{
						// OK
					} else {
						$arr = array();
					}
					break;
				case 'recurring':
					if (date("l", strtotime($date)) == ucfirst($arr['every']) && $sec >= $arr['sec_from'] && $sec <= $arr['sec_to'])
					{
						// OK
					} else {
						$arr = array();
					}
					break;
			}
		}
		return $arr;
	}
}
?>