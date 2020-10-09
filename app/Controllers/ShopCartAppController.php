<?php
namespace App\Controllers;

use App\Models\AppModel;
use App\Models\OptionModel;
use App\Plugins\Locale\Models\LocaleModel;
use App\Models\VoucherModel;
use App\Models\VoucherProductModel;
use App\Controllers\Components\UtilComponent;
use App\Models\MultiLangModel;
use Core\Framework\Registry;
use Core\Framework\Components\ServicesJSONComponent;
use Core\Framework\Components\MultibyteComponent;
use App\Models\HistoryModel;
use App\Models\OrderStockModel;
use App\Models\ExtraModel;
use App\Models\ExtraItemModel;
use App\Models\AttributeModel;
use App\Models\StockAttributeModel;
use App\Models\OrderExtraModel;
use App\Models\TaxModel;
use App\Models\OrderModel;
use Core\Framework\Components\SanitizeComponent;
use App\Models\FieldModel;
use App\Plugins\Invoice\Models\InvoiceConfigModel;

class ShopCartAppController extends AppController
{

    public $models = array();

    public $defaultLocale = 'admin_locale_id';

    private $layoutRange = array(
        1,
        2,
        3
    );

    public $defaultFields = 'fields';

    public $defaultFieldsIndex = 'fields_index';

    protected function loadSetFields($force = false)
    {
        $registry = Registry::getInstance();
        if (! isset($_SESSION[$this->defaultFieldsIndex]) || $_SESSION[$this->defaultFieldsIndex] != $this->option_arr['o_fields_index'] || ! isset($_SESSION[$this->defaultFields]) || empty($_SESSION[$this->defaultFields])) {
            AppController::setFields($this->getFrontendLocaleId());
            if ($registry->is('fields')) {
                $_SESSION[$this->defaultFields] = $registry->get('fields');
            }
            $_SESSION[$this->defaultFieldsIndex] = $this->option_arr['o_fields_index'];
        }
        if (isset($_SESSION[$this->defaultFields]) && ! empty($_SESSION[$this->defaultFields])) {
            $registry->set('fields', $_SESSION[$this->defaultFields]);
        }
        return TRUE;
    }

    public function getLayoutRange()
    {
        return $this->layoutRange;
    }

    public function isInvoiceReady()
    {
        return $this->isAdmin();
    }


    public function isOneAdminReady()
    {
        return $this->isAdmin();
    }


    public function isCountryReady()
    {
        return $this->isAdmin();
    }


    public function getModel($key)
    {
        if (array_key_exists($key, $this->models)) {
            return $this->models[$key];
        }
        return false;
    }

    public function setModel($key, $value)
    {
        $this->models[$key] = $value;
        return true;
    }


    public static function setTimezone($timezone = "UTC")
    {
        if (in_array(version_compare(phpversion(), '5.1.0'), array(
            0,
            1
        ))) {
            date_default_timezone_set($timezone);
        } else {
            $safe_mode = ini_get('safe_mode');
            if ($safe_mode) {
                putenv("TZ=" . $timezone);
            }
        }
    }

    public static function setMySQLServerTime($offset = "-0:00")
    {
        AppModel::factory()->prepare("SET SESSION time_zone = :offset;")->exec(array(
            'offset' => $offset
        ));
    }


    public function setTime()
    {
        if (isset($this->option_arr['o_timezone'])) {
            $offset = $this->option_arr['o_timezone'] / 3600;
            if ($offset > 0) {
                $offset = "-" . $offset;
            } elseif ($offset < 0) {
                $offset = "+" . abs($offset);
            } elseif ($offset === 0) {
                $offset = "+0";
            }
            AppController::setTimezone('Etc/GMT' . $offset);
            if (strpos($offset, '-') !== false) {
                $offset = str_replace('-', '+', $offset);
            } elseif (strpos($offset, '+') !== false) {
                $offset = str_replace('+', '-', $offset);
            }
            AppController::setMySQLServerTime($offset . ":00");
        }
    }

