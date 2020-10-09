<?php
namespace App\Plugins\Locale\Models;

use Core\Framework\Model;

class LocaleAppModel extends Model
{
	public static function factory($attr=array())
	{
		return new LocaleAppModel($attr);
	}
}
?>