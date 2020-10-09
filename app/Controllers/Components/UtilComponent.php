<?php
namespace App\Controllers\Components;
use Core\Framework\Components\ToolkitComponent;
use App\Models\OptionModel;
use App\Models\RouterModel;
use App\Models\VoucherModel;
use App\Plugins\Locale\Models\LocaleModel;

class UtilComponent extends ToolkitComponent
{
	static public function getReferer()
	{
		if (isset($_GET['_escaped_fragment_']))
		{
			if (isset($_SERVER['REDIRECT_URL']))
			{
				return $_SERVER['REDIRECT_URL'];
			}
		}
		
		if (isset($_SERVER['HTTP_REFERER']))
		{
			$pos = strpos($_SERVER['HTTP_REFERER'], "#");
			if ($pos !== FALSE)
			{
				return substr($_SERVER['HTTP_REFERER'], 0, $pos);
			}
			return $_SERVER['HTTP_REFERER'];
		}
	}
	static public function uuid()
	{
		return chr(rand(65,90)) . chr(rand(65,90)) . time();
	}
	
	static public function getBreadcrumbTree(&$stack, $category_arr, $id)
	{
		foreach ($category_arr as $category)
		{
			if ($category['data']['id'] == $id)
			{
				if ($category['deep'] == 0)
				{
					$stack[] = $category;
				} else {
					$stack[] = $category;
					UtilComponent::getBreadcrumbTree($stack, $category_arr, $category['data']['parent_id']);
				}
	
				break;
			}
		}
	}
	static public function getDiscount($amount, $product_id, $voucher=NULL)
	{
		$discount = 0;
	
		if (!is_null($voucher) && isset($voucher) && !empty($voucher) &&
				($voucher['voucher_products'] == 'all' || in_array($product_id, $voucher['voucher_products']))
		)
		{
			$voucher_discount = $voucher['voucher_discount'];
			switch ($voucher['voucher_type'])
			{
				case 'percent':
					//$discount_print = $voucher_discount . '%';
					$discount = ($amount * $voucher_discount) / 100;
					break;
				case 'amount':
					//$discount_print = UtilComponent::formatCurrencySign(number_format($voucher_discount, 2), $tpl['option_arr']['o_currency']);
					$discount = $voucher_discount;
					break;
			}
		}
	
		return $discount;
	}
	static public function getAncestor($arr, $id)
	{
		$ancestor = NULL;
		foreach ($arr as $item)
		{
			if ($item['deep'] > 0 && $item['data']['id'] == $id)
			{
				$ancestor = $item['data']['parent_id'];
				break;
			}
		}
	
		if (!is_null($ancestor))
		{
			foreach ($arr as $item)
			{
				if ($item['deep'] == 0 && $item['data']['id'] == $ancestor)
				{
					return $ancestor;
				}
			}
			return UtilComponent::getAncestor($arr, $ancestor);
		} else {
			return $id;
		}
	}	
	static public function formatSizeUnits($bytes)
	{
		if ($bytes >= 1073741824)
		{
			$bytes = number_format($bytes / 1073741824, 2) . ' GB';
		}elseif ($bytes >= 1048576){
			$bytes = number_format($bytes / 1048576, 2) . ' MB';
		}elseif ($bytes >= 1024){
			$bytes = number_format($bytes / 1024, 2) . ' KB';
		}elseif ($bytes > 1){
			$bytes = $bytes . ' bytes';
		}elseif ($bytes == 1){
			$bytes = $bytes . ' byte';
		}else{
			$bytes = '0 bytes';
		}
		return $bytes;
	}
	
	static public function getClientIp()
	{
		if (isset($_SERVER['HTTP_CLIENT_IP']))
		{
			return $_SERVER['HTTP_CLIENT_IP'];
		} else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if(isset($_SERVER['HTTP_X_FORWARDED'])) {
			return $_SERVER['HTTP_X_FORWARDED'];
		} else if(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_FORWARDED_FOR'];
		} else if(isset($_SERVER['HTTP_FORWARDED'])) {
			return $_SERVER['HTTP_FORWARDED'];
		} else if(isset($_SERVER['REMOTE_ADDR'])) {
			return $_SERVER['REMOTE_ADDR'];
		}

		return 'UNKNOWN';
	}
	