    public function beforeFilter()
    {
        $this->appendJs('jquery-1.8.2.min.js', THIRD_PARTY_PATH . 'jquery/');
        $this->appendJs('AdminCore.js');
        $this->appendCss('reset.css');
        $this->appendJs('jquery-ui.custom.min.js', THIRD_PARTY_PATH . 'jquery_ui/js/');
        $this->appendCss('jquery-ui.min.css', THIRD_PARTY_PATH . 'jquery_ui/css/smoothness/');
        $this->appendCss('admin.css');
        $this->appendCss('-all.css', FRAMEWORK_LIBS_PATH . 'css/');
        if ($_GET['controller'] != 'Installer') {
            $this->setModel('Option', OptionModel::factory());
            $this->option_arr = $this->getModel('Option')->getPairs($this->getForeignId());
            $this->set('option_arr', $this->option_arr);
            $this->setTime();
            if (! isset($_SESSION[$this->defaultLocale])) {
                $locale_arr = LocaleModel::factory()->where('is_default', 1)
                    ->limit(1)
                    ->findAll()
                    ->getData();
                if (count($locale_arr) === 1) {
                    $this->setLocaleId($locale_arr[0]['id']);
                }
            }
            $this->loadSetFields(true);
        }
    }

    public static function getDiscount($data, $option_arr)
    {
        if (! isset($data['code']) || empty($data['code'])) {
            return array(
                'status' => 'ERR',
                'code' => 100,
                'text' => 'Voucher code couldn\'t be empty.'
            );
        }
        $arr = VoucherModel::factory()->select(sprintf("t1.*, (SELECT GROUP_CONCAT(`product_id`) FROM `%s` WHERE `voucher_id` = `t1`.`id` LIMIT 1) AS `products`", VoucherProductModel::factory()->getTable()))
            ->where('t1.code', $data['code'])
            ->limit(1)
            ->findAll()
            ->toArray('products', ',')
            ->getData();
        if (empty($arr)) {
            return array(
                'status' => 'ERR',
                'code' => 101,
                'text' => __('system_133', true)
            );
        }
        $arr = $arr[0];
        $date = $data['date'];
        if (isset($data['hour']) && isset($data['minute'])) {
            $time = $data['hour'] . ":" . $data['minute'] . ":00";
        }
        if (! isset($time)) {
            $time = "00:00:00";
        }
        if (empty($date)) {
            return array(
                'status' => 'ERR',
                'code' => 103,
                'text' => 'Date couldn\'t be empty.'
            );
        }
        $d = strtotime($date);
        $dt = strtotime($date . " " . $time);
        $valid = false;
        switch ($arr['valid']) {
            case 'fixed':
                $time_from = strtotime($arr['date_from'] . " " . $arr['time_from']);
                $time_to = strtotime($arr['date_to'] . " " . $arr['time_to']);
                if ($time_from <= $dt && $time_to >= $dt) {
                    $valid = true;
                }
                break;
            case 'period':
                $d_from = strtotime($arr['date_from']);
                $d_to = strtotime($arr['date_to']);
                $t_from = strtotime($arr['date_from'] . " " . $arr['time_from']);
                $t_to = strtotime($arr['date_to'] . " " . $arr['time_to']);
                if ($d_from <= $d && $d_to >= $d && $t_from <= $dt && $t_to >= $dt) {
                    $valid = true;
                }
                break;
            case 'recurring':
                $t_from = strtotime($date . " " . $arr['time_from']);
                $t_to = strtotime($date . " " . $arr['time_to']);
                if ($arr['every'] == strtolower(date("l", $dt)) && $t_from <= $dt && $t_to >= $dt) {
                    $valid = true;
                }
                break;
        }
        if (! $valid) {
            return array(
                'status' => 'ERR',
                'code' => 102,
                'text' => 'Voucher code is out of date.'
            );
        }
        return array(
            'status' => 'OK',
            'code' => 200,
            'text' => 'Voucher code has been applied.',
            'voucher_code' => $arr['code'],
            'voucher_type' => $arr['type'],
            'voucher_discount' => $arr['discount'],
            'voucher_products' => $arr['products']
        );
    }

    
    
    public function getForeignId()
    {
        return 1;
    }

