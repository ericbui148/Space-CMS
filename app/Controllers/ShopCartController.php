<?php
namespace App\Controllers;

use App\Models\AddressModel;
use App\Models\CategoryModel;
use App\Plugins\Country\Models\CountryModel;
use App\Models\TaxModel;
use Core\Framework\Components\ValidationComponent;
use App\Models\ClientModel;
use Core\Framework\Components\EmailComponent;
use App\Models\MultiLangModel;
use Core\Framework\Components\CaptchaComponent;
use App\Plugins\Gallery\Models\GalleryModel;
use App\Models\StockModel;
use App\Models\ProductCategoryModel;
use App\Models\ProductModel;
use App\Models\StockAttributeModel;
use App\Models\ExtraItemModel;
use App\Models\ExtraModel;
use App\Models\AttributeModel;
use App\Models\ProductSimilarModel;
use App\Models\TagModel;
use App\Controllers\Components\UtilComponent;
use App\Models\RouterModel;
use App\Models\ItemSortModel;

class ShopCartController extends BaseShopCartController
{

    public function __construct()
    {
        parent::__construct();
        $this->theme = $this->getTheme();
        $this->setLayout ( 'Site');
    }


    public function Cart()
    {
        $this->checkMaintainMode();
        $this->set('page','cart');
        $this->title = "Giỏ hàng";
        if (! $this->cart->isEmpty() && (int) $this->option_arr['o_disable_orders'] === 0) {
            $data = $this->GetCart();
            if (isset($_SESSION[$this->defaultTax]) && (int) $_SESSION[$this->defaultTax] > 0) {
                foreach ($data['tax_arr'] as $item) {
                    if ($item['id'] == $_SESSION[$this->defaultTax]) {
                        $this->set('o_tax', $item['tax'])
                            ->set('o_shipping', $item['shipping'])
                            ->set('o_free', $item['free']);
                        break;
                    }
                }
            }
            $this->set('arr', $data['arr'])
                ->set('extra_arr', $data['extra_arr'])
                ->set('order_arr', $data['order_arr'])
                ->set('attr_arr', $data['attr_arr'])
                ->set('stock_arr', $data['stock_arr'])
                ->set('tax_arr', $data['tax_arr'])
                ->set('image_arr', $data['image_arr']);
        }
        $tax_arr = TaxModel::factory()->select('t1.*, t2.content as `location`')
        ->join('MultiLang', "t2.model='Tax' AND t2.foreign_id=t1.id AND t2.field='location' AND t2.locale='".$this->getFrontendLocaleId()."'", 'left outer')
        ->findAll()
        ->getData();
        $this->set('tax_arr', $tax_arr);
    }