	static public function textToHtml($content)
	{
		$content = preg_replace('/\r\n|\n/', '<br />', $content);
		return '<html><head><title></title></head><body>'.$content.'</body></html>';
	}
	static public function getTitles(){
		$arr = array();
		$arr[] = 'mr';
		$arr[] = 'mrs';
		$arr[] = 'ms';
		$arr[] = 'dr';
		$arr[] = 'prof';
		$arr[] = 'rev';
		$arr[] = 'other';
		return $arr;
	}
	static public function getWeekdays(){
		$arr = array();
		$arr[] = 'monday';
		$arr[] = 'tuesday';
		$arr[] = 'wednesday';
		$arr[] = 'thursday';
		$arr[] = 'friday';
		$arr[] = 'saturday';
		$arr[] = 'sunday';
		return $arr;
	}
	static public function getWeekRange($week_start)
	{
		$week_arr = array(0=>'sunday',
						  1=>'monday',
						  2=>'tuesday',
						  3=>'wednesday',
						  4=>'thursday',
						  5=>'friday',
						  6=>'saturday');
						   
		$ts = strtotime(date('Y-m-d'));
	    $start = (date('w', $ts) == 0) ? $ts : strtotime('last ' . $week_arr[$week_start], $ts);
	    $week_start = ($week_start == 0 ? 6 : $week_start -1);
	    return array(date('Y-m-d', $start), date('Y-m-d', strtotime('next ' . $week_arr[$week_start], $start)));
	}
	static public function getPostMaxSize()
	{
		$post_max_size = ini_get('post_max_size');
		switch (substr($post_max_size, -1))
		{
			case 'G':
				$post_max_size = (int) $post_max_size * 1024 * 1024 * 1024;
				break;
			case 'M':
				$post_max_size = (int) $post_max_size * 1024 * 1024;
				break;
			case 'K':
				$post_max_size = (int) $post_max_size * 1024;
				break;
		}
		return $post_max_size;
	}
	
	static public function sortArrayByArray(Array $array, Array $orderArray) {
		$ordered = array();
		foreach($orderArray as $key)
		{
			if(array_key_exists($key,$array))
			{
				$ordered[$key] = $array[$key];
				unset($array[$key]);
			}
		}
		return $ordered + $array;
	}
	
	public static function getStockMoneyFormatPattern() {
		$moneyFormatPattern = '';
		$oMoneyFormat = OptionModel::factory()
		->select('t1.value as o_money_format')
		->where("t1.key = 'o_money_format'")
		->limit(1)
		->findAll()
		->getData();
		if(!empty($oMoneyFormat[0]['o_money_format'])) {
			$arrMoneyFormats = explode("::", $oMoneyFormat[0]['o_money_format']);
			$moneyFormatPattern = $arrMoneyFormats[1];
		}
	
		return $moneyFormatPattern;
	}
	
	static public function getCurrentTimeSnap15Minutes()
	{
		$interval = 15 * 60;
		$ts = time();
		$last = $ts - $ts % $interval;
		$next = $last + $interval + 3600;
		return $next;
	}
	
	public static function stringIsNumeric($value)
	{
		return (preg_match ("/\A(-){0, 1}([0-9]+)((,|.)[0-9]{3, 3})*((,|.)[0-9]){0, 1}([0-9]*)\z/" ,$value) == 1);
	}
	
	public static function convertMoneyFormatedData($data = array()) {
		$result =$data;
		if(!empty($result)) {
			foreach ($result as $key => $value) {
				if(self::stringIsNumeric($value)) {
					$result[$key] = self::convertFormatedNumberToFloat($value);
				}
			}
		}
		
		return $result;
	}

	public static function convertFormatedNumberToFloat($formatString) {
  		return floatval(preg_replace('/[^\d.]/', '', $formatString));
 	}
 	static public function treeMenu($arr, $category)
 	{
 		if ($category['children'] > 0)
 		{
 			?>
 				<ul role="menu" class="scMenu">
 				<?php
 				foreach ($arr as $item)
 				{
 					if ($item['data']['parent_id'] == $category['data']['id'])
 					{
 						?>
 						<li role="menuitem" class="scMenuItem<?php echo $item['children'] > 0 ? ' scMenuItemHub' : NULL; ?>">
 							<a class="scDropDownMenu" href="javascript:void(0);" data-href="<?php echo UtilComponent::getReferer(); ?>#!/Products/q:/category:<?php echo $item['data']['id']; ?>/page:1"><?php echo stripslashes($item['data']['name']); ?></a>
 							<?php UtilComponent::treeMenu($arr, $item); ?>
 						</li>
 						<?php
 					}
 				}
 				?>
 				</ul>
 				<?php
 			}
 		}
 		