    public static function setFields($locale)
    {
        if (isset($_SESSION['lang_show_id']) && (int) $_SESSION['lang_show_id'] == 1) {
            $fields = MultiLangModel::factory()->select('CONCAT(t1.content, CONCAT(":", t2.id, ":")) AS content, t2.key')
                ->join('Field', "t2.id=t1.foreign_id", 'inner')
                ->where('t1.locale', $locale)
                ->where('t1.model', 'Field')
                ->where('t1.field', 'title')
                ->findAll()
                ->getDataPair('key', 'content');
        } else {
            $fields = MultiLangModel::factory()->select('t1.content, t2.key')
                ->join('Field', "t2.id=t1.foreign_id", 'inner')
                ->where('t1.locale', $locale)
                ->where('t1.model', 'Field')
                ->where('t1.field', 'title')
                ->findAll()
                ->getDataPair('key', 'content');
        }
        $registry = Registry::getInstance();
        $tmp = array();
        if ($registry->is('fields')) {
            $tmp = $registry->get('fields');
        }
        $arrays = array();
        foreach ($fields as $key => $value) {
            if (strpos($key, '_ARRAY_') !== false) {
                list ($prefix, $suffix) = explode("_ARRAY_", $key);
                if (! isset($arrays[$prefix])) {
                    $arrays[$prefix] = array();
                }
                $arrays[$prefix][$suffix] = $value;
            }
        }
        require CONFIG_PATH . 'settings.inc.php';
        $fields = array_merge($tmp, $fields, $settings, $arrays);
        $registry->set('fields', $fields);
    }

    public static function jsonDecode($str)
    {
        $Services_JSON = new ServicesJSONComponent();
        return $Services_JSON->decode($str);
    }


    public static function jsonEncode($arr)
    {
        $Services_JSON = new ServicesJSONComponent();
        return $Services_JSON->encode($arr);
    }


    public static function jsonResponse($arr)
    {
        header("Content-Type: application/json; charset=utf-8");
        echo AppController::jsonEncode($arr);
        exit();
    }

    public function getLocaleId()
    {
        return isset($_SESSION[$this->defaultLocale]) && (int) $_SESSION[$this->defaultLocale] > 0 ? (int) $_SESSION[$this->defaultLocale] : false;
    }

    public function setLocaleId($locale_id)
    {
        $_SESSION[$this->defaultLocale] = (int) $locale_id;
    }

    public function CheckInstall()
    {
        $this->setLayout('ActionEmpty');
        $result = array(
            'status' => 'OK',
            'code' => 200,
            'text' => 'Operation succeeded',
            'info' => array()
        );
        $folders = array(
            'app/web/upload/digital'
        );
        foreach ($folders as $dir) {
            if (! is_writable($dir)) {
                $result['status'] = 'ERR';
                $result['code'] = 101;
                $result['text'] = 'Permission requirement';
                $result['info'][] = sprintf('Folder \'<span class="bold">%1$s</span>\' is not writable. You need to set write permissions (chmod 777) to directory located at \'<span class="bold">%1$s</span>\'', $dir);
            }
        }
        return $result;
    }

    public static function friendlyURL($str, $divider = '-')
    {
        $str = MultibyteComponent::strtolower($str);
        $str = trim($str);
        $str = preg_replace('/[_|\s]+/', $divider, $str);
        $str = preg_replace('/\x{00C5}/u', 'AA', $str);
        $str = preg_replace('/\x{00C6}/u', 'AE', $str);
        $str = preg_replace('/\x{00D8}/u', 'OE', $str);
        $str = preg_replace('/\x{00E5}/u', 'aa', $str);
        $str = preg_replace('/\x{00E6}/u', 'ae', $str);
        $str = preg_replace('/\x{00F8}/u', 'oe', $str);
        $str = preg_replace('/[^a-z\x{0400}-\x{04FF}0-9-]+/u', '', $str);
        $str = preg_replace('/[-]+/', $divider, $str);
        $str = preg_replace('/^-+|-+$/', '', $str);
        return $str;
    }

