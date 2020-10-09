<?php
namespace App\Controllers;

use App\Models\CartModel;
use App\Controllers\Components\ShoppingCartComponent;
use App\Plugins\Locale\Models\LocaleModel;
use App\Models\ProductModel;
use App\Models\OptionModel;
use App\Models\AddressModel;
use App\Models\StockModel;
use App\Models\ExtraItemModel;
use App\Models\ExtraModel;
use App\Models\TaxModel;
use App\Plugins\Gallery\Models\GalleryModel;
use Core\Framework\Components\CaptchaComponent;
use App\Models\ProductCategoryModel;
use Core\Framework\Objects;
use App\Models\OrderModel;
use Core\Framework\Components\EmailComponent;
use Core\Framework\Dispatcher;
use App\Models\OrderStockModel;
use App\Models\StockAttributeModel;
use App\Models\AttributeModel;
use App\Controllers\Components\UtilComponent;
use App\Models\MultiLangModel;
use Core\Framework\Components\ValidationComponent;
use Core\Framework\Components\SanitizeComponent;
use Core\Framework\Components\ZipStreamComponent;
use App\Plugins\Invoice\Models\InvoiceModel;
use App\Models\RouterModel;

class BaseShopCartController extends ShopCartAppController
{

    public $defaultForm = 'SCart_Form';

    public $defaultCaptcha = 'SCart_Captcha';

    public $defaultUser = 'SCart_Client';

    public $defaultVoucher = 'SCart_Voucher';

    public $defaultCookie = 'SCart_Cookie';

    public $defaultTax = 'SCart_Tax';

    public $defaultLocale = 'SCart_LocaleId';

    public $defaultHash = 'SCart_Hash';

    public $defaultLangMenu = 'SCart_LangMenu';

    public $defaultCategoryMenu = 'SCart_CategoryMenu';

    public $cart = NULL;

    public function __construct()
    {
        $this->setLayout('Front');
        if (! isset($_SESSION[$this->defaultHash])) {
            if ($this->isLoged()) {
                $_SESSION[$this->defaultHash] = md5(SALT . $this->getUserId());
            } else {
                $_SESSION[$this->defaultHash] = md5(uniqid(rand(), true));
            }
        }
        $this->loadDefaultFrontendLang();
        $this->setModel('Cart', CartModel::factory());
        $this->cart = new ShoppingCartComponent($this->getModel('Cart'), $_SESSION[$this->defaultHash]);
        $this->set('cart_arr', $this->cart->getAll());
        self::allowCORS();
    }

    public function afterFilter()
    {
        if (! isset($_GET['hide']) || (isset($_GET['hide']) && (int) $_GET['hide'] !== 1) && in_array($_GET['action'], array(
            'Login',
            'Forgot',
            'Register',
            'Profile',
            'Favs',
            'Products',
            'Product',
            'Cart',
            'Checkout',
            'Preview',
            'GetPaymentForm'
        ))) {
            $locale_arr = LocaleModel::factory()->select('t1.*, t2.file, t2.title')
                ->join('LocaleLanguage', 't2.iso=t1.language_iso', 'left outer')
                ->where('t2.file IS NOT NULL')
                ->orderBy('t1.sort ASC')
                ->findAll()
                ->getData();
            $this->set('locale_arr', $locale_arr);
        }
        $this->set('hidden_ids_arr', ProductModel::factory()->where('t1.status', 2)
            ->findAll()
            ->getDataPair('id', 'id'));
    }

 
    public function beforeFilter()
    {
        $this->setModel('Option', OptionModel::factory());
        $OptionModel = $this->getModel('Option');
        $this->option_arr = $OptionModel->getPairs($this->getForeignId());
        $this->set('option_arr', $this->option_arr);
        $this->setTime();
        if (isset($_GET['locale']) && (int) $_GET['locale'] > 0) {
            $this->SetLocale($_GET['locale']);
        }
        if ($this->GetLocale() === FALSE) {
            $locale_arr = LocaleModel::factory()->where('is_default', 1)
                ->limit(1)
                ->findAll()
                ->getData();
            if (count($locale_arr) === 1) {
                $this->SetLocale($locale_arr[0]['id']);
            }
        }

        $this->loadSetFields(true);
    }

    public function beforeRender()
    {
        $this->set('price_arr', $this->GetPrice());
    }