 		static public function treeMenuLayout3($arr, $category)
 		{
 			if ($category['children'] > 0)
 			{
 				?>
 					<ul role="menu" class="dropdown-menu">
 					<?php
 					foreach ($arr as $item)
 					{
 						if ($item['data']['parent_id'] == $category['data']['id'])
 						{
 							?>
 							<li<?php echo $item['children'] > 0 ? ' class="dropdown-submenu"' : NULL; ?>>
 								<a class="scDropDownMenu" href="javascript:void(0);" data-href="<?php echo UtilComponent::getReferer(); ?>#!/Products/q:/category:<?php echo $item['data']['id']; ?>/page:1"><?php echo stripslashes($item['data']['name']); ?></a>
 								<?php UtilComponent::treeMenuLayout3($arr, $item); ?>
 							</li>
 							<?php
 						}
 					}
 					?>
 					</ul>
 					<?php
 				}
 	}
 	 	
	public static function getMoneyFormatPattern() {
	  	$moneyFormatPattern = '';
	  	$oMoneyFormat = OptionModel::factory()
		  ->select('t1.value as o_money_format')
		  ->where("t1.key = 'o_money_format'")
		  ->limit(1)
		  ->findAll()
		  ->getData();
	  if(!empty($oMoneyFormat[0]['o_money_format'])) {
	   		$arrMoneyFormats = explode("::", $oMoneyFormat[0]['o_money_format']);
	   		$moneyFormatPattern = $arrMoneyFormats[1];
	 	}
  		return $moneyFormatPattern;
 	}
	public static function formatNumberByPattern($s, $pattern) {
		$number = '';
		  switch ($pattern)
		  {
		   case 'x,xxx,xxx':
		    $number = number_format( (float) $s, 0, '.', ',');
		    break;
		   case 'x,xxx,xxx.xx':
		    $number = number_format( (float) $s, 2, '.', ',');
		    break;
		   case 'x.xxx.xxx':
		    $number = number_format( (float) $s, 0, ',', '.');
		    break;
		   case 'x.xxx.xxx,xx':
		    $number = number_format( (float) $s, 2, ',', '.');
		    break;
		   default:
		    $number = number_format( (float) $s, 2, '.', ',');
		    break;
		  }
	  	return $number;
	 }
	 
	public static function remove_accent($str)
	{
		$a = array('À','Á','Ạ','Ả','Â','Ầ','Ấ','Ã','Ạ','Ä','Å','Æ','Ç','È','É','Ẹ','Ê','Ề','Ë','Ệ','Ì','Í','Ị','Î','Ï','Ð','Ñ','Ò','Ó','Ọ','Ô','Ổ','Õ','Ö','Ø','Ù','Ủ','Ú','Ụ','Û','Ü','Ý','ß','à','ả','á','ạ','â','ấ','ẩ','ấ','ầ','ậ','ã','ä','å','æ','ç','è','é','ẹ','ê','ế','ề','ệ','ẻ','ë','ì','í','ỉ','ị','î','ï','ñ','ò','ó','ọ','ô','ổ','ồ','ố','ộ','õ','ö','ø','ù','ú','ủ','ụ','û','ư','ừ','ứ','ự','ữ','ü','ý','ỳ','ỹ','ỵ','ÿ','Ā','ā','Ă','Ằ','Ặ','ă','ắ','ặ','ẵ','Ą','ą','Ć','ć','Ĉ','ĉ','Ċ','ċ','Č','č','Ď','ď','Đ','đ','Ē','ē','Ĕ','ĕ','Ė','ė','Ę','ę','Ě','ě','Ĝ','ĝ','Ğ','ğ','Ġ','ġ','Ģ','ģ','Ĥ','ĥ','Ħ','ħ','Ĩ','ĩ','Ī','ī','Ĭ','ĭ','Į','į','İ','ı','Ĳ','ĳ','Ĵ','ĵ','Ķ','ķ','Ĺ','ĺ','Ļ','ļ','Ľ','ľ','Ŀ','ŀ','Ł','ł','Ń','ń','Ņ','ņ','Ň','ň','ŉ','Ō','ō','Ŏ','ŏ','Ő','ő','Œ','œ','Ŕ','ŕ','Ŗ','ŗ','Ř','ř','Ś','ś','Ŝ','ŝ','Ş','ş','Š','š','Ţ','ţ','Ť','ť','Ŧ','ŧ','Ũ','ũ','Ū','ū','Ŭ','ŭ','Ů','ů','Ű','ű','Ų','ų','Ŵ','ŵ','Ŷ','ŷ','Ÿ','Ź','ź','Ż','ż','Ž','ž','ſ','ƒ','Ơ','Ợ','Ỡ','ơ','ợ','ớ','ờ','ỡ','Ư','ư','ử','ử','Ǎ','ǎ','Ǐ','ǐ','Ǒ','ǒ','Ǔ','ǔ','Ǖ','ǖ','Ǘ','ǘ','Ǚ','ǚ','Ǜ','ǜ','Ǻ','ǻ','Ǽ','ǽ','Ǿ','ǿ');
		$b = array('A','A','A','A','A','A','A','A','A','A','A','AE','C','E','E','E','E','E','E','E','I','I','I','I','I','D','N','O','O','O','O','O','O','O','O','U','U','U','U','U','U','Y','s','a','a','a','a','a','a','a','a','a','a','a','a','a','ae','c','e','e','e','e','e','e','e','e','e','i','i','i','i','ị','i','i','o','o','o','o','o','o','o','o','o','o','o','o','u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y','A','a','A','A','A','a','a','a','a','A','a','C','c','C','c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','IJ','ij','J','j','K','k','L','l','L','l','L','l','L','l','l','l','N','n','N','n','N','n','n','O','o','O','o','O','o','OE','oe','R','r','R','r','R','r','S','s','S','s','S','s','S','s','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','s','f','O','O','O','o','o','o','o','o','U','u','u','u','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','A','a','AE','ae','O','o');
		return str_replace($a, $b, $str);
	}
	