    public static function addToHistory($record_id, $user_id, $table, $before, $after)
    {
        return HistoryModel::factory()->setAttributes(array(
            'record_id' => $record_id,
            'user_id' => $user_id,
            'table_name' => $table,
            'before' => base64_encode(serialize($before)),
            'after' => base64_encode(serialize($after)),
            'ip' => $_SERVER['REMOTE_ADDR']
        ))
            ->insert()
            ->getInsertId();
    }

    public static function getTokens($order_arr, $option_arr)
    {
        $search = array(
            '{BillingName}',
            '{BillingCountry}',
            '{BillingCity}',
            '{BillingState}',
            '{BillingZip}',
            '{BillingAddress1}',
            '{BillingAddress2}',
            '{ShippingName}',
            '{ShippingCountry}',
            '{ShippingCity}',
            '{ShippingState}',
            '{ShippingZip}',
            '{ShippingAddress1}',
            '{ShippingAddress2}',
            '{ClientName}',
            '{ClientEmail}',
            '{ClientPassword}',
            '{ClientPhone}',
            '{ClientURL}',
            '{CCType}',
            '{CCNum}',
            '{CCExpMonth}',
            '{CCExpYear}',
            '{CCSec}',
            '{PaymentMethod}',
            '{Price}',
            '{Discount}',
            '{Insurance}',
            '{Shipping}',
            '{Tax}',
            '{Total}',
            '{Voucher}',
            '{Notes}',
            '{OrderID}',
            '{OrderUUID}',
            '{DigitalDownload}',
            '{Products}',
            '{StoreName}'
        );
        $cc_num = NULL;
        $cc_num_length = strlen($order_arr['cc_num']);
        if (! empty($order_arr['cc_num']) && $cc_num_length > 4) {
            $multiplier = $cc_num_length - 4;
            $multiplier = $multiplier > 0 ? $multiplier : 11;
            $cc_num = sprintf("%s%s%s", substr($order_arr['cc_num'], 0, 2), str_repeat(".", $multiplier), substr($order_arr['cc_num'], - 2));
        }
        $digital_download = __('front_na', true);
        if ($order_arr['has_digital'] == true) {
            $digital_download = sprintf("%sindex.php?controller=BaseShopCart&action=DigitalDownload&uuid=%s&hash=%s", BASE_URL, $order_arr['uuid'], md5($order_arr['uuid'] . SALT));
        }
        $replace = array(
            $order_arr['b_name'],
            @$order_arr['b_country'],
            $order_arr['b_city'],
            $order_arr['b_state'],
            $order_arr['b_zip'],
            $order_arr['b_address_1'],
            $order_arr['b_address_2'],
            $order_arr['s_name'],
            @$order_arr['s_country'],
            $order_arr['s_city'],
            $order_arr['s_state'],
            $order_arr['s_zip'],
            $order_arr['s_address_1'],
            $order_arr['s_address_2'],
            $order_arr['client_name'],
            @$order_arr['email'],
            $order_arr['password'],
            @$order_arr['phone'],
            @$order_arr['url'],
            $order_arr['cc_type'],
            $cc_num,
            ($order_arr['payment_method'] == 'creditcard' ? $order_arr['cc_exp_month'] : NULL),
            ($order_arr['payment_method'] == 'creditcard' ? $order_arr['cc_exp_year'] : NULL),
            $order_arr['cc_code'],
            $order_arr['payment_method'],
            $order_arr['price'] . " " . $option_arr['o_currency'],
            $order_arr['discount'] . " " . $option_arr['o_currency'],
            $order_arr['insurance'] . " " . $option_arr['o_currency'],
            $order_arr['shipping'] . " " . $option_arr['o_currency'],
            $order_arr['tax'] . " " . $option_arr['o_currency'],
            $order_arr['total'] . " " . $option_arr['o_currency'],
            $order_arr['voucher'],
            $order_arr['notes'],
            $order_arr['id'],
            $order_arr['uuid'],
            $digital_download,
            @$order_arr['products'],
            __('lblStoreName', true)
        );
        return compact('search', 'replace');
    }