    protected function GetPrice()
    {
        if ($this->cart->isEmpty()) {
            return array(
                'status' => 'ERR',
                'code' => 105,
                'text' => 'Empty cart.'
            );
        }
        $data = $stock_id = $stocks = $product_id = array();
        $cart_arr = $this->get('cart_arr');
        foreach ($cart_arr as $cart_item) {
            if (isset($cart_item['stock_id']) && (int) $cart_item['stock_id'] > 0) {
                $stock_id[] = $cart_item['stock_id'];
            }
            $product_id[] = $cart_item['product_id'];
        }
        if (empty($stock_id)) {
            return array(
                'status' => 'ERR',
                'code' => 105,
                'text' => 'Empty cart.'
            );
        }
        $stocks = StockModel::factory()->whereIn('t1.id', $stock_id)
            ->findAll()
            ->getDataPair('id');
        if (empty($stocks)) {
            return array(
                'status' => 'ERR',
                'code' => 106,
                'text' => 'Stocks in cart not found into the database.'
            );
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
        $calc_price = ShopCartAppController::CalcPrices($product_id, $extra_arr, $cart_arr, $stocks, @$_SESSION[$this->defaultVoucher], $this->option_arr, isset($_SESSION[$this->defaultTax]) ? $_SESSION[$this->defaultTax] : null, 'front');
        if ($calc_price == false) {
            return array(
                'status' => 'ERR',
                'code' => 108,
                'text' => __('system_118', true)
            );
        }
        $data['price'] = $calc_price['price'];
        $data['discount'] = $calc_price['discount'];
        $data['insurance'] = $calc_price['insurance'];
        $data['shipping'] = $calc_price['shipping'];
        $data['tax'] = $calc_price['tax'];
        $data['total'] = $calc_price['total'];
        $data['total'] = $data['total'] > 0 ? $data['total'] : 0;
        return array(
            'status' => 'OK',
            'code' => 200,
            'text' => 'Success',
            'data' => $data
        );
    }


    protected function GetCart()
    {
        $order_arr = $product_id = $stock_id = array();
        $cart_arr = $this->get('cart_arr');
        foreach ($cart_arr as $cart_item) {
            if (! isset($order_arr[$cart_item['stock_id']])) {
                $order_arr[$cart_item['stock_id']] = 0;
            }
            $order_arr[$cart_item['stock_id']] += $cart_item['qty'];
            $product_id[] = $cart_item['product_id'];
            if (! empty($cart_item['stock_id'])) {
                $stock_id[] = $cart_item['stock_id'];
            }
        }
        $arr = ProductModel::factory()->select(sprintf("t1.*, t2.content AS name,  (SELECT GROUP_CONCAT(`category_id`) FROM `%1\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `category_ids`", ProductCategoryModel::factory()->getTable()))
            ->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='name'", 'left outer')
            ->join('Stock', 't3.product_id=t1.id', 'inner')
            ->whereIn('t1.id', $product_id)
            ->groupBy('t1.id')
            ->findAll()
            ->toArray('category_ids', ',')
            ->getData();
            if (!empty($arr)) {
                $arr = UtilComponent::attachCustomLinks($arr, RouterModel::TYPE_PRODUCT, $this->getFrontendLocaleId());
            }
        
        $tax_arr = TaxModel::factory()->select('t1.*, t2.content AS location')
            ->join('MultiLang', "t2.model='Tax' AND t2.foreign_id=t1.id AND t2.field='location' AND t2.locale='" . $this->getFrontendLocaleId() . "'", 'left outer')
            ->orderBy('`location` ASC')
            ->findAll()
            ->getData();
        $_stock_arr = StockModel::factory()->whereIn('t1.id', $stock_id)
            ->findAll()
            ->getData();
        $stock_arr = array();
        foreach ($_stock_arr as $stock) {
            $stock_arr[$stock['id']] = $stock;
        }
        $image_arr = StockModel::factory()->select('t1.id, t2.small_path')
            ->join('Gallery', 't2.id=t1.image_id', 'left outer')
            ->whereIn('t1.id', $stock_id)
            ->findAll()
            ->getDataPair('id', 'small_path');
        foreach ($image_arr as $id => $img) {
            if (empty($img)) {
                $gallery_arr = GalleryModel::factory()->select('t1.*')
                    ->where("`foreign_id` = (SELECT TS.`product_id` FROM `" . StockModel::factory()->getTable() . "` AS TS WHERE TS.id='$id')")
                    ->limit(1)
                    ->findAll()
                    ->getData();
                if (! empty($gallery_arr)) {
                    $image_arr[$id] = $gallery_arr[0]['small_path'];
                }
            }
        }
        $extra_arr = ShopCartAppController::GetExtrasList($product_id, $this->getFrontendLocaleId());
        $attr_arr = ShopCartAppController::GetAttr($product_id, $this->getFrontendLocaleId());
        return compact('arr', 'extra_arr', 'order_arr', 'attr_arr', 'stock_arr', 'tax_arr', 'image_arr');
    }


    public function Captcha()
    {
        $this->setAjax(true);
        $this->setLayout('Empty');
        $Captcha = new CaptchaComponent(WEB_PATH . 'obj/Lato-Bol.ttf', $this->defaultCaptcha, 6);
        $Captcha->setImage(IMG_PATH . 'button.png')->init(@$_GET['rand']);
        exit();
    }


    public function CheckCaptcha()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            echo isset($_SESSION[$this->defaultCaptcha]) && isset($_GET['captcha']) && CaptchaComponent::validate($_GET['captcha'], $_SESSION[$this->defaultCaptcha]) ? 'true' : 'false';
        }
        exit();
    }


    public function ConfirmAuthorize()
    {
        $this->setAjax(true);
        if (Objects::getPlugin('Authorize') === NULL) {
            $this->log('Authorize.NET plugin not installed');
            exit();
        }
        if (! isset($_POST['x_invoice_num'])) {
            $this->log('Missing arguments');
            exit();
        }
        $InvoiceModel = InvoiceModel::factory();
        $OrderModel = OrderModel::factory();
        $invoice_arr = $InvoiceModel->where('t1.uuid', $_POST['x_invoice_num'])
            ->limit(1)
            ->findAll()
            ->getData();
        if (empty($invoice_arr)) {
            $this->log('Invoice not found');
            exit();
        }
        $invoice_arr = $invoice_arr[0];
        $order_arr = $OrderModel->select(sprintf("t1.*,  AES_DECRYPT(t1.cc_type, '%1\$s') AS `cc_type`,  AES_DECRYPT(t1.cc_num, '%1\$s') AS `cc_num`,  AES_DECRYPT(t1.cc_exp_month, '%1\$s') AS `cc_exp_month`,  AES_DECRYPT(t1.cc_exp_year, '%1\$s') AS `cc_exp_year`,  AES_DECRYPT(t1.cc_code, '%1\$s') AS `cc_code`,  t2.content AS b_country, t3.content AS s_country, t4.email AS `admin_email`, t4.phone AS `admin_phone`,  t6.content AS `payment_subject_client`, t7.content AS `payment_tokens_client`, t8.content AS `payment_subject_admin`,  t9.content AS `payment_tokens_admin`, t10.content AS `payment_sms_admin`,  t5.email, t5.client_name, t5.phone, t5.url, AES_DECRYPT(t5.password, '%1\$s') AS `password`", SALT))
            ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.b_country_id AND t2.locale=t1.locale_id AND t2.field='name'", 'left outer')
            ->join('MultiLang', "t3.model='Country' AND t3.foreign_id=t1.s_country_id AND t3.locale=t1.locale_id AND t3.field='name'", 'left outer')
            ->join('User', 't4.id=1', 'left outer')
            ->join('Client', 't5.id=t1.client_id', 'left outer')
            ->join('MultiLang', sprintf("t6.model='Option' AND t6.foreign_id='%u' AND t6.locale=t1.locale_id AND t6.field='payment_subject_client'", $this->getForeignId()), 'left outer')
            ->join('MultiLang', sprintf("t7.model='Option' AND t7.foreign_id='%u' AND t7.locale=t1.locale_id AND t7.field='payment_tokens_client'", $this->getForeignId()), 'left outer')
            ->join('MultiLang', sprintf("t8.model='Option' AND t8.foreign_id='%u' AND t8.locale=t1.locale_id AND t8.field='payment_subject_admin'", $this->getForeignId()), 'left outer')
            ->join('MultiLang', sprintf("t9.model='Option' AND t9.foreign_id='%u' AND t9.locale=t1.locale_id AND t9.field='payment_tokens_admin'", $this->getForeignId()), 'left outer')
            ->join('MultiLang', sprintf("t10.model='Option' AND t10.foreign_id='%u' AND t10.locale=t1.locale_id AND t10.field='payment_sms_admin'", $this->getForeignId()), 'left outer')
            ->where('t1.uuid', $invoice_arr['order_id'])
            ->limit(1)
            ->findAll()
            ->getData();
        if (empty($order_arr)) {
            $this->log('Order not found');
            exit();
        }
        $order_arr = $order_arr[0];
        $params = array(
            'transkey' => $this->option_arr['o_authorize_key'],
            'x_login' => $this->option_arr['o_authorize_mid'],
            'md5_setting' => $this->option_arr['o_authorize_hash'],
            'key' => md5($this->option_arr['private_key'] . SALT)
        );
        $response = $this->requestAction(array(
            'controller' => 'Authorize',
            'action' => 'Confirm',
            'params' => $params
        ), array(
            'return'
        ));
        if ($response !== FALSE && $response['status'] === 'OK') {
            $OrderModel->set('id', $order_arr['id'])->modify(array(
                'status' => 'completed',
                'processed_on' => ':NOW()'
            ));
            $InvoiceModel->reset()
                ->set('id', $invoice_arr['id'])
                ->modify(array(
                'status' => 'paid',
                'modified' => ':NOW()'
            ));
            $order_arr['has_digital'] = AppController::CheckDigital($order_arr['id']);
            BaseShopCartController::ConfirmSend($this->option_arr, $order_arr, 'payment');
        } elseif (! $response) {
            $this->log('Authorization failed');
        } else {
            $this->log('Order not confirmed. ' . $response['response_reason_text']);
        }
        exit();
    }

    public function ConfirmPaypal()
    {
        $this->setAjax(true);
        if (Objects::getPlugin('Paypal') === NULL) {
            $this->log('Paypal plugin not installed');
            exit();
        }
        if (! isset($_POST['custom'])) {
            $this->log('Missing arguments');
            exit();
        }
        $InvoiceModel = InvoiceModel::factory();
        $OrderModel = OrderModel::factory();
        $invoice_arr = $InvoiceModel->where('t1.uuid', $_POST['custom'])
            ->limit(1)
            ->findAll()
            ->getData();
        if (empty($invoice_arr)) {
            $this->log('Invoice not found');
            exit();
        }
        $invoice_arr = $invoice_arr[0];
        $order_arr = $OrderModel->select(sprintf("t1.*,  AES_DECRYPT(t1.cc_type, '%1\$s') AS `cc_type`,  AES_DECRYPT(t1.cc_num, '%1\$s') AS `cc_num`,  AES_DECRYPT(t1.cc_exp_month, '%1\$s') AS `cc_exp_month`,  AES_DECRYPT(t1.cc_exp_year, '%1\$s') AS `cc_exp_year`,  AES_DECRYPT(t1.cc_code, '%1\$s') AS `cc_code`,  t2.content AS b_country, t3.content AS s_country, t4.email AS `admin_email`, t4.phone AS `admin_phone`,  t6.content AS `payment_subject_client`, t7.content AS `payment_tokens_client`, t8.content AS `payment_subject_admin`,  t9.content AS `payment_tokens_admin`, t10.content AS `payment_sms_admin`,  t5.email, t5.client_name, t5.phone, t5.url, AES_DECRYPT(t5.password, '%1\$s') AS `password`", SALT))
            ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.b_country_id AND t2.locale=t1.locale_id AND t2.field='name'", 'left outer')
            ->join('MultiLang', "t3.model='Country' AND t3.foreign_id=t1.s_country_id AND t3.locale=t1.locale_id AND t3.field='name'", 'left outer')
            ->join('User', 't4.id=1', 'left outer')
            ->join('Client', 't5.id=t1.client_id', 'left outer')
            ->join('MultiLang', sprintf("t6.model='Option' AND t6.foreign_id='%u' AND t6.locale=t1.locale_id AND t6.field='payment_subject_client'", $this->getForeignId()), 'left outer')
            ->join('MultiLang', sprintf("t7.model='Option' AND t7.foreign_id='%u' AND t7.locale=t1.locale_id AND t7.field='payment_tokens_client'", $this->getForeignId()), 'left outer')
            ->join('MultiLang', sprintf("t8.model='Option' AND t8.foreign_id='%u' AND t8.locale=t1.locale_id AND t8.field='payment_subject_admin'", $this->getForeignId()), 'left outer')
            ->join('MultiLang', sprintf("t9.model='Option' AND t9.foreign_id='%u' AND t9.locale=t1.locale_id AND t9.field='payment_tokens_admin'", $this->getForeignId()), 'left outer')
            ->join('MultiLang', sprintf("t10.model='Option' AND t10.foreign_id='%u' AND t10.locale=t1.locale_id AND t10.field='payment_sms_admin'", $this->getForeignId()), 'left outer')
            ->where('t1.uuid', $invoice_arr['order_id'])
            ->limit(1)
            ->findAll()
            ->getData();
        if (empty($order_arr)) {
            $this->log('Order not found');
            exit();
        }
        $order_arr = $order_arr[0];
        $params = array(
            'txn_id' => @$invoice_arr['txn_id'],
            'paypal_address' => @$this->option_arr['o_paypal_address'],
            'deposit' => @$invoice_arr['total'],
            'currency' => @$invoice_arr['currency'],
            'key' => md5($this->option_arr['private_key'] . SALT)
        );
        $response = $this->requestAction(array(
            'controller' => 'Paypal',
            'action' => 'Confirm',
            'params' => $params
        ), array(
            'return'
        ));
        if ($response !== FALSE && $response['status'] === 'OK') {
            $this->log('Booking confirmed');
            $OrderModel->reset()
                ->set('id', $order_arr['id'])
                ->modify(array(
                'status' => 'completed',
                'txn_id' => $response['transaction_id'],
                'processed_on' => ':NOW()'
            ));
            $InvoiceModel->reset()
                ->set('id', $invoice_arr['id'])
                ->modify(array(
                'status' => 'paid',
                'modified' => ':NOW()'
            ));
            $order_arr['has_digital'] = AppController::CheckDigital($order_arr['id']);
            BaseShopCartController::ConfirmSend($this->option_arr, $order_arr, 'payment');
        } elseif (! $response) {
            $this->log('Authorization failed');
        } else {
            $this->log('Booking not confirmed');
        }
        exit();
    }


    protected static function ConfirmSend($option_arr, $order_arr, $type)
    {
        if (! in_array($type, array(
            'confirm',
            'payment'
        ))) {
            return false;
        }
        $Email = new EmailComponent();
        $Email->setContentType('text/html');
        if ($option_arr['o_send_email'] == 'smtp') {
            $Email->setTransport('smtp')
                ->setSmtpSecure($option_arr['o_smtp_secure'])
                ->setSmtpHost($option_arr['o_smtp_host'])
                ->setSmtpPort($option_arr['o_smtp_port'])
                ->setSmtpUser($option_arr['o_smtp_user'])
                ->setSmtpPass($option_arr['o_smtp_pass']);
        }
        $order_arr['products'] = self::GetProductsString($order_arr['id'], $order_arr['locale_id']);
        $tokens = self::getTokens($order_arr, $option_arr);
        switch ($type) {
            case 'confirm':
                $subject = str_replace($tokens['search'], $tokens['replace'], $order_arr['confirm_subject_client']);
                $message = str_replace($tokens['search'], $tokens['replace'], $order_arr['confirm_tokens_client']);
                $Email->setTo($order_arr['email'])
                    ->setFrom($order_arr['admin_email'])
                    ->setSubject($subject)
                    ->send($message);
                $subject = str_replace($tokens['search'], $tokens['replace'], $order_arr['confirm_subject_admin']);
                $message = str_replace($tokens['search'], $tokens['replace'], $order_arr['confirm_tokens_admin']);
                $Email->setTo($order_arr['admin_email'])
                    ->setFrom($order_arr['admin_email'])
                    ->setSubject($subject)
                    ->send($message);
                if (Objects::getPlugin('Sms') !== NULL && isset($order_arr['admin_phone']) && ! empty($order_arr['admin_phone'])) {
                    $dispatcher = new Dispatcher();
                    $controller = $dispatcher->createController(array(
                        'controller' => 'Front'
                    ));
                    $controller->requestAction(array(
                        'controller' => 'Sms',
                        'action' => 'Send',
                        'params' => array(
                            'number' => $order_arr['admin_phone'],
                            'text' => str_replace($tokens['search'], $tokens['replace'], @$order_arr['confirm_sms_admin']),
                            'key' => md5($option_arr['private_key'] . SALT)
                        )
                    ), array(
                        'return'
                    ));
                }
                break;
            case 'payment':
                $subject = str_replace($tokens['search'], $tokens['replace'], $order_arr['payment_subject_client']);
                $message = str_replace($tokens['search'], $tokens['replace'], $order_arr['payment_tokens_client']);
                $Email->setTo($order_arr['email'])
                    ->setFrom($order_arr['admin_email'])
                    ->setSubject($subject)
                    ->send($message);
                $subject = str_replace($tokens['search'], $tokens['replace'], $order_arr['payment_subject_admin']);
                $message = str_replace($tokens['search'], $tokens['replace'], $order_arr['payment_tokens_admin']);
                $Email->setTo($order_arr['admin_email'])
                    ->setFrom($order_arr['admin_email'])
                    ->setSubject($subject)
                    ->send($message);
                if (Objects::getPlugin('Sms') !== NULL && isset($order_arr['admin_phone']) && ! empty($order_arr['admin_phone'])) {
                    $dispatcher = new Dispatcher();
                    $controller = $dispatcher->createController(array(
                        'controller' => 'BaseShopCartFront'
                    ));
                    $controller->requestAction(array(
                        'controller' => 'Sms',
                        'action' => 'Send',
                        'params' => array(
                            'number' => $order_arr['admin_phone'],
                            'text' => str_replace($tokens['search'], $tokens['replace'], @$order_arr['payment_sms_admin']),
                            'key' => md5($option_arr['private_key'] . SALT)
                        )
                    ), array(
                        'return'
                    ));
                }
                break;
        }
    }


    public function DigitalDownload()
    {
        $this->setLayout('Empty');
        if (! isset($_GET['uuid']) || empty($_GET['uuid']) || ! isset($_GET['hash']) || empty($_GET['hash']) || md5($_GET['uuid'] . SALT) != $_GET['hash']) {
            $this->set('status', 1);
            return;
        }
        $order = OrderModel::factory()->where('t1.uuid', $_GET['uuid'])
            ->limit(1)
            ->findAll()
            ->getData();
        if (empty($order)) {
            $this->set('status', 2);
            return;
        }
        $order = $order[0];
        if ($order['status'] != 'completed') {
            $this->set('status', 3);
            return;
        }
        $os_arr = OrderStockModel::factory()->select('t3.digital_file, t3.digital_name, t3.digital_expire, t2.processed_on,  DATE_ADD(t2.processed_on, INTERVAL t3.digital_expire HOUR_SECOND) AS `expire_at`,  IF(DATE_ADD(t2.processed_on, INTERVAL t3.digital_expire HOUR_SECOND) < NOW(), 1, 0) AS `is_expired`')
            ->join('Order', 't2.id=t1.order_id', 'inner')
            ->join('Product', "t3.id=t1.product_id AND t3.is_digital='1'", 'inner')
            ->where('t1.order_id', $order['id'])
            ->findAll()
            ->getData();
        if (empty($os_arr)) {
            $this->set('status', 4);
            return;
        }
        $digitals = $expired = array();
        foreach ($os_arr as $item) {
            if ((int) $item['is_expired'] === 0 || $item['digital_expire'] == '00:00:00') {
                $digitals[] = $item;
            } else {
                $expired[] = $item;
            }
        }
        if (empty($digitals)) {
            $this->set('status', 5);
            return;
        }
        $zip = new ZipStreamComponent();
        foreach ($digitals as $file) {
            if (empty($file['digital_file']) || ! is_file($file['digital_file'])) {
                continue;
            }
            $handle = @fopen($file['digital_file'], "rb");
            if ($handle) {
                $buffer = "";
                while (! feof($handle)) {
                    $buffer .= fgets($handle, 4096);
                }
                $zip->addFile($buffer, $file['digital_name']);
                fclose($handle);
            }
        }
        $zip->finalize();
        $zip->sendZip(sprintf("%s.zip", $order['uuid']));
        exit();
    }

    public function GetStocks()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
                $order_arr = array();
                $cart_arr = $this->get('cart_arr');
                foreach ($cart_arr as $cart_item) {
                    if (! isset($order_arr[$cart_item['stock_id']])) {
                        $order_arr[$cart_item['stock_id']] = 0;
                    }
                    $order_arr[$cart_item['stock_id']] += $cart_item['qty'];
                }
                $StockModel = StockModel::factory();
                $StockAttributeModel = StockAttributeModel::factory();
                $AttributeModel = AttributeModel::factory();
                $stock_arr = $StockModel->where('t1.product_id', $_GET['id'])
                    ->where('t1.qty > 0')
                    ->findAll()
                    ->getData();
                $stocks = $stock_ids = $qty = $price = array();
                foreach ($stock_arr as $k => $stock) {
                    $_qty = $stock['qty'];
                    if (isset($order_arr[$stock['id']])) {
                        $_qty -= $order_arr[$stock['id']];
                        if ($_qty < 1) {
                            continue;
                        }
                    }
                    $stock_ids[] = $stock['id'];
                    $stocks[] = $StockAttributeModel->reset()
                        ->where('t1.stock_id', $stock['id'])
                        ->where("t1.attribute_id IN (SELECT TA.id FROM `" . $AttributeModel->getTable() . "` AS `TA` WHERE `TA`.product_id='" . $_GET['id'] . "')")
                        ->orderBy('t1.attribute_id ASC')
                        ->findAll()
                        ->getDataPair('attribute_parent_id', 'attribute_id');
                    $qty[] = $_qty;
                    $price[] = $stock['price'];
                }
                $attr_arr = $AttributeModel->where('t1.product_id', $_GET['id'])
                    ->where(sprintf("(CONCAT_WS('_', t1.id, t1.parent_id) IN (  SELECT CONCAT_WS('_', TSA.attribute_id, TSA.attribute_parent_id)  FROM `%s` AS `TSA`  INNER JOIN `%s` AS `TS` ON TS.id = TSA.stock_id AND TS.qty > 0  WHERE TSA.product_id = t1.product_id  ) OR t1.parent_id IS NULL OR t1.parent_id = '0')", $StockAttributeModel->getTable(), $StockModel->getTable()))
                    ->findAll()
                    ->getDataPair('id', 'parent_id');
                foreach ($stocks as $k => $stock) {
                    foreach ($stock as $_k => $_v) {
                        if ((int) $_v === 0) {
                            $stokkk = $stock;
                            UtilComponent::reArrange($stocks, $qty, $price, $stokkk, $attr_arr, $_k, $k);
                        }
                    }
                }
                $attr_arr = array();
                $a_arr = $AttributeModel->reset()
                    ->select('t1.id, t1.product_id, t1.parent_id, t2.content AS name')
                    ->join('MultiLang', "t2.model='Attribute' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->GetLocale() . "'", 'left outer')
                    ->where('t1.product_id', $_GET['id'])
                    ->where(sprintf("(CONCAT_WS('_', t1.id, t1.parent_id) IN (  SELECT CONCAT_WS('_', TSA.attribute_id, TSA.attribute_parent_id)  FROM `%s` AS `TSA`  INNER JOIN `%s` AS `TS` ON TS.id = TSA.stock_id AND TS.qty > 0  WHERE TSA.product_id = t1.product_id  ) OR t1.parent_id IS NULL OR t1.parent_id = '0')", $StockAttributeModel->getTable(), $StockModel->getTable()))
                    ->orderBy('t1.parent_id ASC, `name` ASC')
                    ->findAll()
                    ->getData();
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
                $attributes = array_values($attr_arr);
                if (isset($stocks[0]) && empty($stocks[0])) {
                    $stocks = array();
                }
                AppController::jsonResponse(compact('stocks', 'qty', 'price', 'stock_ids', 'attributes'));
            }
        }
        exit();
    }


    public function SendToFriend()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (! isset($_POST['id']) || empty($_POST['id']) || ! isset($_POST['url']) || empty($_POST['url']) || ! isset($_POST['your_email']) || empty($_POST['your_email']) || ! ValidationComponent::Email($_POST['your_email']) || ! isset($_POST['your_name']) || empty($_POST['your_name']) || ! isset($_POST['friend_email']) || empty($_POST['friend_email']) || ! ValidationComponent::Email($_POST['friend_email']) || ! isset($_POST['friend_name']) || empty($_POST['friend_name']) || ! isset($_POST['captcha']) || empty($_POST['captcha']) || ! isset($_SESSION[$this->defaultCaptcha]) || ! CaptchaComponent::validate($_POST['captcha'], $_SESSION[$this->defaultCaptcha])) {
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 100,
                    'text' => __('system_100', true)
                ));
            }
            $Email = new EmailComponent();
            $Email->setContentType('text/html');
            if ($this->option_arr['o_send_email'] == 'smtp') {
                $Email->setTransport('smtp')
                    ->setSmtpHost($this->option_arr['o_smtp_host'])
                    ->setSmtpPort($this->option_arr['o_smtp_port'])
                    ->setSmtpUser($this->option_arr['o_smtp_user'])
                    ->setSmtpPass($this->option_arr['o_smtp_pass']);
            }
            $MultiLangModel = MultiLangModel::factory();
            $lang_message = $MultiLangModel->reset()
                ->select('t1.*')
                ->where('t1.model', 'Option')
                ->where('t1.locale', $this->getFrontendLocaleId())
                ->where('t1.field', 'send_to_tokens')
                ->limit(0, 1)
                ->findAll()
                ->getData();
            $lang_subject = $MultiLangModel->reset()
                ->select('t1.*')
                ->where('t1.model', 'Option')
                ->where('t1.locale', $this->getFrontendLocaleId())
                ->where('t1.field', 'send_to_subject')
                ->limit(0, 1)
                ->findAll()
                ->getData();
            if (count($lang_message) === 1 && count($lang_subject) === 1) {
                $message = str_replace(array(
                    '{FriendName}',
                    '{FriendEmail}',
                    '{YourName}',
                    '{YourEmail}',
                    '{URL}'
                ), array(
                    SanitizeComponent::html($_POST['friend_name']),
                    SanitizeComponent::html($_POST['friend_email']),
                    SanitizeComponent::html($_POST['your_name']),
                    SanitizeComponent::html($_POST['your_email']),
                    SanitizeComponent::html($_POST['url'])
                ), $lang_message[0]['content']);
                $result = $Email->setContentType('text/html')
                    ->setTo($_POST['friend_email'])
                    ->setFrom($_POST['your_email'])
                    ->setSubject($lang_subject[0]['content'])
                    ->send(UtilComponent::textToHtml($message));
                if (! $result) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 101,
                        'text' => __('system_101', true)
                    ));
                }
            }
            AppController::jsonResponse(array(
                'status' => 'OK',
                'code' => 200,
                'text' => __('system_200', true)
            ));
        }
        exit();
    }


    public function Load()
    {
        ob_start();
        header("Content-type: text/javascript");
        if (isset($_GET['locale']) && $_GET['locale'] > 0) {
            $_SESSION[$this->defaultLangMenu] = 'hide';
        } else {
            $_SESSION[$this->defaultLangMenu] = 'show';
        }
        if (isset($_GET['category_id']) && $_GET['category_id'] > 0) {
            $_SESSION[$this->defaultCategoryMenu] = 'hide';
        } else {
            $_SESSION[$this->defaultCategoryMenu] = 'show';
        }
    }


    public function LoadCss()
    {
        $layout = isset($_GET['layout']) && in_array($_GET['layout'], $this->getLayoutRange()) ? (int) $_GET['layout'] : (int) $this->option_arr['o_layout'];
        $theme = isset($_GET['theme']) ? $_GET['theme'] : $this->option_arr['o_theme'];
        if ((int) $theme > 0) {
            $theme = 'theme' . $theme;
        }
        $arr = array(
            array(
                'file' => 'ShoppingCart' . $layout . '.css',
                'path' => CSS_PATH
            ),
            array(
                'file' => 'jquery.fancybox.css',
                'path' => LIBS_PATH . 'Q/fancybox/'
            ),
            array(
                'file' => $theme . '.css',
                'path' => CSS_PATH
            )
        );
        header("Content-Type: text/css; charset=utf-8");
        foreach ($arr as $item) {
            $string = FALSE;
            if ($stream = fopen($item['path'] . $item['file'], 'rb')) {
                $string = stream_get_contents($stream);
                fclose($stream);
            }
            if ($string !== FALSE) {
                echo str_replace(array(
                    '../fonts/',
                    "url('",
                    "Wrapper"
                ), array(
                    BASE_URL . LIBS_PATH . 'Q/bootstrap/fonts/',
                    "url('" . BASE_URL . LIBS_PATH . "Q/fancybox/img/",
                    "WrapperShoppingCart_" . $theme
                ), $string) . "\n";
            }
        }
        exit();
    }


    public function Logout()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if ($this->isLoged()) {
                $_SESSION[$this->defaultUser] = NULL;
                unset($_SESSION[$this->defaultUser]);
                $_SESSION[$this->defaultHash] = NULL;
                unset($_SESSION[$this->defaultHash]);
            }
            AppController::jsonResponse(array(
                'status' => 'OK',
                'code' => 201,
                'text' => __('system_201', true)
            ));
        }
        exit();
    }


    public function Locale()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_GET['locale_id'])) {
                $this->SetLocale($_GET['locale_id']);
            }
        }
        exit();
    }

    private function SetLocale($locale)
    {
        if ((int) $locale > 0) {
            $_SESSION[$this->defaultLocale] = (int) $locale;
        }
        return $this;
    }

    public function GetLocale()
    {
        return isset($_SESSION[$this->defaultLocale]) && (int) $_SESSION[$this->defaultLocale] > 0 ? (int) $_SESSION[$this->defaultLocale] : FALSE;
    }

  

    public function ShowShipping()
    {
        $cart_arr = $this->get('cart_arr');
        foreach ($cart_arr as $cart_item) {
            $item = unserialize($cart_item['key_data']);
            if ((int) $item['is_digital'] === 0) {
                return true;
                break;
            }
        }
        return false;
    }


    protected function SaveToAddressBook($client_id, $data, $prefix = 'b_')
    {
        return AddressModel::factory()->setAttributes(array(
            'client_id' => $client_id,
            'country_id' => @$data[$prefix . 'country_id'],
            'state' => @$data[$prefix . 'state'],
            'city' => @$data[$prefix . 'city'],
            'zip' => @$data[$prefix . 'zip'],
            'address_1' => @$data[$prefix . 'address_1'],
            'address_2' => @$data[$prefix . 'address_2'],
            'name' => @$data[$prefix . 'name']
        ))
            ->insert()
            ->getInsertId();
    }


    public function isXHR()
    {
        return parent::isXHR() || isset($_SERVER['HTTP_ORIGIN']);
    }


    static protected function allowCORS()
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With");
    }
}
?>