    public function Checkout()
    {
        $this->checkMaintainMode();
        $this->set('page','checkout');
        $this->title = "Thanh toán";
        if (isset($_POST['sc_checkout'])) {
            $_SESSION[$this->defaultForm] = $_POST;
            UtilComponent::redirect(BASE_URL.'xemlaidonhang');
        } else {
            if (! $this->cart->isEmpty() && (int) $this->option_arr['o_disable_orders'] === 0) {
                if ($this->ShowShipping() && (! isset($_SESSION[$this->defaultTax]) || empty($_SESSION[$this->defaultTax])) && 0 < TaxModel::factory()->findCount()->getData()) {
                    $this->set('status', 'ERR');
                    $this->set('code', '100');
                } else {
                    $this->set('country_arr', CountryModel::factory()->select('t1.*, t2.content AS name')
                        ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getFrontendLocaleId() . "'", 'left outer')
                        ->where('t1.status', 'T')
                        ->orderBy('`name` ASC')
                        ->findAll()
                        ->getData());
                    $terms = $this->getModel('Option')
                        ->reset()
                        ->select('t1.*, t2.content AS `terms_url`, t3.content AS `terms_body`')
                        ->join('MultiLang', sprintf("t2.model='Option' AND t2.foreign_id='%u' AND t2.locale='%u' AND t2.field='terms_url'", $this->getForeignId(), $this->GetLocale()), 'left outer')
                        ->join('MultiLang', sprintf("t3.model='Option' AND t3.foreign_id='%u' AND t3.locale='%u' AND t3.field='terms_body'", $this->getForeignId(), $this->GetLocale()), 'left outer')
                        ->limit(1)
                        ->findAll()
                        ->getData();
                    $this->set('terms', @$terms[0]);
                    if ($this->isLoged()) {
                        $this->set('address_arr', AddressModel::factory()->where('t1.client_id', $this->getUserId())
                            ->findAll()
                            ->getData());
                    }
                    $this->set('status', 'OK');
                    $data = $this->GetCart();
                    $this->set('arr', $data['arr'])
                        ->set('extra_arr', $data['extra_arr'])
                        ->set('attr_arr', $data['attr_arr'])
                        ->set('stock_arr', $data['stock_arr'])
                        ->set('tax_arr', $data['tax_arr']);
                }
            } else {
                $this->set('status', 'ERR');
                $this->set('code', '101');
            }
            $category_arr = CategoryModel::factory()->getNode($this->getFrontendLocaleId(), 1);
            $this->set('category_arr',$category_arr);
        }
    }

    public function Preview()
    {
        $this->set('page','order_preview');
        $this->title = "Xem lại đơn hàng";
        if (! $this->cart->isEmpty() && (int) $this->option_arr['o_disable_orders'] === 0) {
            if ($this->ShowShipping() && (! isset($_SESSION[$this->defaultTax]) || empty($_SESSION[$this->defaultTax])) && 0 < TaxModel::factory()->findCount()->getData()) {
                $this->set('status', 'ERR');
                $this->set('code', '100');
            } elseif (! isset($_SESSION[$this->defaultForm]) || empty($_SESSION[$this->defaultForm])) {
                $this->set('status', 'ERR');
                $this->set('code', '102');
            } else {
                $this->set('country_arr', CountryModel::factory()->select('t1.*, t2.content AS name')
                    ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getFrontendLocaleId() . "'", 'left outer')
                    ->where('t1.status', 'T')
                    ->orderBy('`name` ASC')
                    ->findAll()
                    ->getData());
                $this->set('status', 'OK');
                $data = $this->GetCart();
                $this->set('arr', $data['arr'])
                    ->set('extra_arr', $data['extra_arr'])
                    ->set('attr_arr', $data['attr_arr'])
                    ->set('stock_arr', $data['stock_arr'])
                    ->set('tax_arr', $data['tax_arr']);
            }
        } else {
            $this->set('status', 'ERR');
            $this->set('code', '101');
        }
        $this->set('category_arr', CategoryModel::factory()->getNode($this->getFrontendLocaleId(), 1));
    }

    public function Login()
    {
        if ($this->isXHR() || isset($_GET['_escaped_fragment_'])) {
            if (isset($_POST['sc_login'])) {
                if (! isset($_POST['email']) || ! ValidationComponent::NotEmpty($_POST['email']) || ! ValidationComponent::Email($_POST['email']) || ! isset($_POST['password']) || ! ValidationComponent::NotEmpty($_POST['password'])) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 120,
                        'text' => __('system_120', true)
                    ));
                }
                $ClientModel = ClientModel::factory();
                $arr = $ClientModel->where('t1.email', $_POST['email'])
                    ->limit(1)
                    ->findAll()
                    ->getData();
                if (empty($arr)) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 121,
                        'text' => __('system_121', true)
                    ));
                }
                $arr = $arr[0];
                if ($arr['password'] != $_POST['password']) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 122,
                        'text' => __('system_122', true)
                    ));
                }
                if ($arr['status'] != 'T') {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 132,
                        'text' => __('system_132', true)
                    ));
                }
                $ClientModel->reset()
                    ->set('id', $arr['id'])
                    ->modify(array(
                    'last_login' => ':NOW()'
                ));
                $_SESSION[$this->defaultUser] = $arr;
                $hash = md5(SALT . $this->getUserId());
                $this->cart->transform($hash);
                $_SESSION[$this->defaultHash] = $hash;
                AppController::jsonResponse(array(
                    'status' => 'OK',
                    'code' => 212,
                    'text' => __('system_212', true)
                ));
            }
            $this->set('category_arr', CategoryModel::factory()->getNode($this->getFrontendLocaleId(), 1));
        }
    }
    
    public function Forgot()
    {
        if ($this->isXHR() || isset($_GET['_escaped_fragment_'])) {
            if (isset($_POST['sc_forgot'])) {
                if (! isset($_POST['email']) || ! ValidationComponent::NotEmpty($_POST['email']) || ! ValidationComponent::Email($_POST['email'])) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 123,
                        'text' => __('system_123', true)
                    ));
                }
                $arr = ClientModel::factory()->where('t1.email', $_POST['email'])
                    ->limit(1)
                    ->findAll()
                    ->getData();
                if (empty($arr)) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 124,
                        'text' => __('system_124', true)
                    ));
                }
                $arr = $arr[0];
                $Email = new EmailComponent();
                $Email->setContentType('text/html');
                if ($this->option_arr['o_send_email'] == 'smtp') {
                    $Email->setSmtpHost($this->option_arr['o_smtp_host'])
                        ->setSmtpUser($this->option_arr['o_smtp_user'])
                        ->setSmtpPass($this->option_arr['o_smtp_pass'])
                        ->setSmtpPort($this->option_arr['o_smtp_port']);
                }
                $body = $this->option_arr['o_email_password_reminder'];
                $subject = $this->option_arr['o_email_password_reminder_subject'];
                $MultiLangModel = MultiLangModel::factory();
                $lang_message = $MultiLangModel->reset()
                    ->select('t1.*')
                    ->where('t1.model', 'Option')
                    ->where('t1.locale', $this->getFrontendLocaleId())
                    ->where('t1.field', 'forgot_tokens')
                    ->limit(0, 1)
                    ->findAll()
                    ->getData();
                $lang_subject = $MultiLangModel->reset()
                    ->select('t1.*')
                    ->where('t1.model', 'Option')
                    ->where('t1.locale', $this->getFrontendLocaleId())
                    ->where('t1.field', 'forgot_subject')
                    ->limit(0, 1)
                    ->findAll()
                    ->getData();
                if (count($lang_message) === 1) {
                    $body = $lang_message[0]['content'];
                }
                if (count($lang_subject) === 1) {
                    $subject = $lang_subject[0]['content'];
                }
                $body = str_replace(array(
                    '{Name}',
                    '{Password}',
                    '{StoreName}'
                ), array(
                    $arr['client_name'],
                    $arr['password'],
                    __('lblStoreName', true)
                ), $body);
                $result = $Email->setTo($arr['email'])
                    ->setFrom($arr['email'])
                    ->setSubject($subject)
                    ->send($body);
                if (! $result) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 125,
                        'text' => __('system_125', true)
                    ));
                }
                AppController::jsonResponse(array(
                    'status' => 'OK',
                    'code' => 213,
                    'text' => __('system_213', true)
                ));
            }
            $this->set('category_arr', CategoryModel::factory()->getNode($this->getFrontendLocaleId(), 1));
        }
    }


    public function Profile()
    {
        if ($this->isXHR() || isset($_GET['_escaped_fragment_'])) {
            $ClientModel = ClientModel::factory();
            if (isset($_POST['sc_profile'])) {
                $ClientModel->beforeValidate($this->option_arr);
                if (! $ClientModel->validates($_POST)) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 126,
                        'text' => __('system_126', true)
                    ));
                }
                if (0 != $ClientModel->where('t1.email', $_POST['email'])
                    ->where('t1.id !=', $this->getUserId())
                    ->findCount()
                    ->getData()) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 127,
                        'text' => __('system_127', true)
                    ));
                }
                $ClientModel->reset()
                    ->set('id', $this->getUserId())
                    ->modify($_POST);
                $AddressModel = AddressModel::factory();
                $AddressModel->where('client_id', $this->getUserId());
                if (isset($_POST['name']) && ! empty($_POST['name'])) {
                    $AddressModel->whereNotIn('id', array_keys($_POST['name']));
                }
                $AddressModel->eraseAll();
                if (isset($_POST['name'])) {
                    $client_id = $this->getUserId();
                    $AddressModel->begin();
                    foreach ($_POST['name'] as $k => $v) {
                        if (empty($v)) {
                            continue;
                        }
                        if (strpos($k, 'new_') === 0) {
                            $AddressModel->reset()
                                ->setAttributes(array(
                                'client_id' => $client_id,
                                'country_id' => $_POST['country_id'][$k],
                                'state' => $_POST['state'][$k],
                                'city' => $_POST['city'][$k],
                                'zip' => $_POST['zip'][$k],
                                'address_1' => $_POST['address_1'][$k],
                                'address_2' => $_POST['address_2'][$k],
                                'name' => $_POST['name'][$k],
                                'is_default_shipping' => (@$_POST['is_default_shipping'] == $k ? 1 : 0),
                                'is_default_billing' => (@$_POST['is_default_billing'] == $k ? 1 : 0)
                            ))
                                ->insert();
                        } else {
                            $AddressModel->reset()
                                ->set('id', $k)
                                ->modify(array(
                                'country_id' => $_POST['country_id'][$k],
                                'state' => $_POST['state'][$k],
                                'city' => $_POST['city'][$k],
                                'zip' => $_POST['zip'][$k],
                                'address_1' => $_POST['address_1'][$k],
                                'address_2' => $_POST['address_2'][$k],
                                'name' => $_POST['name'][$k],
                                'is_default_shipping' => (@$_POST['is_default_shipping'] == $k ? 1 : 0),
                                'is_default_billing' => (@$_POST['is_default_billing'] == $k ? 1 : 0)
                            ));
                        }
                    }
                    $AddressModel->commit();
                }
                AppController::jsonResponse(array(
                    'status' => 'OK',
                    'code' => 214,
                    'text' => __('system_214', true)
                ));
            } else {
                $this->set('arr', $ClientModel->find($this->getUserId())
                    ->getData());
                $this->set('address_arr', AddressModel::factory()->where('t1.client_id', $this->getUserId())
                    ->orderBy('FIELD(`is_default_shipping`,1,0), FIELD(`is_default_billing`,1,0), t1.id ASC')
                    ->findAll()
                    ->getData());
                $this->set('country_arr', CountryModel::factory()->select('t1.*, t2.content AS name')
                    ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getFrontendLocaleId() . "'", 'left outer')
                    ->where('t1.status', 'T')
                    ->orderBy('`name` ASC')
                    ->findAll()
                    ->getData());
            }
            $this->set('category_arr', CategoryModel::factory()->getNode($this->getFrontendLocaleId(), 1));
        }
    }

    public function Register()
    {
        if ($this->isXHR() || isset($_GET['_escaped_fragment_'])) {
            if (isset($_POST['sc_register'])) {
                $ClientModel = ClientModel::factory();
                if (! isset($_POST['captcha']) || ! ValidationComponent::NotEmpty($_POST['captcha']) || ! isset($_SESSION[$this->defaultCaptcha]) || ! ValidationComponent::NotEmpty($_SESSION[$this->defaultCaptcha]) || ! CaptchaComponent::validate($_POST['captcha'], $_SESSION[$this->defaultCaptcha])) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 128,
                        'text' => __('system_128', true)
                    ));
                }
                $ClientModel->beforeValidate($this->option_arr);
                if (! $ClientModel->validates($_POST)) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 129,
                        'text' => __('system_129', true)
                    ));
                }
                if (0 != $ClientModel->where('t1.email', $_POST['email'])
                    ->findCount()
                    ->getData()) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 130,
                        'text' => __('system_130', true)
                    ));
                }
                $client_id = $ClientModel->setAttributes($_POST)
                    ->insert()
                    ->getInsertId();
                if ($client_id === FALSE || (int) $client_id <= 0) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 131,
                        'text' => __('system_131', true)
                    ));
                }
                $Email = new EmailComponent();
                $Email->setContentType('text/html');
                if ($this->option_arr['o_send_email'] == 'smtp') {
                    $Email->setSmtpHost($this->option_arr['o_smtp_host'])
                        ->setSmtpUser($this->option_arr['o_smtp_user'])
                        ->setSmtpPass($this->option_arr['o_smtp_pass'])
                        ->setSmtpPort($this->option_arr['o_smtp_port']);
                }
                $arr = $ClientModel->reset()
                    ->find($client_id)
                    ->getData();
                $body = $this->option_arr['o_email_new_registration'];
                $subject = $this->option_arr['o_email_new_registration_subject'];
                $MultiLangModel = MultiLangModel::factory();
                $lang_message = $MultiLangModel->reset()
                    ->select('t1.*')
                    ->where('t1.model', 'Option')
                    ->where('t1.locale', $this->getFrontendLocaleId())
                    ->where('t1.field', 'register_tokens')
                    ->limit(0, 1)
                    ->findAll()
                    ->getData();
                $lang_subject = $MultiLangModel->reset()
                    ->select('t1.*')
                    ->where('t1.model', 'Option')
                    ->where('t1.locale', $this->getFrontendLocaleId())
                    ->where('t1.field', 'register_subject')
                    ->limit(0, 1)
                    ->findAll()
                    ->getData();
                if (count($lang_message) === 1) {
                    $body = $lang_message[0]['content'];
                }
                if (count($lang_subject) === 1) {
                    $subject = $lang_subject[0]['content'];
                }
                $body = str_replace(array(
                    '{Name}',
                    '{Password}',
                    '{Email}',
                    '{Phone}',
                    '{URL}',
                    '{StoreName}'
                ), array(
                    $arr['client_name'],
                    $arr['password'],
                    $arr['email'],
                    $arr['phone'],
                    $arr['url'],
                    __('lblStoreName', true)
                ), $body);
                $result = $Email->setTo($arr['email'])
                    ->setFrom($arr['email'])
                    ->setSubject($subject)
                    ->send($body);
                if (! $result) {
                    AppController::jsonResponse(array(
                        'status' => 'OK',
                        'code' => 215,
                        'text' => __('system_215', true)
                    ));
                }
                AppController::jsonResponse(array(
                    'status' => 'OK',
                    'code' => 216,
                    'text' => __('system_216', true)
                ));
            }
            $this->set('category_arr', CategoryModel::factory()->getNode($this->getFrontendLocaleId(), 1));
        }
    }


    public function Product()
    {
        $this->checkMaintainMode();
        $this->set('page','product');
        $id = $this->request->params['id'];
        if (!empty($id)) {
            
            $order_arr = array();
            $cart_arr = $this->get('cart_arr');
            foreach ($cart_arr as $cart_item) {
                if (! isset($order_arr[$cart_item['stock_id']])) {
                    $order_arr[$cart_item['stock_id']] = 0;
                }
                $order_arr[$cart_item['stock_id']] += $cart_item['qty'];
            }
            $GalleryModel = GalleryModel::factory();
            $StockModel = StockModel::factory();
            $ProductCategoryModel = ProductCategoryModel::factory();
            $arr = ProductModel::factory()->select(sprintf("t1.*, t2.content AS name, t3.content AS full_desc, t4.content AS short_desc,  (SELECT MIN(`price`) FROM `%2\$s`  WHERE `product_id` = `t1`.`id` AND `qty` > 0  LIMIT 1) AS `price`,  (SELECT MAX(`price`) FROM `%2\$s`  WHERE `product_id` = `t1`.`id` AND `qty` > 0  LIMIT 1) AS `max_price`,  (SELECT `id` FROM `%2\$s`  WHERE `product_id` = `t1`.`id`  ORDER BY `price` ASC  LIMIT 1) AS `stockId`,  (SELECT CONCAT_WS('~:~', `medium_path`, `large_path`) FROM `%1\$s`  WHERE `foreign_id` = `t1`.`id`  ORDER BY ISNULL(`sort`), `sort` ASC, `id` ASC  LIMIT 1) AS `pic`,  (SELECT GROUP_CONCAT(`category_id`) FROM `%3\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `category_ids`  ", $GalleryModel->getTable(), $StockModel->getTable(), $ProductCategoryModel->getTable()))
                ->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='name'", 'left outer')
                ->join('MultiLang', "t3.model='Product' AND t3.foreign_id=t1.id AND t3.locale='" . $this->getFrontendLocaleId() . "' AND t3.field='full_desc'", 'left outer')
                ->join('MultiLang', "t4.model='Product' AND t4.foreign_id=t1.id AND t4.locale='" . $this->getFrontendLocaleId() . "' AND t4.field='short_desc'", 'left outer')
                ->find($id)
                ->toArray('category_ids', ',')
                ->getData();
            if (! empty($arr)) {
                if ($arr['status'] != 2) {
                    $arr['gallery_arr'] = $GalleryModel->select('t1.small_path, t1.medium_path, t1.large_path, t1.alt')
                        ->where('t1.foreign_id', $arr['id'])
                        ->orderBy('t1.sort ASC')
                        ->findAll()
                        ->getData();
                    $StockAttributeModel = StockAttributeModel::factory();
                    $ExtraItemModel = ExtraItemModel::factory();
                    $arr['image_arr'] = $StockModel->select('t1.id AS stock_id, t2.medium_path, t2.large_path, t2.alt AS title')
                        ->join('Gallery', 't2.id=t1.image_id', 'inner')
                        ->where('t1.product_id', $arr['id'])
                        ->orderBy('ISNULL(t2.sort), t2.sort ASC, t2.id ASC')
                        ->findAll()
                        ->getData();
                    $extra_arr = ExtraModel::factory()->select('t1.*, t2.content AS name, t3.content AS title')
                        ->join('MultiLang', "t2.model='Extra' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='extra_name'", 'left outer')
                        ->join('MultiLang', "t3.model='Extra' AND t3.foreign_id=t1.id AND t3.locale='" . $this->getFrontendLocaleId() . "' AND t3.field='extra_title'", 'left outer')
                        ->where('t1.product_id', $arr['id'])
                        ->orderBy('`title` ASC, `name` ASC')
                        ->findAll()
                        ->getData();
                    $locale_id = $this->getFrontendLocaleId();
                    foreach ($extra_arr as $k => $extra) {
                        $extra_arr[$k]['extra_items'] = $ExtraItemModel->reset()
                            ->select('t1.*, t2.content AS name')
                            ->join('MultiLang', "t2.model='ExtraItem' AND t2.foreign_id=t1.id AND t2.locale='$locale_id' AND t2.field='extra_name'", 'left outer')
                            ->where('t1.extra_id', $extra['id'])
                            ->orderBy('t1.price ASC')
                            ->findAll()
                            ->getData();
                    }
                    $this->set('extra_arr', $extra_arr);
                    $attr_arr = array();
                    $a_arr = AttributeModel::factory()->select('t1.id, t1.product_id, t1.parent_id, t2.content AS name')
                        ->join('MultiLang', "t2.model='Attribute' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->GetLocale() . "'", 'left outer')
                        ->where('t1.product_id', $arr['id'])
                        ->where(sprintf("(CONCAT_WS('_', t1.id, t1.parent_id) IN (  SELECT CONCAT_WS('_', TSA.attribute_id, TSA.attribute_parent_id)  FROM `%s` AS `TSA`  INNER JOIN `%s` AS `TS` ON TS.id = TSA.stock_id AND TS.qty > 0  WHERE TSA.product_id = t1.product_id  ) OR t1.parent_id IS NULL OR t1.parent_id = '0')", $StockAttributeModel->getTable(), $StockModel->getTable()))
                        ->orderBy('t1.`order_group` ASC, `order_item` ASC')
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
                    $this->set('attr_arr', array_values($attr_arr));
                    $stock_arr = $StockModel->reset()
                        ->select('t1.*, t2.small_path')
                        ->join('Gallery', 't2.id=t1.image_id', 'left outer')
                        ->where('t1.product_id', $arr['id'])
                        ->where('t1.qty > 0')
                        ->findAll()
                        ->getData();
                    $_arr = array();
                    foreach ($stock_arr as $k => $stock) {
                        $_qty = $stock['qty'];
                        if (isset($order_arr[$stock['id']])) {
                            $_qty -= $order_arr[$stock['id']];
                            if ($_qty < 1) {
                                unset($stock_arr[$k]);
                                continue;
                            }
                        }
                        $stock_arr[$k]['qty'] = $_qty;
                        $_arr[$stock['id']] = $StockAttributeModel->reset()
                            ->where('t1.stock_id', $stock['id'])
                            ->orderBy('t1.attribute_id ASC')
                            ->findAll()
                            ->getDataPair('attribute_parent_id', 'attribute_id');
                    }
                    $this->set('stock_attr_arr', $_arr);
                    $this->set('stock_arr', array_values($stock_arr));
                } else {
                    $arr = array();
                }
            }
            $similar_arr = ProductSimilarModel::factory()->select(sprintf("t2.*, t3.content AS name,  (SELECT `medium_path` FROM `%1\$s` WHERE `foreign_id` = `t1`.`similar_id` ORDER BY ISNULL(`sort`), `sort` ASC, `id` ASC LIMIT 1) AS `pic`,  (SELECT MIN(`price`) FROM `%2\$s` WHERE `product_id` = `t1`.`similar_id` LIMIT 1) AS `price`,  (SELECT GROUP_CONCAT(`category_id`) FROM `%3\$s` WHERE `product_id` = `t1`.`similar_id` LIMIT 1) AS `category_ids`  ", $GalleryModel->getTable(), $StockModel->getTable(), $ProductCategoryModel->getTable()))
            ->join('Product', 't2.id=t1.similar_id AND t2.status!=2', 'inner')
            ->join('MultiLang', "t3.model='Product' AND t3.foreign_id=t2.id AND t3.field='name' AND t3.locale='" . $this->getFrontendLocaleId() . "'", 'left outer')
            ->where('t1.product_id', $id)
            ->where('t2.status', 1)
            ->orderBy('`name` ASC')
            ->limit(5)
            ->findAll()
            ->toArray('category_ids', ',')
            ->getData();
            if (!empty($similar_arr)) {
                $similar_arr = UtilComponent::attachCustomLinks($similar_arr, RouterModel::TYPE_PRODUCT, $this->getFrontendLocaleId());
                $similar_arr = UtilComponent::attachVouchers($similar_arr, 'm');
            }
            $arr = UtilComponent::attachVouchers($arr, 's');
            $this->title = !empty($arr['name'])?$arr['name'] : '';
            $this->set('product_arr', $arr)
                ->set('category_arr', CategoryModel::factory()->getNode($this->getFrontendLocaleId(), 1))
                ->set('similar_arr', $similar_arr);
            $tags = TagModel::factory()->select('t1.*, t2.content as name')
                ->join ('MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Tag' AND t2.locale = '" . $this->getFrontendLocaleId () . "' AND t2.field = 'name'", 'left' )
                ->join('ProductTag',"t3.tag_id = t1.id")
                ->where('t3.product_id', $id)
                ->findAll()
                ->getData();
            
            if (!empty($tags)) {
                $tags = UtilComponent::attachCustomLinks($tags, RouterModel::TYPE_TAG, $this->getFrontendLocaleId());
                $this->set('tag_arr', $tags);
            }
            
        }
    }


    public function Products()
    {
        $this->checkMaintainMode();
        $this->set('page','products');
        $q = @$this->request->params['q'];
        $category_id = (int)@$this->request->params['category_id'];
        $page = @$this->request->params['page'];
        
        $order_arr = array();
        $cart_arr = $this->get('cart_arr');
        foreach ($cart_arr as $cart_item) {
            if (! isset($order_arr[$cart_item['stock_id']])) {
                $order_arr[$cart_item['stock_id']] = 0;
            }
            $order_arr[$cart_item['stock_id']] += $cart_item['qty'];
        }
        $sortOrder = '';
        if (!empty($category_id)) {
            $sortType = ItemSortModel::TYPE_PRODUCT_CATEGORY;
            $ProductModel = ProductModel::factory()->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='name'", 'left outer')
            ->join('MultiLang', "t3.model='Product' AND t3.foreign_id=t1.id AND t3.locale='" . $this->getFrontendLocaleId() . "' AND t3.field='short_desc'", 'left outer')
            ->join ('ItemSort', "t4.foreign_id = t1.id AND t4.foreign_type_id = $category_id AND t4.type = $sortType", 'left')
            ->where('t1.status', ProductModel::STATUS_ACTIVE);
            $sortOrder = "`sort` DESC,";
        } else {
            $ProductModel = ProductModel::factory()->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='name'", 'left outer')
            ->join('MultiLang', "t3.model='Product' AND t3.foreign_id=t1.id AND t3.locale='" . $this->getFrontendLocaleId() . "' AND t3.field='short_desc'", 'left outer')
            ->where('t1.status', ProductModel::STATUS_ACTIVE);
        }

        if (isset($category_id) && (int) $category_id > 0) {
            $ProductModel->where(sprintf("t1.id IN (SELECT `product_id` FROM `%s` WHERE `category_id` = '%u')", ProductCategoryModel::factory()->getTable(), (int) $category_id));
        }
        if (isset($q) && ! empty($q)) {
            $q = str_replace(array(
                '_',
                '%'
            ), array(
                '\_',
                '\%'
            ), $ProductModel->escapeStr(trim(urldecode($q))));
            $ProductModel->where("(t2.content LIKE '%$q%' OR t3.content LIKE '%$q%')");
            $this->title = "Kết quả tìm kiếm";
        }
        $page = isset($page) && (int) $page > 0 ? intval($page) : 1;
        $row_count = (int) $this->option_arr['o_products_per_page'] > 0 ? (int) $this->option_arr['o_products_per_page'] : 10;
        $offset = ((int) $page - 1) * $row_count;
        $count = $ProductModel->findCount()->getData();
        $pages = ceil($count / $row_count);
        if (!empty($category_id)) {
            $product_arr = $ProductModel->select(sprintf("t1.*, t2.content AS `name`, t4.sort AS `sort`,  (SELECT MIN(`price`) FROM `%2\$s`  WHERE `product_id` = `t1`.`id` AND `qty` > 0  LIMIT 1) AS `min_price`,  (SELECT MAX(`price`) FROM `%2\$s`  WHERE `product_id` = `t1`.`id` AND `qty` > 0  LIMIT 1) AS `max_price`,  (SELECT `id` FROM `%2\$s`  WHERE `product_id` = `t1`.`id` AND `qty` > 0  ORDER BY `price` ASC  LIMIT 1) AS `stockId`,  (SELECT `qty` FROM `%2\$s`  WHERE `id` = `stockId`  LIMIT 1) AS `stockQty`,  (SELECT GROUP_CONCAT(CONCAT_WS('_', attribute_id, attribute_parent_id))  FROM `%3\$s`  WHERE `product_id` = `t1`.`id` AND `stock_id` = `stockId`  LIMIT 1) AS `stockId_attr`,  (SELECT `medium_path` FROM `%1\$s`  WHERE `foreign_id` = `t1`.`id`  ORDER BY ISNULL(`sort`), `sort` ASC, `id` ASC  LIMIT 1) AS `pic`,  (SELECT GROUP_CONCAT(`category_id`) FROM `%4\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `category_ids`,  (SELECT GROUP_CONCAT(CONCAT_WS('.', `id`, IF(`type`='single',NULL,(SELECT `id` FROM `%6\$s` WHERE `extra_id` = te.id ORDER BY `price` ASC LIMIT 1)))) FROM `%5\$s` AS `te` WHERE `product_id` = t1.id AND `is_mandatory` = '1' LIMIT 1) AS `m_extras`", GalleryModel::factory()->getTable(), StockModel::factory()->getTable(), StockAttributeModel::factory()->getTable(), ProductCategoryModel::factory()->getTable(), ExtraModel::factory()->getTable(), ExtraItemModel::factory()->getTable(), AttributeModel::factory()->getTable()))
            ->orderBy("$sortOrder`is_featured` DESC, `name` ASC")
            ->limit($row_count, $offset)
            ->findAll()
            ->toArray('category_ids', ',')
            ->toArray('m_extras', ',')
            ->getData();
        } else {
            $product_arr = $ProductModel->select(sprintf("t1.*, t2.content AS `name`,  (SELECT MIN(`price`) FROM `%2\$s`  WHERE `product_id` = `t1`.`id` AND `qty` > 0  LIMIT 1) AS `min_price`,  (SELECT MAX(`price`) FROM `%2\$s`  WHERE `product_id` = `t1`.`id` AND `qty` > 0  LIMIT 1) AS `max_price`,  (SELECT `id` FROM `%2\$s`  WHERE `product_id` = `t1`.`id` AND `qty` > 0  ORDER BY `price` ASC  LIMIT 1) AS `stockId`,  (SELECT `qty` FROM `%2\$s`  WHERE `id` = `stockId`  LIMIT 1) AS `stockQty`,  (SELECT GROUP_CONCAT(CONCAT_WS('_', attribute_id, attribute_parent_id))  FROM `%3\$s`  WHERE `product_id` = `t1`.`id` AND `stock_id` = `stockId`  LIMIT 1) AS `stockId_attr`,  (SELECT `medium_path` FROM `%1\$s`  WHERE `foreign_id` = `t1`.`id`  ORDER BY ISNULL(`sort`), `sort` ASC, `id` ASC  LIMIT 1) AS `pic`,  (SELECT GROUP_CONCAT(`category_id`) FROM `%4\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `category_ids`,  (SELECT GROUP_CONCAT(CONCAT_WS('.', `id`, IF(`type`='single',NULL,(SELECT `id` FROM `%6\$s` WHERE `extra_id` = te.id ORDER BY `price` ASC LIMIT 1)))) FROM `%5\$s` AS `te` WHERE `product_id` = t1.id AND `is_mandatory` = '1' LIMIT 1) AS `m_extras`", GalleryModel::factory()->getTable(), StockModel::factory()->getTable(), StockAttributeModel::factory()->getTable(), ProductCategoryModel::factory()->getTable(), ExtraModel::factory()->getTable(), ExtraItemModel::factory()->getTable(), AttributeModel::factory()->getTable()))
            ->orderBy("`is_featured` DESC, `name` ASC")
            ->limit($row_count, $offset)
            ->findAll()
            ->toArray('category_ids', ',')
            ->toArray('m_extras', ',')
            ->getData();
        }
        if (!empty($product_arr)) {
            $product_arr = UtilComponent::attachCustomLinks($product_arr, RouterModel::TYPE_PRODUCT, $this->getFrontendLocaleId());
            $product_arr = UtilComponent::attachVouchers($product_arr, 'm');
        }
        $this->set('product_arr', $product_arr);
        $category_arr = CategoryModel::factory()->getNode($this->getFrontendLocaleId(), 1);
        foreach ($category_arr as &$category) {
            $category['data'] = UtilComponent::attachSingleCustomLink($category['data'], RouterModel::TYPE_PRODUCT_CATEGORY, $this->getFrontendLocaleId());
        }
        $cat = CategoryModel::factory()
        ->select("t1.*, t2.content AS `name`, t3.content AS `description`")
        ->join('MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Category' AND t2.locale = '".$this->getFrontendLocaleId()."' AND t2.field = 'name'", 'left outer')
        ->join('MultiLang', "t3.foreign_id = t1.id AND t3.model = 'Category' AND t3.locale = '".$this->getFrontendLocaleId()."' AND t3.field = 'description'", 'left outer')
        ->where('t1.id', $category_id)
        ->findAll()
        ->first();
        if (!empty($cat)) {
            $cat = UtilComponent::attachSingleCustomLink($cat, RouterModel::TYPE_PRODUCT_CATEGORY, $this->getFrontendLocaleId());
            $this->set('category', $cat);
            $this->title = $cat['name'];
        }
        $this->set('order_arr', $order_arr)
            ->set('paginator', compact('pages', 'page', 'count', 'row_count', 'offset'))
            ->set('category_arr', $category_arr);
    }

    public function Favs()
    {
        if ($this->isXHR() || isset($_GET['_escaped_fragment_'])) {
            if (isset($_COOKIE[$this->defaultCookie]) && ! empty($_COOKIE[$this->defaultCookie])) {
                $favs = unserialize(stripslashes($_COOKIE[$this->defaultCookie]));
                $arr = $extra_arr = $attr_arr = $stock_arr = $image_arr = $product_id = $stock_id = array();
                foreach ($favs as $fav => $whatever) {
                    $item = unserialize($fav);
                    $product_id[] = $item['product_id'];
                    $stock_id[] = $item['stock_id'];
                }
                if (! empty($product_id)) {
                    $arr = ProductModel::factory()->select(sprintf("t1.*, t2.content AS name,  (SELECT GROUP_CONCAT(`category_id`) FROM `%1\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `category_ids`  ", ProductCategoryModel::factory()->getTable()))
                        ->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='name'", 'left outer')
                        ->whereIn('t1.id', $product_id)
                        ->where('t1.status', 1)
                        ->findAll()
                        ->toArray('category_ids', ',')
                        ->getData();
                    $extra_arr = ExtraModel::factory()->select('t1.*, t2.content AS name, t3.content AS title')
                        ->join('MultiLang', "t2.model='Extra' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='extra_name'", 'left outer')
                        ->join('MultiLang', "t3.model='Extra' AND t3.foreign_id=t1.id AND t3.locale='" . $this->getFrontendLocaleId() . "' AND t3.field='extra_title'", 'left outer')
                        ->whereIn('t1.product_id', $product_id)
                        ->orderBy('`title` ASC, `name` ASC')
                        ->findAll()
                        ->getData();
                    if (! empty($extra_arr)) {
                        $locale_id = $this->getFrontendLocaleId();
                        $ExtraItemModel = ExtraItemModel::factory();
                        foreach ($extra_arr as $k => $extra) {
                            $extra_arr[$k]['extra_items'] = $ExtraItemModel->reset()
                                ->select('t1.*, t2.content AS name')
                                ->join('MultiLang', "t2.model='ExtraItem' AND t2.foreign_id=t1.id AND t2.locale='$locale_id' AND t2.field='extra_name'", 'left outer')
                                ->where('t1.extra_id', $extra['id'])
                                ->orderBy('t1.price ASC')
                                ->findAll()
                                ->getData();
                        }
                    }
                    $a_arr = AttributeModel::factory()->select('t1.id, t1.product_id, t1.parent_id, t2.content AS name')
                        ->join('MultiLang', "t2.model='Attribute' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->GetLocale() . "'", 'left outer')
                        ->whereIn('t1.product_id', $product_id)
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
                }
                if (! empty($stock_id)) {
                    $stock_arr = StockModel::factory()->whereIn('t1.id', $stock_id)
                        ->findAll()
                        ->getDataPair('id', 'price');
                    $image_arr = StockModel::factory()->select('t1.id, t2.small_path')
                        ->join('Gallery', 't2.id=t1.image_id', 'left outer')
                        ->whereIn('t1.id', $stock_id)
                        ->findAll()
                        ->getDataPair('id', 'small_path');
                }
                $this->set('arr', $arr);
                $this->set('extra_arr', $extra_arr);
                $this->set('attr_arr', array_values($attr_arr));
                $this->set('stock_arr', $stock_arr);
                $this->set('image_arr', $image_arr);
            }
            $this->set('category_arr', CategoryModel::factory()->getNode($this->getFrontendLocaleId(), 1));
        }
    }
    
    public function OrderConfirm()
    {
        $this->checkMaintainMode();
        $this->set('page','order_confirm');
        $this->title = "Thông báo đặt hàng";
        $fail = @$this->request->params['fail'];
        if (!empty($fail)) {
            $this->set('fail', $fail);
        }
    }
}
?>