<?php
namespace App\Controllers;

use Core\Framework\Components\ValidationComponent;
use App\Models\CartModel;
use App\Models\AddressModel;
use App\Models\OrderModel;
use App\Models\CategoryModel;
use App\Models\ClientModel;
use Core\Framework\Components\CaptchaComponent;
use App\Controllers\Components\UtilComponent;
use App\Models\StockModel;
use App\Models\OrderStockModel;
use App\Models\OrderExtraModel;
use App\Models\ExtraModel;
use App\Models\ExtraItemModel;
use App\Plugins\Invoice\Models\InvoiceModel;

class CartController extends BaseShopCartController
{

    public function ApplyCode()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (! isset($_POST['code']) || ! ValidationComponent::NotEmpty($_POST['code'])) {
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 104,
                    'text' => __('system_104', true)
                ));
            }
            $pre = array();
            list ($pre['date'], $pre['hour'], $pre['minute']) = explode(",", date("Y-m-d,H,i"));
            $product_ids = CartModel::factory()->where('t1.hash', $_SESSION[$this->defaultHash])
                ->findAll()
                ->getDataPair(null, 'product_id');
            $product_ids = array_unique($product_ids);
            $response = ShopCartAppController::getDiscount(array_merge($_POST, $pre), $this->option_arr);
            if ($response['status'] == 'OK') {
                $intersect = array_intersect($response['voucher_products'], $product_ids);
                if (empty($response['voucher_products'][0]) || ! empty($intersect)) {
                    $_SESSION[$this->defaultVoucher] = array(
                        'voucher_code' => $response['voucher_code'],
                        'voucher_type' => $response['voucher_type'],
                        'voucher_discount' => $response['voucher_discount'],
                        'voucher_products' => empty($response['voucher_products'][0]) ? 'all' : $response['voucher_products']
                    );
                } else {
                    $response = array(
                        'status' => 'ERR',
                        'code' => 104,
                        'text' => 'Voucher code not applied.'
                    );
                }
            }
            AppController::jsonResponse($response);
        }
        exit();
    }


    public function RemoveCode()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_SESSION[$this->defaultVoucher]) && ! empty($_SESSION[$this->defaultVoucher])) {
                $_SESSION[$this->defaultVoucher] = NULL;
                unset($_SESSION[$this->defaultVoucher]);
            }
            AppController::jsonResponse(array(
                'status' => 'OK',
                'code' => 205,
                'text' => __('system_205', true)
            ));
        }
        exit();
    }

 
    public function Add()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_POST['qty'])) {
                $qty = $_POST['qty'];
                unset($_POST['qty']);
                if (isset($_POST['extra']) && (empty($_POST['extra']) || empty($_POST['extra'][0]))) {
                    unset($_POST['extra']);
                }
                $key = serialize($_POST);
                $q = $this->cart->get($key);
                if ($q !== FALSE) {
                    $this->cart->update($key, $q['qty'] + $qty);
                } else {
                    $this->cart->insert($key, $qty);
                }
                $response = array(
                    'status' => 'OK',
                    'code' => 206,
                    'text' => __('system_206', true)
                );
            } else {
                $response = array(
                    'status' => 'ERR',
                    'code' => 105,
                    'text' => __('system_105', true)
                );
            }
            AppController::jsonResponse($response);
        }
        exit();
    }


    public function Remove()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_POST['hash']) && ! empty($_POST['hash']) && ! $this->cart->isEmpty()) {
                $response = array(
                    'status' => 'OK',
                    'code' => 207,
                    'text' => __('system_207', true)
                );
                $this->cart->remove($_POST['hash']);
            }
            if (! isset($response)) {
                $response = array(
                    'status' => 'ERR',
                    'code' => 106,
                    'text' => __('system_106', true)
                );
            }
            AppController::jsonResponse($response);
        }
        exit();
    }


    public function CartEmpty()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (! $this->cart->isEmpty()) {
                $this->cart->clear();
                $response = array(
                    'status' => 'OK',
                    'code' => 208,
                    'text' => __('system_208', true)
                );
            } else {
                $response = array(
                    'status' => 'ERR',
                    'code' => 107,
                    'text' => __('system_107', true)
                );
            }
            AppController::jsonResponse($response);
        }
        exit();
    }


    public function Update()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_POST['qty']) && ! empty($_POST['qty']) && ! $this->cart->isEmpty()) {
                $cart_arr = $this->get('cart_arr');
                foreach ($_POST['qty'] as $hash => $qty) {
                    foreach ($cart_arr as $item) {
                        if ($hash == md5($item['key_data'])) {
                            if ((int) $qty > 0) {
                                $this->cart->update($item['key_data'], $qty);
                            } else {
                                $this->cart->remove($hash);
                            }
                            $response = array(
                                'status' => 'OK',
                                'code' => 209,
                                'text' => __('system_209', true)
                            );
                            break;
                        }
                    }
                }
            }
            if (isset($_POST['tax_id'])) {
                $_SESSION[$this->defaultTax] = (int) $_POST['tax_id'];
            }
            if (! isset($response)) {
                $response = array(
                    'status' => 'ERR',
                    'code' => 108,
                    'text' => __('system_108', true)
                );
            }
            AppController::jsonResponse($response);
        }
        exit();
    }

    public function GetAddress()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
                $address_arr = AddressModel::factory()->select('t1.*, t2.content AS country_name')
                    ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.country_id AND t2.field='name' AND t2.locale='" . $this->getFrontendLocaleId() . "'", 'left outer')
                    ->find($_GET['id'])
                    ->getData();
                AppController::jsonResponse(array(
                    'status' => 'OK',
                    'code' => 200,
                    'text' => '',
                    'result' => $address_arr
                ));
            }
            AppController::jsonResponse(array(
                'status' => 'ERR',
                'code' => 100,
                'text' => ''
            ));
        }
    }

    public function GetPaymentForm()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $order_arr = OrderModel::factory()->find($_GET['order_id'])->getData();
            $invoice_arr = InvoiceModel::factory()->find($_GET['invoice_id'])->getData();
            switch ($_GET['payment_method']) {
                case 'paypal':
                    $this->set('params', array(
                        'name' => 'scPaypal',
                        'id' => 'scPaypal',
                        'target' => '_self',
                        'business' => $this->option_arr['o_paypal_address'],
                        'item_name' => $order_arr['uuid'],
                        'custom' => $invoice_arr['uuid'],
                        'amount' => $invoice_arr['total'],
                        'currency_code' => $invoice_arr['currency'],
                        'return' => $this->option_arr['o_thankyou_page'],
                        'notify_url' => BASE_URL . 'index.php?controller=Front&action=ConfirmPaypal&locale=' . $order_arr['locale_id']
                    ));
                    break;
                case 'authorize':
                    $this->set('params', array(
                        'name' => 'scAuthorize',
                        'id' => 'scAuthorize',
                        'timezone' => $this->option_arr['o_authorize_tz'],
                        'transkey' => $this->option_arr['o_authorize_key'],
                        'x_login' => $this->option_arr['o_authorize_mid'],
                        'x_description' => $order_arr['uuid'],
                        'x_amount' => $invoice_arr['total'],
                        'x_invoice_num' => $invoice_arr['uuid'],
                        'x_receipt_link_url' => $this->option_arr['o_thankyou_page'],
                        'x_relay_url' => BASE_URL . 'index.php?controller=Front&action=ConfirmAuthorize&locale=' . $order_arr['locale_id']
                    ));
                    break;
            }
            $this->set('category_arr', CategoryModel::factory()->getNode($this->getFrontendLocaleId(), 1));
            $this->set('order_arr', $order_arr)->set('get', $_GET);
        }
    }


    public function ProcessOrder()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $_SESSION[$this->defaultForm] = $_POST;
            $data = array();
            if ($this->isLoged()) {
                $data['client_id'] = $this->getUserId();
                if (isset($_SESSION[$this->defaultForm]['b_save'])) {
                    $this->SaveToAddressBook($data['client_id'], $_SESSION[$this->defaultForm], 'b_');
                }
                if (isset($_SESSION[$this->defaultForm]['s_save']) && ! isset($_SESSION[$this->defaultForm]['same_as'])) {
                    $this->SaveToAddressBook($data['client_id'], $_SESSION[$this->defaultForm], 's_');
                }
            } else {
                $ClientModel = ClientModel::factory();
                $ClientModel->beforeValidate($this->option_arr);
                if (! $ClientModel->validates(@$_SESSION[$this->defaultForm])) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 111,
                        'text' => 'Thông tin khách hàng chưa hợp lệ'
                    ));
                }
                $client = $ClientModel->where('t1.email', $_SESSION[$this->defaultForm]['email'])
                    ->limit(1)
                    ->findAll()
                    ->getData();
                if (! empty($client)) {
                    $client = $client[0];
                    if ($client['password'] != $_SESSION[$this->defaultForm]['password']) {
                        AppController::jsonResponse(array(
                            'status' => 'ERR',
                            'code' => 112,
                            'text' => 'Mật khẩu không chính xác'
                        ));
                    } elseif ($client['status'] != 'T') {
                        AppController::jsonResponse(array(
                            'status' => 'ERR',
                            'code' => 132,
                            'text' => 'Khách hàng chưa được kích hoạt'
                        ));
                    } else {
                        $data['client_id'] = $client['id'];
                        $ClientModel->reset()
                            ->set('id', $client['id'])
                            ->modify(array(
                            'phone' => $_SESSION[$this->defaultForm]['phone'],
                            'url' => $_SESSION[$this->defaultForm]['url'],
                            'client_name' => $_SESSION[$this->defaultForm]['client_name']
                        ));
                        if (isset($_SESSION[$this->defaultForm]['b_save'])) {
                            $this->SaveToAddressBook($data['client_id'], $_SESSION[$this->defaultForm], 'b_');
                        }
                        if (isset($_SESSION[$this->defaultForm]['s_save']) && ! isset($_SESSION[$this->defaultForm]['same_as'])) {
                            $this->SaveToAddressBook($data['client_id'], $_SESSION[$this->defaultForm], 's_');
                        }
                    }
                } else {
                    $client_id = $ClientModel->setAttributes($_SESSION[$this->defaultForm])
                        ->insert()
                        ->getInsertId();
                    if ($client_id !== false && (int) $client_id > 0) {
                        $data['client_id'] = $client_id;
                        $this->SaveToAddressBook($data['client_id'], $_SESSION[$this->defaultForm], 'b_');
                    } else {
                        AppController::jsonResponse(array(
                            'status' => 'ERR',
                            'code' => 113,
                            'text' => 'Không thể tạo khách hàng'
                        ));
                    }
                }
            }
            if (isset($_SESSION[$this->defaultVoucher]) && isset($_SESSION[$this->defaultVoucher]['voucher_code'])) {
                $data['voucher'] = $_SESSION[$this->defaultVoucher]['voucher_code'];
            }
            $data['status'] = 'new';
            $data['uuid'] = UtilComponent::uuid();
            $data['ip'] = $_SERVER['REMOTE_ADDR'];
            $data['locale_id'] = $this->getFrontendLocaleId();
            $data = array_merge($_SESSION[$this->defaultForm], $data);
            if (isset($data['payment_method']) && $data['payment_method'] != 'creditcard') {
                unset($data['cc_type']);
                unset($data['cc_num']);
                unset($data['cc_exp_month']);
                unset($data['cc_exp_year']);
                unset($data['cc_code']);
            }
            $OrderModel = OrderModel::factory();
            if (! $OrderModel->validates($data)) {
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 114,
                    'text' => 'Thông tin tạo đơn hàng không hợp lệ'
                ));
            }
            $stock_id = $stocks = $product_id = array();
            $cart_arr = $this->get('cart_arr');
            foreach ($cart_arr as $cart_item) {
                $stock_id[] = $cart_item['stock_id'];
                $product_id[] = $cart_item['product_id'];
            }
            if (empty($stock_id)) {
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 115,
                    'text' => 'Kho hàng trống'
                ));
            }
            $StockModel = StockModel::factory();
            $stock_arr = $StockModel->whereIn('t1.id', $stock_id)
                ->findAll()
                ->getData();
            foreach ($stock_arr as $stock) {
                $stocks[$stock['id']] = $stock;
            }
            if (empty($stocks)) {
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 116,
                    'text' => 'Kho hàng trống'
                ));
            }
            $ExtraItemModel = ExtraItemModel::factory();
            $extra_arr = ExtraModel::factory()->whereIn('t1.product_id', $product_id)
                ->findAll()
                ->getDataPair('id', 'price');
            foreach ($extra_arr as $e_id => $e_price) {
                $extra_arr[$e_id] = array(
                    'price' => $e_price,
                    'extra_items' => $ExtraItemModel->reset()
                        ->join('Extra', "t2.id=t1.extra_id AND t2.type='multi'", 'inner')
                        ->where('t1.extra_id', $e_id)
                        ->findAll()
                        ->getDataPair('id', 'price')
                );
            }
            $calc_price = BaseShopCartController::CalcPrices($product_id, $extra_arr, $cart_arr, $stocks, @$_SESSION[$this->defaultVoucher], $this->option_arr, isset($_SESSION[$this->defaultTax]) ? $_SESSION[$this->defaultTax] : null, 'front');
            if ($calc_price == false) {
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 118,
                    'text' => 'Không thể tính giá'
                ));
            }
            $data['tax_id'] = (int) @$_SESSION[$this->defaultTax];
            $data['price'] = $calc_price['price'];
            $data['discount'] = $calc_price['discount'];
            $data['insurance'] = $calc_price['insurance'];
            $data['shipping'] = $calc_price['shipping'];
            $data['tax'] = $calc_price['tax'];
            $data['total'] = $calc_price['total'];
            $order_id = $OrderModel->setAttributes($data)
                ->insert()
                ->getInsertId();
            if ($order_id !== false && (int) $order_id > 0) {
                $OrderStockModel = OrderStockModel::factory();
                $OrderExtraModel = OrderExtraModel::factory();
                foreach ($cart_arr as $cart_item) {
                    $item = unserialize($cart_item['key_data']);
                    $order_stock_id = $OrderStockModel->reset()
                        ->setAttributes(array(
                        'order_id' => $order_id,
                        'product_id' => $cart_item['product_id'],
                        'stock_id' => $cart_item['stock_id'],
                        'price' => @$stocks[$cart_item['stock_id']]['price'],
                        'qty' => $cart_item['qty']
                    ))
                        ->insert()
                        ->getInsertId();
                    if ($order_stock_id !== FALSE && (int) $order_stock_id > 0) {
                        $StockModel->reset()
                            ->set('id', $cart_item['stock_id'])
                            ->modify(array(
                            'qty' => ":qty - " . (int) $cart_item['qty']
                        ));
                        if (isset($item['extra']) && is_array($item['extra'])) {
                            $extras = array(
                                'order_id' => $order_id,
                                'order_stock_id' => $order_stock_id
                            );
                            foreach ($item['extra'] as $extra) {
                                if (strpos($extra, ".") !== FALSE) {
                                    list ($extras['extra_id'], $extras['extra_item_id']) = explode(".", $extra);
                                    $extras['price'] = @$extra_arr[$extras['extra_id']]['extra_items'][$extras['extra_item_id']];
                                } else {
                                    $extras['extra_id'] = $extra;
                                    $extras['extra_item_id'] = NULL;
                                    $extras['price'] = @$extra_arr[$extra]['price'];
                                }
                                $OrderExtraModel->reset()
                                    ->setAttributes($extras)
                                    ->insert();
                            }
                        }
                    }
                }
                $invoice_arr = $this->GenerateInvoice($order_id);
                $order_arr = $OrderModel->reset()
                    ->select(sprintf("t1.*,  AES_DECRYPT(t1.cc_type, '%1\$s') AS `cc_type`,  AES_DECRYPT(t1.cc_num, '%1\$s') AS `cc_num`,  AES_DECRYPT(t1.cc_exp_month, '%1\$s') AS `cc_exp_month`,  AES_DECRYPT(t1.cc_exp_year, '%1\$s') AS `cc_exp_year`,  AES_DECRYPT(t1.cc_code, '%1\$s') AS `cc_code`,  t2.content AS `b_country`, t3.content AS `s_country`, t4.email AS `admin_email`, t4.phone AS `admin_phone`,  t6.content AS `confirm_subject_client`, t7.content AS `confirm_tokens_client`, t8.content AS `confirm_subject_admin`,  t9.content AS `confirm_tokens_admin`, t10.content AS `confirm_sms_admin`,  t5.email, t5.client_name, t5.phone, t5.url, AES_DECRYPT(t5.password, '%1\$s') AS `password`", SALT))
                    ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.b_country_id AND t2.locale=t1.locale_id AND t2.field='name'", 'left outer')
                    ->join('MultiLang', "t3.model='Country' AND t3.foreign_id=t1.s_country_id AND t3.locale=t1.locale_id AND t3.field='name'", 'left outer')
                    ->join('User', 't4.id=1', 'left outer')
                    ->join('Client', 't5.id=t1.client_id', 'left outer')
                    ->join('MultiLang', sprintf("t6.model='Option' AND t6.foreign_id='%u' AND t6.locale=t1.locale_id AND t6.field='confirm_subject_client'", $this->getForeignId()), 'left outer')
                    ->join('MultiLang', sprintf("t7.model='Option' AND t7.foreign_id='%u' AND t7.locale=t1.locale_id AND t7.field='confirm_tokens_client'", $this->getForeignId()), 'left outer')
                    ->join('MultiLang', sprintf("t8.model='Option' AND t8.foreign_id='%u' AND t8.locale=t1.locale_id AND t8.field='confirm_subject_admin'", $this->getForeignId()), 'left outer')
                    ->join('MultiLang', sprintf("t9.model='Option' AND t9.foreign_id='%u' AND t9.locale=t1.locale_id AND t9.field='confirm_tokens_admin'", $this->getForeignId()), 'left outer')
                    ->join('MultiLang', sprintf("t10.model='Option' AND t10.foreign_id='%u' AND t10.locale=t1.locale_id AND t10.field='confirm_sms_admin'", $this->getForeignId()), 'left outer')
                    ->find($order_id)
                    ->getData();
                $order_arr['has_digital'] = BaseShopCartController::CheckDigital($order_id);
                BaseShopCartController::ConfirmSend($this->option_arr, $order_arr, 'confirm');
                $this->cart->clear();
                $_SESSION[$this->defaultForm] = NULL;
                unset($_SESSION[$this->defaultForm]);
                $_SESSION[$this->defaultVoucher] = NULL;
                unset($_SESSION[$this->defaultVoucher]);
                $_SESSION[$this->defaultTax] = NULL;
                unset($_SESSION[$this->defaultTax]);
                $_SESSION[$this->defaultCaptcha] = NULL;
                unset($_SESSION[$this->defaultCaptcha]);
                AppController::jsonResponse(array(
                    'status' => 'OK',
                    'code' => 210,
                    'text' => 'Đặt hàng thành công',
                    'order_id' => $order_id,
                    'invoice_id' => @$invoice_arr['data']['id'],
                    'payment_method' => $data['payment_method']
                ));
            } else {
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 119,
                    'text' => 'Không tạo được đơn hàng'
                ));
            }
        }
        exit();
    }

    static public function CalcPrice($option_arr, $cart_arr, $stock_arr, $extra_arr, $o_shipping, $o_tax, $o_free, $voucher_sess)
    {
        $total = $subtotal = $tax = $insurance = $shipping = $discount = $amount = 0;
        $voucher_discount = $discount_print = $voucher_code = '';
        $p_arr = array();
        foreach ($cart_arr as $key => $cart_item) {
            $item = unserialize($cart_item['key_data']);
            $price = (@$stock_arr[$cart_item['stock_id']]['price']);
            $p_arr[$key] = $price;
            $extra_price = 0;
            if (isset($item['extra']) && ! empty($item['extra'])) {
                $extras = array();
                foreach ($item['extra'] as $eid) {
                    if (strpos($eid, ".") === FALSE) {
                        foreach ($extra_arr as $extra) {
                            if ($extra['id'] == $eid) {
                                $extra_price += $extra['price'];
                                break;
                            }
                        }
                    } else {
                        list ($e_id, $ei_id) = explode(".", $eid);
                        foreach ($extra_arr as $extra) {
                            if ($extra['id'] == $e_id && isset($extra['extra_items']) && ! empty($extra['extra_items'])) {
                                foreach ($extra['extra_items'] as $extra_item) {
                                    if ($extra_item['id'] == $ei_id) {
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
            $price += $extra_price;
            $subtotal = $price * $cart_item['qty'];
            $amount += $subtotal;
            $discount += UtilComponent::getDiscount($subtotal, $item['product_id'], $voucher_sess);
            if (isset($voucher_sess) && ! empty($voucher_sess)) {
                $voucher_code = $voucher_sess['voucher_code'];
                $voucher_discount = $voucher_sess['voucher_discount'];
                switch ($voucher_sess['voucher_type']) {
                    case 'percent':
                        $discount_print = $voucher_discount . '%';
                        break;
                    case 'amount':
                        $discount_print = UtilComponent::formatCurrencySign(number_format($voucher_discount, 2), $option_arr['o_currency']);
                        break;
                }
            }
            $shipping = $o_shipping != null ? (float) $o_shipping : 0;
            if ($o_free != null && (float) $o_free > 0 && (float) $amount >= (float) $o_free) {
                $shipping = 0;
            }
            switch ($option_arr['o_insurance_type']) {
                case 'percent':
                    $insurance = (($amount - $discount) * (float) $option_arr['o_insurance']) / 100;
                    break;
                case 'amount':
                    $insurance = (float) $option_arr['o_insurance'];
                    break;
                default:
                    $insurance = 0;
            }
            if ($o_tax != null && (float) $o_tax > 0) {
                $tax = (($amount - $discount) * (float) $o_tax) / 100;
            }
            $total = $amount - $discount + $tax + $shipping + $insurance;
            $total = $total > 0 ? $total : 0;
        }
        return compact('p_arr', 'total', 'subtotal', 'tax', 'insurance', 'shipping', 'discount', 'amount', 'voucher_code', 'voucher_discount', 'discount_print');
    }
}
?>