	public static function cleanVietnamese($str)
	{
		$unicode = array(
				'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ|ä|å|æ',
				'd'=>'đ|ð',
				'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
				'i'=>'í|ì|ỉ|ĩ|ị|î|ï',
				'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
				'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
				'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
				'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ|Ä|Å|Æ',
				'D'=>'Đ',
				'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ|Ë',
				'I'=>'Í|Ì|Ỉ|Ĩ|Ị|Î|Ï',
				'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
				'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
				'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
		);
		foreach($unicode as $nonUnicode=>$uni)
		{
			$str = preg_replace("/($uni)/i", $nonUnicode, $str);
		}
		return $str;
	}
	public static function cleanURL($string, $delimiter = '-') {
		// Remove special characters
		$string = preg_replace("/[^\p{L}\/_|+ -]/ui","",$string);

		// Replace blank space with delimeter
		$string = preg_replace("/[\/_|+ -]+/", $delimiter, $string);

		// Trim delimiter
		$string =  trim($string,$delimiter);

		return mb_strtolower($string, 'UTF-8');
	}

	public static function post_slug($str, $divider = '-')
	{ 
		$locale = isset($_SESSION['admin_locale_id'])? $_SESSION['admin_locale_id'] : null;
		if ($locale == 1 || $locale == 2) {
			$slug = strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('', $divider, ''), self::cleanVietnamese($str)));
		} else {
			$slug = 'o' . self::cleanURL($str);			
		}
		return $slug;
	}
	
	static public function formatPrice($price, $format, $currency_code)
	{
		$price = preg_replace('/\s+/', '', $price);
		$price = str_replace(',', '', $price);
	
		switch($format)
		{
			case "$100000":
				$price = number_format($price, 0, '.', '');
				break;
			case "$100 000":
				$price = number_format($price, 0, '.', ' ');
				break;
			case "$100,000":
				$price = number_format($price, 0, '.', ',');
				break;
			case "$100,000.00":
				$price = number_format($price, 2, '.', ',');
				break;
		}
		$price = UtilComponent::getCurrencySign($currency_code, false) . $price;
	
		return $price;
	}

	static public function truncateDescription($string, $limit, $break=".", $pad="..."){
		if(strlen($string) <= $limit)
			return $string;
		if(false !== ($breakpoint = strpos($string, $break, $limit)))
		{
			if($breakpoint < strlen($string) - 1)
			{
				$string = substr($string, 0, $breakpoint) . $pad;
			}
		}
		return $string;
	}

	static public function html2txt($document)
	{
		$search = array('@<script[^>]*?>.*?</script>@si',
				'@<[\/\!]*?[^<>]*?>@si',
				'@<style[^>]*?>.*?</style>@siU',
				'@<![\s\S]*?--[ \t\n\r]*>@'
		);
		$text = preg_replace($search, '', $document);
		return $text;
	}

	static public function secondsToTime($inputSeconds) 
	{
		$secondsInAMinute = 60;
		$secondsInAnHour  = 60 * $secondsInAMinute;
		$secondsInADay    = 24 * $secondsInAnHour;
	
		$days = floor($inputSeconds / $secondsInADay);

		$hourSeconds = $inputSeconds % $secondsInADay;
		$hours = floor($hourSeconds / $secondsInAnHour);
	
		$minuteSeconds = $hourSeconds % $secondsInAnHour;
		$minutes = floor($minuteSeconds / $secondsInAMinute);
	
		$remainingSeconds = $minuteSeconds % $secondsInAMinute;
		$seconds = ceil($remainingSeconds);
	
		$obj = array(
				'days' => (int) $days,
				'hours' => (int) $hours,
				'minutes' => (int) $minutes,
				'seconds' => (int) $seconds,
		);
		return $obj;
	}
	
	static public function  getBaseUrl(){
		if(isset($_SERVER['HTTPS'])){
			$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
		}
		else{
			$protocol = 'http';
		}
		return $protocol . "://" . $_SERVER['HTTP_HOST'].'/';
	}
	

	static public function getPageURL()
	{
		$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
		if ($_SERVER["SERVER_PORT"] != "80")
		{
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		}
		else
		{
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
	static public function replaceURL($text)
	{
		return  preg_replace_callback(
			array(
					'/(?(?=<a[^>]*>.+<\/a>)
			             (?:<a[^>]*>.+<\/a>)
			             |
			             ([^="\']?)((?:https?|ftp|bf2|):\/\/[^<> \n\r]+)
			         )/iex',
									'/<a([^>]*)target="?[^"\']+"?/i',
									'/<a([^>]+)>/i',
									'/(^|\s)(www.[^<> \n\r]+)/iex',
									'/(([_A-Za-z0-9-]+)(\\.[_A-Za-z0-9-]+)*@([A-Za-z0-9-]+)
			       (\\.[A-Za-z0-9-]+)*)/iex'
			),
			array(
					"stripslashes((strlen('\\2')>0?'\\1<a href=\"\\2\">\\2</a>\\3':'\\0'))",
					'<a\\1',
					'<a\\1 target="_blank" rel="follow">',
					"stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\">\\2</a>\\3':'\\0'))",
					"stripslashes((strlen('\\2')>0?'<a href=\"mailto:\\0\">\\0</a>':'\\0'))"
			),
			$text
		);
	}
	
	public static function escapeString($value) {
		if (get_magic_quotes_gpc ()) {
			$value = stripslashes ( $value );
		}
		return function_exists ( 'mysql_real_escape_string' ) ? mysql_real_escape_string ( $value ) : mysql_escape_string ( $value );
	}

	public static function base64_url_encode($input) {
		return strtr(base64_encode($input), '+/=', '._-');
	}
	   
	public static function base64_url_decode($input) {
		return base64_decode(strtr($input, '._-', '+/='));
	}

	public static function getFileList($path)
	{
		$files = array_diff(scandir($path), array('.', '..'));
		return $files;
	}

	public static function attachCustomLinks(array $arrs, $type, $localeId)
	{
		$ids = array_column($arrs, 'id');
		$urls = RouterModel::factory()
			->whereIn('t1.foreign_id', $ids)
			->where('t1.type', $type)
			->where('t1.locale_id', $localeId)
			->findAll()
			->getDataPair ( 'foreign_id', 'url' );
		for($i = 0; $i < count($arrs); $i++) {
			$arrs[$i]['url'] = !empty($urls[$arrs[$i]['id']])? $urls[$arrs[$i]['id']] : null;
		}

		return $arrs;
	}

	public static function attachSingleCustomLink(array $item, $type, $localeId)
	{
	    if (!empty($item)) {
	        $id = $item['id'];
	        $router = RouterModel::factory()
	        ->where('t1.foreign_id', $id)
	        ->where('t1.type', $type)
	        ->where('t1.locale_id', $localeId)
	        ->limit(1)
	        ->findAll()
	        ->first();
	        $item['url'] = !empty($router['url'])? $router['url'] : '/';
	    }
	    return $item;
	}

	public static function getFirstImage($content) {
		$first_img = '';
		ob_start();
		ob_end_clean();
		$matches = [];
		preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
		$first_img = @$matches [1] [0];
	
		if(empty($first_img)){ //Defines a default image
			$first_img = "app/web/img/frontend/noimg.jpg";
		}
		return $first_img;
	}
	
	public static function attachVouchers($productArr, $type = null)
	{
	    // Get date and day
	    $date = date('Y-m-d H:i:s');
	    $time = date('H:i:s');
	    $day = date('l');
	    
	    $entryProData = array();
	    if ($type == 'm') {
	        $entryProData = VoucherModel::factory()->select("t1.id, t1.type, t1.discount, t1.code, t1.valid, CONCAT(t1.date_from, ' ', t1.time_from) AS dtFrom, CONCAT(t1.date_to, ' ', t1.time_to) AS dtTo, t1.time_from, t1.time_to, t1.every")
	        ->findAll()
	        ->getData();
	    } elseif ($type == 's') {
	        $entryProData = VoucherModel::factory()->select("t1.id, t1.type, t1.discount, t1.code, t1.valid, CONCAT(t1.date_from, ' ', t1.time_from) AS dtFrom, CONCAT(t1.date_to, ' ', t1.time_to) AS dtTo, t1.time_from, t1.time_to, t1.every, t2.product_id")
	        ->join('VoucherProduct', "t2.voucher_id = t1.id")
	        ->where('t2.product_id', $productArr['id'])
	        ->findAll()
	        ->getData();
	    } elseif ($type == 'c') {
	        $idArr = array_column($productArr, 'product_id');
	        $entryProData = VoucherModel::factory()->select("t1.id, t1.type, t1.discount,t1.code, t1.valid, CONCAT(t1.date_from, ' ', t1.time_from) AS dtFrom, CONCAT(t1.date_to, ' ', t1.time_to) AS dtTo, t1.time_from, t1.time_to, t1.every, t2.product_id")
	        ->join('VoucherProduct', "t2.voucher_id = t1.id")
	        ->whereIn('t2.product_id', $idArr)
	        ->findAll()
	        ->getData();
	    }
	    
	    $fixedArr = array();
	    $periodArr = array();
	    $recurringArr = array();
	    foreach ($entryProData as $value) {
	        switch ($value["valid"]) {
	            case "fixed":
	                if (strtotime($date) <= strtotime($value['dtFrom'])) {
	                    $fixedArr = VoucherModel::factory()->select("t1.type, t1.valid, t1.discount, t1.code, t2.product_id")
	                    ->join('VoucherProduct', "t2.voucher_id = t1.id")
	                    ->where('t1.valid', "fixed");
	                    
	                    // set data with type
	                    if ($type == 'm') {
	                        $fixedArr = $fixedArr->findAll()->getData();
	                    } elseif ($type == 's') {
	                        $fixedArr = $fixedArr->where('t2.product_id', $productArr['id'])
	                        ->findAll()
	                        ->getData();
	                    } elseif ($type == 'c') {
	                        $idArr = array_column($productArr, 'product_id');
	                        $fixedArr = $fixedArr->whereIn('t2.product_id', $idArr)
	                        ->findAll()
	                        ->getData();
	                    }
	                }
	                break;
	            case "period":
	                if (strtotime($date) >= strtotime($value['dtFrom']) && strtotime($date) <= strtotime($value['dtTo'])) {
	                    $periodArr = VoucherModel::factory()->select("t1.type, t1.valid, t1.discount, t1.code, t2.product_id")
	                    ->join('VoucherProduct', "t2.voucher_id = t1.id")
	                    ->where('t1.valid', "period");
	                    
	                    // set data with type
	                    if ($type == 'm') {
	                        $periodArr = $periodArr->findAll()->getData();
	                    } elseif ($type == 's') {
	                        $periodArr = $periodArr->where('t2.product_id', $productArr['id'])
	                        ->findAll()
	                        ->getData();
	                    } elseif ($type == 'c') {
	                        $idArr = array_column($productArr, 'product_id');
	                        $periodArr = $periodArr->whereIn('t2.product_id', $idArr)
	                        ->findAll()
	                        ->getData();
	                    }
	                }
	                break;
	            case "recurring":
	                if (strtolower($day) == strtolower($value['every']) && strtotime($time) >= strtotime($value['time_from'])) {
	                    $recurringArr = VoucherModel::factory()->select("t1.type, t1.valid, t1.discount, t1.code, t2.product_id")
	                    ->join('VoucherProduct', "t2.voucher_id = t1.id")
	                    ->where('t1.valid', "recurring");
	                    
	                    // set data with type
	                    if ($type == 'm') {
	                        $recurringArr = $recurringArr->findAll()->getData();
	                    } elseif ($type == 's') {
	                        $recurringArr = $recurringArr->where('t2.product_id', $productArr['id'])
	                        ->findAll()
	                        ->getData();
	                    } elseif ($type == 'c') {
	                        $idArr = array_column($productArr, 'product_id');
	                        $recurringArr = $recurringArr->whereIn('t2.product_id', $idArr)
	                        ->findAll()
	                        ->getData();
	                    }
	                }
	                break;
	            default:
	                break;
	        }
	    }
	    
	    $mergeArr = array_merge($fixedArr, $periodArr);
	    $mergeArr = array_merge($mergeArr, $recurringArr);
	    
	    foreach ($mergeArr as $key => $val) {
	        switch ($val['valid']) {
	            case "period":
	                if (in_array($val['product_id'], array_column($fixedArr, 'product_id'))) {
	                    unset($mergeArr[$key]);
	                }
	                break;
	            case "recurring":
	                if (in_array($val['product_id'], array_column($fixedArr, 'product_id')) || in_array($val['product_id'], array_column($periodArr, 'product_id'))) {
	                    unset($mergeArr[$key]);
	                }
	                break;
	            default:
	                break;
	        }
	    }
	    
	    
	    if (count($mergeArr) > 0) {
	        if ($type == 'm') {
	            for ($i = 0; $i < count($productArr); $i ++) {
	                foreach ($mergeArr as $k => $v) {
	                    if ($productArr[$i]['id'] == $v['product_id']) {
	                        $productArr[$i]['type_sale'] = $v['type'];
	                        $productArr[$i]['discount_sale'] = $v['discount'];
	                        $productArr[$i]['name_sale'] = $v['code'];
	                    }
	                }
	            }
	        } elseif ($type == 's') {
	            foreach ($mergeArr as $k => $v) {
	                if ($productArr['id'] == $v['product_id']) {
	                    $productArr['type_sale'] = $v['type'];
	                    $productArr['discount_sale'] = $v['discount'];
	                    $productArr['name_sale'] = $v['code'];
	                }
	            }
	        } elseif ($type == 'c') {
	            foreach ($productArr as $key => $value) {
	                foreach($mergeArr as $k => $v) {
	                    if ($productArr[$key]['product_id'] == $v['product_id']) {
	                        $productArr[$key]['type_sale'] = $v['type'];
	                        $productArr[$key]['discount_sale'] = $v['discount'];
	                        $productArr[$key]['name_sale'] = $v['code'];
	                    }
	                }
	            }
	        }
	    }
	    
	    return $productArr;
	}
	
	static public function getDateFormat()
	{
	    return array(
	        'd.m.Y' => 'd.m.Y (25.09.2012)',
	        'm.d.Y' => 'm.d.Y (09.25.2012)',
	        'Y.m.d' => 'Y.m.d (2012.09.25)',
	        'j.n.Y' => 'j.n.Y (25.9.2012)',
	        'n.j.Y' => 'n.j.Y (9.25.2012)',
	        'Y.n.j' => 'Y.n.j (2012.9.25)',
	        'd/m/Y' => 'd/m/Y (25/09/2012)',
	        'm/d/Y' => 'm/d/Y (09/25/2012)',
	        'Y/m/d' => 'Y/m/d (2012/09/25)',
	        'j/n/Y' => 'j/n/Y (25/9/2012)',
	        'n/j/Y' => 'n/j/Y (9/25/2012)',
	        'Y/n/j' => 'Y/n/j (2012/9/25)',
	        'd-m-Y' => 'd-m-Y (25-09-2012)',
	        'm-d-Y' => 'm-d-Y (09-25-2012)',
	        'Y-m-d' => 'Y-m-d (2012-09-25)',
	        'j-n-Y' => 'j-n-Y (25-9-2012)',
	        'n-j-Y' => 'n-j-Y (9-25-2012)',
	        'Y-n-j' => 'Y-n-j (2012-9-25)'
	    );
	}
	
	static public function getFontSizes()
	{
	    return array(
	        '8' => '8',
	        '10' => '10',
	        '11' => '11',
	        '12' => '12',
	        '14' => '14',
	        '16' => '16',
	        '18' => '18',
	        '20' => '20',
	        '22' => '22',
	        '24' => '24'
	    );
	}
	
	static public function getFields()
	{
	    return array(
	        'heading' => 'Heading',
	        'textbox' => 'Text Box',
	        'email' => 'Email',
	        'textarea' => 'Text Area',
	        'dropdown' => 'Drop Down',
	        'radio' => 'Radio Button',
	        'checkbox' => 'Check Box',
	        'fileupload' => 'File Upload',
	        'datepicker' => 'Date Picker',
	        'captcha' => 'Captcha',
	        'button' => 'Submit Button'
	    );
	}
	
	static public function getFonts()
	{
	    return array(
	        'Arial' => 'Arial',
	        'Courier' => 'Courier',
	        'Courier New' => 'Courier New',
	        'Comic Sans MS' => 'Comic Sans MS',
	        'Gill Sans' => 'Gill Sans',
	        'Helvetica' => 'Helvetica',
	        'Lucida' => 'Lucida',
	        'Lucida Grande' => 'Lucida Grande',
	        'Trebuchet MS' => 'Trebuchet MS',
	        'Tahoma' => 'Tahoma',
	        'Times New Roman' => 'Times New Roman',
	        'Verdana' => 'Verdana'
	    );
	}
	
	public static function printInstallNotice($title, $body, $convert = true, $close = true)
	{
	    ?>
		<div class="install-notice-box">
			<div class="notice-top"></div>
			<div class="notice-middle">
				<span class="notice-info">&nbsp;</span>
				<?php
				if (!empty($title))
				{
					printf('<span class="block bold">%s</span>', $convert ? htmlspecialchars(stripslashes($title)) : stripslashes($title));
				}
				if (!empty($body))
				{
					printf('<span class="block">%s</span>', $convert ? htmlspecialchars(stripslashes($body)) : stripslashes($body));
				}
				if ($close)
				{
					?><a href="#" class="notice-close"></a><?php
				}
				?>
			</div>
			<div class="notice-bottom"></div>
		</div>
		<?php
	}
	
	public static function addHttp($url) {
	    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
	        $url = "http://" . $url;
	    }
	    return $url;
	}
	
	public static function getFrontendTranslation($key)
	{
	    $jsonPath = THEME_PATH_PUBLIC.'/configs/config.json';
	    $string = file_get_contents($jsonPath);
	    $json_arr = json_decode($string, true);
	    $defaultLang = $json_arr['default_lang'];
	    $locale = LocaleModel::factory()->where('language_iso', $defaultLang)->findAll()->limit(1)->first();
	    $languages = $json_arr['languages'];
	    $_SESSION[FONTEND_TRANS_DICT]['languages'] = $languages;
	    foreach ($languages as $lang) {
	        $langJsonPath = THEME_PATH_PUBLIC."/languages/$lang.json";
	        $langString = file_get_contents($langJsonPath);
	        $lang_arr = json_decode($langString, true);
	        $_SESSION[FONTEND_TRANS_DICT]['translations'][$lang] = $lang_arr;
	    }
	    if (empty( $_SESSION[FONTEND_TRANS_DICT]['locale_id'])) {
	        $_SESSION[FONTEND_TRANS_DICT]['locale_id'] = $locale['id'];
	    }
	    if (empty( $_SESSION[FONTEND_TRANS_DICT]['lang'])) {
	        $_SESSION[FONTEND_TRANS_DICT]['lang'] = $locale['language_iso'];
	    }
	    
	    if (empty( $_SESSION[FONTEND_TRANS_DICT]['default_lang'])) {
	        $_SESSION[FONTEND_TRANS_DICT]['default_lang'] = $locale['language_iso'];
	    }
	    $frontendLang = !empty($_SESSION[FONTEND_TRANS_DICT]['lang'])? $_SESSION[FONTEND_TRANS_DICT]['lang'] : $_SESSION[FONTEND_TRANS_DICT]['default_lang'];
	    return !empty($_SESSION[FONTEND_TRANS_DICT]['translations'][$frontendLang][$key])? $_SESSION[FONTEND_TRANS_DICT]['translations'][$frontendLang][$key] : NULL;
	    
	}
		
}
?>