    public static function CheckDigital($order_id)
    {
        $has_digital = false;
        $digitals = array();
        $os_arr = OrderStockModel::factory()->select('t2.digital_file, t2.digital_name, t2.is_digital')
            ->join('Product', "t2.id=t1.product_id AND t2.is_digital='1'", 'inner')
            ->where('t1.order_id', $order_id)
            ->findAll()
            ->getData();
        foreach ($os_arr as $item) {
            $digitals[] = $item;
        }
        foreach ($digitals as $file) {
            if (! empty($file['digital_file']) && is_file($file['digital_file'])) {
                $has_digital = true;
                break;
            }
        }
        return $has_digital;
    }

    public static function GetExtrasList($product_id, $locale_id)
    {
        $ExtraItemModel = ExtraItemModel::factory();
        $ExtraModel = ExtraModel::factory();
        if (! empty($product_id)) {
            $ExtraModel->whereIn('t1.product_id', $product_id);
        }
        $extra_arr = $ExtraModel->select('t1.*, t2.content AS name, t3.content AS title')
            ->join('MultiLang', "t2.model='Extra' AND t2.foreign_id=t1.id AND t2.locale='$locale_id' AND t2.field='extra_name'", 'left outer')
            ->join('MultiLang', "t3.model='Extra' AND t3.foreign_id=t1.id AND t3.locale='$locale_id' AND t3.field='extra_title'", 'left outer')
            ->orderBy('`title` ASC, `name` ASC')
            ->findAll()
            ->getData();
        foreach ($extra_arr as $k => $extra) {
            $extra_arr[$k]['extra_items'] = $ExtraItemModel->reset()
                ->select('t1.*, t2.content AS name')
                ->join('MultiLang', "t2.model='ExtraItem' AND t2.foreign_id=t1.id AND t2.locale='$locale_id' AND t2.field='extra_name'", 'left outer')
                ->where('t1.extra_id', $extra['id'])
                ->orderBy('t1.price ASC')
                ->findAll()
                ->getData();
        }
        return $extra_arr;
    }

    public static function GetAttr($product_id, $locale_id)
    {
        $attr_arr = $a_arr = array();
        if (! empty($product_id)) {
            $a_arr = AttributeModel::factory()->select('t1.id, t1.product_id, t1.parent_id, t2.content AS name')
                ->join('MultiLang', "t2.model='Attribute' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='$locale_id'", 'left outer')
                ->whereIn('t1.product_id', $product_id)
                ->orderBy('t1.parent_id ASC, `name` ASC')
                ->findAll()
                ->getData();
        }
        foreach ($a_arr as $attr) {
            if ((int) $attr['parent_id'] === 0) {
                $attr_arr[$attr['id']] = $attr;
            } else {
                if (! isset($attr_arr[$attr['parent_id']]['child'])) {
                    $attr_arr[$attr['parent_id']]['child'] = array();
                }
                $attr_arr[$attr['parent_id']]['child'][] = $attr;
            }
        }
        $attr_arr = array_values($attr_arr);
        return $attr_arr;
    }


    public static function GetOrderStock($order_id, $locale_id)
    {
        $os_arr = OrderStockModel::factory()->select("t1.*, t2.sku, t3.content AS name,  (SELECT GROUP_CONCAT(CONCAT_WS('_', `attribute_id`, `attribute_parent_id`))  FROM `" . StockAttributeModel::factory()->getTable() . "`  WHERE `stock_id` = `t1`.`stock_id`  LIMIT 1) AS `attr`,  (SELECT GROUP_CONCAT(CONCAT_WS('.', `extra_id`, `extra_item_id`))  FROM `" . OrderExtraModel::factory()->getTable() . "`  WHERE `order_stock_id` = `t1`.`id`  LIMIT 1) AS `extra`")
            ->join('Product', 't2.id=t1.product_id', 'left outer')
            ->join('MultiLang', "t3.model='Product' AND t3.foreign_id=t2.id AND t3.field='name' AND t3.locale='$locale_id'", 'left outer')
            ->where('t1.order_id', $order_id)
            ->findAll()
            ->getData();
        $product_id = array();
        foreach ($os_arr as $item) {
            $product_id[] = $item['product_id'];
        }
        $extra_arr = BaseShopCartController::GetExtrasList($product_id, $locale_id);
        $attr_arr = BaseShopCartController::GetAttr($product_id, $locale_id);
        return compact('os_arr', 'extra_arr', 'attr_arr');
    }

    public static function GetProductsString($order_id, $locale_id)
    {
        $result = BaseShopCartController::GetOrderStock($order_id, $locale_id);
        if (! isset($result['os_arr']) || empty($result['os_arr'])) {
            return '';
        }
        $stack = array();
        foreach ($result['os_arr'] as $item) {
            $stack[] = sprintf("%s x %u", $item['name'], (int) $item['qty']);
            $attrs = array();
            if (isset($item['attr']) && ! empty($item['attr'])) {
                $at = array();
                $a = explode(",", $item['attr']);
                foreach ($a as $v) {
                    $t = explode("_", $v);
                    $at[$t[1]] = $t[0];
                }
                foreach ($at as $attr_parent_id => $attr_id) {
                    foreach ($result['attr_arr'] as $attr) {
                        if ($attr['id'] == $attr_parent_id) {
                            foreach ($attr['child'] as $child) {
                                if ($child['id'] == $attr_id) {
                                    $attrs[] = sprintf('%s: %s', $attr['name'], $child['name']);
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            if (! empty($attrs)) {
                $stack[] = join("; ", $attrs);
            }
            $extras = array();
            if (isset($item['extra']) && ! empty($item['extra'])) {
                $a = explode(",", $item['extra']);
                foreach ($a as $eid) {
                    if (strpos($eid, ".") === FALSE) {
                        foreach ($result['extra_arr'] as $extra) {
                            if ($extra['id'] == $eid) {
                                $extras[] = $extra['name'];
                                break;
                            }
                        }
                    } else {
                        list ($e_id, $ei_id) = explode(".", $eid);
                        foreach ($result['extra_arr'] as $extra) {
                            if ($extra['id'] == $e_id && isset($extra['extra_items']) && ! empty($extra['extra_items'])) {
                                foreach ($extra['extra_items'] as $extra_item) {
                                    if ($extra_item['id'] == $ei_id) {
                                        $extras[] = $extra_item['name'];
                                        break;
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
            }
            if (! empty($extras)) {
                $stack[] = join("; ", $extras);
            }
        }
        return join("\n", $stack);
    }


    public static function CalcPrices($product_id, $extra_arr, $cart_arr, $stocks, $voucher, $option_arr, $tax_id, $from)
    {
        $price = $discount = $tax = $shipping = $insurance = $total = 0;
        foreach ($cart_arr as $cart_item) {
            if ($from == 'front') {
                if ($cart_item['qty'] > @$stocks[$cart_item['stock_id']]['qty']) {
                    return false;
                    break;
                }
                $amount = @$stocks[$cart_item['stock_id']]['price'] * $cart_item['qty'];
                $item = unserialize($cart_item['key_data']);
                if (isset($item['extra']) && is_array($item['extra'])) {
                    $extras = array();
                    foreach ($item['extra'] as $extra) {
                        if (strpos($extra, ".") !== FALSE) {
                            list ($extras['extra_id'], $extras['extra_item_id']) = explode(".", $extra);
                            $amount += @$extra_arr[$extras['extra_id']]['extra_items'][$extras['extra_item_id']] * $cart_item['qty'];
                        } else {
                            $amount += @$extra_arr[$extra]['price'] * $cart_item['qty'];
                        }
                    }
                }
            } else {
                $oe_arr = OrderExtraModel::factory()->where('t1.order_id', $product_id)
                    ->findAll()
                    ->getData();
                $amount = $cart_item['qty'] * $cart_item['price'];
                foreach ($oe_arr as $oe_item) {
                    if ($cart_item['id'] == $oe_item['order_stock_id']) {
                        $amount += $oe_item['price'] * $cart_item['qty'];
                    }
                }
            }
            $price += $amount;
            $discount += UtilComponent::getDiscount($amount, $cart_item['product_id'], @$voucher);
        }
        if ($tax_id != null && (int) $tax_id > 0) {
            $tax_arr = TaxModel::factory()->find($tax_id)->getData();
            if (! empty($tax_arr)) {
                $shipping = (float) $tax_arr['shipping'];
                if ((float) $tax_arr['free'] > 0 && (float) $price >= (float) $tax_arr['free']) {
                    $shipping = 0;
                }
                if ((float) $tax_arr['tax'] > 0) {
                    $tax = (($price - $discount) * (float) $tax_arr['tax']) / 100;
                }
            }
        }
        switch ($option_arr['o_insurance_type']) {
            case 'percent':
                $insurance = (($price - $discount) * (float) $option_arr['o_insurance']) / 100;
                break;
            case 'amount':
                $insurance = (float) $option_arr['o_insurance'];
                break;
            default:
                $insurance = 0;
        }
        $total = $price + $shipping + $tax + $insurance - $discount;
        return compact('price', 'tax', 'shipping', 'insurance', 'discount', 'total');
    }
    
    protected function GenerateInvoice($order_id)
    {
        if (! isset($order_id) || (int) $order_id <= 0) {
            return array(
                'status' => 'ERR',
                'code' => 400,
                'text' => 'ID is not set ot invalid.'
            );
        }
        $arr = OrderModel::factory()->select('t1.*, t2.email, t2.phone, t2.url')
            ->join('Client', 't2.id=t1.client_id', 'left outer')
            ->find($order_id)
            ->getData();
        if (empty($arr)) {
            return array(
                'status' => 'ERR',
                'code' => 404,
                'text' => 'Order not found.'
            );
        }
        $stack = BaseShopCartController::GetOrderStock($arr['id'], $arr['locale_id']);
        $items = array();
        if (isset($stack['os_arr']) && ! empty($stack['os_arr'])) {
            $total = 0;
            foreach ($stack['os_arr'] as $i => $item) {
                $desc = array();
                $extra_price = 0;
                if (isset($item['attr']) && ! empty($item['attr'])) {
                    $at = array();
                    $a = explode(",", $item['attr']);
                    foreach ($a as $v) {
                        $t = explode("_", $v);
                        $at[$t[1]] = $t[0];
                    }
                    foreach ($at as $attr_parent_id => $attr_id) {
                        foreach ($stack['attr_arr'] as $attr) {
                            if ($attr['id'] == $attr_parent_id) {
                                foreach ($attr['child'] as $child) {
                                    if ($child['id'] == $attr_id) {
                                        $desc[] = sprintf('%s: %s', $attr['name'], SanitizeComponent::html($child['name']));
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                if (isset($item['extra']) && ! empty($item['extra'])) {
                    $a = explode(",", $item['extra']);
                    foreach ($a as $eid) {
                        if (strpos($eid, ".") === FALSE) {
                            foreach ($stack['extra_arr'] as $extra) {
                                if ($extra['id'] == $eid) {
                                    $desc[] = sprintf('Extra: %s (%s)', $extra['name'], UtilComponent::formatCurrencySign(number_format($extra['price'], 2), $this->option_arr['o_currency']));
                                    $extra_price += $extra['price'];
                                    break;
                                }
                            }
                        } else {
                            list ($e_id, $ei_id) = explode(".", $eid);
                            foreach ($stack['extra_arr'] as $extra) {
                                if ($extra['id'] == $e_id && isset($extra['extra_items']) && ! empty($extra['extra_items'])) {
                                    foreach ($extra['extra_items'] as $extra_item) {
                                        if ($extra_item['id'] == $ei_id) {
                                            $desc[] = sprintf('Extra: %s (%s)', $extra_item['name'], UtilComponent::formatCurrencySign(number_format($extra_item['price'], 2), $this->option_arr['o_currency']));
                                            $extra_price += $extra_item['price'];
                                            break;
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
                $price = $item['price'] + $extra_price;
                $subtotal = $price * (int) $item['qty'];
                $total += $subtotal;
                $items[] = array(
                    'name' => $item['name'],
                    'description' => join("; ", $desc),
                    'qty' => (int) $item['qty'],
                    'unit_price' => number_format($price, 2, ".", ""),
                    'amount' => number_format($subtotal, 2, ".", "")
                );
            }
            $items[] = array(
                'name' => __('order_insurance', true),
                'description' => NULL,
                'qty' => 1,
                'unit_price' => $arr['insurance'],
                'amount' => $arr['insurance']
            );
            $items[] = array(
                'name' => __('order_shipping', true),
                'description' => NULL,
                'qty' => 1,
                'unit_price' => $arr['shipping'],
                'amount' => $arr['shipping']
            );
        } else {
            $items[] = array(
                'name' => 'Order payment',
                'description' => "",
                'qty' => 1,
                'unit_price' => $arr['total'],
                'amount' => $arr['total']
            );
        }
        $map = array(
            'completed' => 'paid',
            'cancelled' => 'cancelled',
            'new' => 'not_paid',
            'pending' => 'not_paid'
        );
        $response = $this->requestAction(array(
            'controller' => 'Invoice',
            'action' => 'Create',
            'params' => array(
                'key' => md5($this->option_arr['private_key'] . SALT),
                'uuid' => UtilComponent::uuid(),
                'order_id' => $arr['uuid'],
                'foreign_id' => $this->getForeignId(),
                'issue_date' => ':CURDATE()',
                'due_date' => ':CURDATE()',
                'created' => ':NOW()',
                'status' => @$map[$arr['status']],
                'subtotal' => $arr['price'] + $arr['insurance'] + $arr['shipping'],
                'discount' => $arr['discount'],
                'tax' => $arr['tax'],
                'shipping' => $arr['shipping'],
                'total' => $arr['total'],
                'paid_deposit' => 0,
                'amount_due' => 0,
                'currency' => $this->option_arr['o_currency'],
                'notes' => $arr['notes'],
                'b_billing_address' => $arr['b_address_1'],
                'b_name' => $arr['b_name'],
                'b_address' => $arr['b_address_1'],
                'b_street_address' => $arr['b_address_2'],
                'b_city' => $arr['b_city'],
                'b_state' => $arr['b_state'],
                'b_zip' => $arr['b_zip'],
                'b_phone' => $arr['phone'],
                'b_email' => $arr['email'],
                'b_url' => $arr['url'],
                's_shipping_address' => (int) $arr['same_as'] === 1 ? $arr['b_address_1'] : $arr['s_address_1'],
                's_name' => (int) $arr['same_as'] === 1 ? $arr['b_name'] : $arr['s_name'],
                's_address' => (int) $arr['same_as'] === 1 ? $arr['b_address_1'] : $arr['s_address_1'],
                's_street_address' => (int) $arr['same_as'] === 1 ? $arr['b_address_2'] : $arr['s_address_2'],
                's_city' => (int) $arr['same_as'] === 1 ? $arr['b_city'] : $arr['s_city'],
                's_state' => (int) $arr['same_as'] === 1 ? $arr['b_state'] : $arr['s_state'],
                's_zip' => (int) $arr['same_as'] === 1 ? $arr['b_zip'] : $arr['s_zip'],
                's_phone' => $arr['phone'],
                's_email' => $arr['email'],
                's_url' => $arr['url'],
                'items' => $items
            )
        ), array(
            'return'
        ));
        return $response;
    }

    public function AfterInstall()
    {
        InvoiceConfigModel::factory()->set('id', 1)->modify(array(
            'o_booking_url' => "index.php?controller=AdminOrders&action=Update&uuid={ORDER_ID}"
        ));
        $query = sprintf("UPDATE `%s`  SET `content` = :content  WHERE `model` = :model  AND `foreign_id` = (SELECT `id` FROM `%s` WHERE `key` = :key LIMIT 1)  AND `field` = :field", MultiLangModel::factory()->getTable(), FieldModel::factory()->getTable());
        AppModel::factory()->prepare($query)->exec(array(
            'content' => 'Order URL - Token: {ORDER_ID}',
            'model' => 'Field',
            'field' => 'title',
            'key' => 'plugin_invoice_i_booking_url'
        ));
        $query = sprintf("UPDATE `%s`  SET `label` = :label  WHERE `key` = :key  LIMIT 1", FieldModel::factory()->getTable());
        AppModel::factory()->prepare($query)->exec(array(
            'label' => 'Invoice plugin / Order URL - Token: {ORDER_ID}',
            'key' => 'plugin_invoice_i_booking_url'
        ));
    }
}
?>