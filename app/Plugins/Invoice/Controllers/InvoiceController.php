<?php
namespace App\Plugins\Invoice\Controllers;

use Core\Framework\Objects;
use App\Plugins\Country\Models\CountryModel;
use App\Controllers\Components\UtilComponent;
use App\Controllers\AppController;
use Core\Framework\Components\ValidationComponent;
use Core\Framework\Components\EmailComponent;
use Core\Framework\Components\ImageComponent;
use App\Models\MultiLangModel;
use App\Plugins\Invoice\Models\InvoiceModel;
use App\Plugins\Invoice\Models\InvoiceItemModel;
use App\Plugins\Invoice\Models\InvoiceConfigModel;

class InvoiceController extends InvoiceAppController
{

    public $invoiceErrors = 'InvoiceErrors';


    private function sortTimezones(Array $array)
    {
        $ordered = array();
        $orderArray = array(
            '-43200',
            '-39600',
            '-36000',
            '-32400',
            '-28800',
            '-25200',
            '-21600',
            '-18000',
            '-14400',
            '-10800',
            '-7200',
            '-3600',
            '0',
            '3600',
            '7200',
            '10800',
            '14400',
            '18000',
            '21600',
            '25200',
            '28800',
            '32400',
            '36000',
            '39600',
            '43200',
            '46800'
        );
        foreach ($orderArray as $key) {
            if (array_key_exists($key, $array)) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }
        return $ordered + $array;
    }


    public function CheckUniqueId()
    {
        $this->setAjax(true);
        if ($this->isXHR() && isset($_GET['uuid'])) {
            $InvoiceModel = InvoiceModel::factory();
            if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
                $InvoiceModel->where('t1.id !=', $_GET['id']);
            }
            echo $InvoiceModel->where('t1.uuid', $_GET['uuid'])
                ->findCount()
                ->getData() == 0 ? 'true' : 'false';
        }
        exit();
    }

    public function AddItem()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged() && $this->isInvoiceReady()) {
            if (isset($_POST['invoice_add'])) {
                $insert_id = InvoiceItemModel::factory($_POST)->insert()->getInsertId();
                if ($insert_id !== false && (int) $insert_id > 0) {
                    $InvoiceModel = InvoiceModel::factory();
                    $invoice = $InvoiceModel->find($_POST['invoice_id'])->getData();
                    if (! empty($invoice)) {
                        $total = (float) $invoice['total'] + (float) $_POST['amount'];
                        $InvoiceModel->modify(array(
                            'total' => $total
                        ));
                        AppController::jsonResponse(array(
                            'status' => 'OK',
                            'code' => 200,
                            'text' => '',
                            'total' => $total
                        ));
                    }
                    AppController::jsonResponse(array(
                        'status' => 'OK',
                        'code' => 200,
                        'text' => ''
                    ));
                }
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 100,
                    'text' => ''
                ));
            }
            if (isset($_GET['invoice_id']) && (int) $_GET['invoice_id'] > 0) {
                $this->set('arr', InvoiceModel::factory()->find($_GET['invoice_id'])
                    ->getData());
            }
            $this->set('config_arr', InvoiceConfigModel::factory()->find(1)
                ->getData());
        }
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
        $invoice_arr = $InvoiceModel->where('t1.uuid', $_POST['x_invoice_num'])
            ->limit(1)
            ->findAll()
            ->getData();
        if (empty($invoice_arr)) {
            $this->log('Invoice not found');
            exit();
        }
        $invoice_arr = $invoice_arr[0];
        $config_arr = InvoiceConfigModel::factory()->find(1)->getData();
        $params = array(
            'transkey' => @$config_arr['p_authorize_key'],
            'x_login' => @$config_arr['p_authorize_mid'],
            'md5_setting' => @$config_arr['p_authorize_hash'],
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
            $this->log('Invoice confirmed as paid');
            $InvoiceModel->reset()
                ->set('id', $invoice_arr['id'])
                ->modify(array(
                'status' => 'paid',
                'modified' => ':NOW()'
            ));
        } elseif (! $response) {
            $this->log('Authorization failed');
        } else {
            $this->log('Invoice not confirmed as paid. ' . $response['response_reason_text']);
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
        $invoice_arr = $InvoiceModel->where('t1.uuid', $_POST['custom'])
            ->limit(1)
            ->findAll()
            ->getData();
        if (empty($invoice_arr)) {
            $this->log('Invoice not found');
            exit();
        }
        $invoice_arr = $invoice_arr[0];
        $config_arr = InvoiceConfigModel::factory()->find(1)->getData();
        $params = array(
            'txn_id' => @$invoice_arr['txn_id'],
            'paypal_address' => $config_arr['p_paypal_address'],
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
            $this->log('Invoice confirmed as paid');
            $InvoiceModel->reset()
                ->set('id', $invoice_arr['id'])
                ->modify(array(
                'status' => 'paid',
                'txn_id' => $response['transaction_id'],
                'processed_on' => ':NOW()'
            ));
        } elseif (! $response) {
            $this->log('Authorization failed');
        } else {
            $this->log('Invoice not confirmed as paid');
        }
        exit();
    }

    public function Create()
    {
        $params = $this->getParams();
        if (! isset($params['key']) || $params['key'] != md5($this->option_arr['private_key'] . SALT)) {
            return array(
                'status' => 'ERR',
                'code' => '101',
                'text' => 'Key is not set or invalid'
            );
        }
        $config = InvoiceConfigModel::factory()->find(1)->getData();
        $config['id'] = NULL;
        unset($config['id']);
        $data = array_merge($config, $params);
        $invoice_id = InvoiceModel::factory($data)->insert()->getInsertId();
        if ($invoice_id !== FALSE && (int) $invoice_id > 0) {
            if (isset($params['items']) && is_array($params['items']) && ! empty($params['items'])) {
                $InvoiceItemModel = InvoiceItemModel::factory();
                foreach ($params['items'] as $item) {
                    $item['invoice_id'] = $invoice_id;
                    $InvoiceItemModel->reset()
                        ->setAttributes($item)
                        ->insert();
                }
            }
            return array(
                'status' => 'OK',
                'code' => '200',
                'text' => 'Invoice has been created.',
                'data' => array_merge($data, array(
                    'id' => $invoice_id
                ))
            );
        } else {
            return array(
                'status' => 'ERR',
                'code' => '100',
                'text' => 'Invoice has not been created.'
            );
        }
    }


    public function CreateInvoice()
    {
        $this->checkLogin();
        if (! $this->isInvoiceReady()) {
            $this->set('status', 2);
            return;
        }
        $InvoiceModel = InvoiceModel::factory();
        $InvoiceItemModel = InvoiceItemModel::factory();
        if (isset($_POST['invoice_create'])) {
            $data = array();
            $data['foreign_id'] = $this->getForeignId();
            $data['issue_date'] = ! empty($_POST['issue_date']) ? UtilComponent::formatDate($_POST['issue_date'], $this->option_arr['o_date_format']) : NULL;
            $data['due_date'] = ! empty($_POST['due_date']) ? UtilComponent::formatDate($_POST['due_date'], $this->option_arr['o_date_format']) : NULL;
            $data['s_date'] = ! empty($_POST['s_date']) ? UtilComponent::formatDate($_POST['s_date'], $this->option_arr['o_date_format']) : NULL;
            $data = array_merge($_POST, $data);
            if (! $InvoiceModel->validates($data)) {
                UtilComponent::redirect(BASE_URL . "index.php?controller=Invoice&action=Invoices&err=PIN06");
            }
            $invoice_id = $InvoiceModel->setAttributes($data)
                ->insert()
                ->getInsertId();
            if ($invoice_id !== false && (int) $invoice_id > 0) {
                $InvoiceItemModel->where('tmp', $_POST['tmp'])->modifyAll(array(
                    'invoice_id' => $invoice_id,
                    'tmp' => ":NULL"
                ));
                $err = "PIN07";
            } else {
                $err = "PIN08";
            }
            UtilComponent::redirect(BASE_URL . "index.php?controller=Invoice&action=Invoices&err=$err");
        } else {
            if (isset($_REQUEST['items']) && ! empty($_REQUEST['items'])) {
                $InvoiceItemModel->where('tmp', $_REQUEST['tmp'])->eraseAll();
                foreach ($_REQUEST['items'] as $item) {
                    $item['tmp'] = $_REQUEST['tmp'];
                    $InvoiceItemModel->reset()
                        ->setAttributes($item)
                        ->insert();
                }
            }
            $this->set('uuid', InvoiceModel::factory()->getInvoiceID());
            $this->set('config_arr', InvoiceConfigModel::factory()->find(1)
                ->getData());
            $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/')
                ->appendJs('jquery.validate.min.js', $this->getConst('PLUGIN_LIBS_PATH'))
                ->appendJs('Invoice.js', $this->getConst('PLUGIN_JS_PATH'))
                ->appendJs('index.php?controller=Admin&action=Messages', BASE_URL, true);
        }
    }

    public function Delete()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged() && $this->isInvoiceReady()) {
            $response = array();
            if (InvoiceModel::factory()->set('id', $_GET['id'])
                ->erase()
                ->getAffectedRows() == 1) {
                InvoiceItemModel::factory()->where('invoice_id', $_GET['id'])->eraseAll();
                $response['code'] = 200;
            } else {
                $response['code'] = 100;
            }
            AppController::jsonResponse($response);
        }
        exit();
    }

    public function DeleteBulk()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged() && $this->isInvoiceReady()) {
            if (isset($_POST['record']) && count($_POST['record']) > 0) {
                InvoiceModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
                InvoiceItemModel::factory()->whereIn('invoice_id', $_POST['record'])->eraseAll();
            }
        }
        exit();
    }

    public function DeleteItem()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged() && $this->isInvoiceReady()) {
            $InvoiceItemModel = InvoiceItemModel::factory();
            $invoice_item = $InvoiceItemModel->find($_GET['id'])->getData();
            if (! empty($invoice_item) && $InvoiceItemModel->erase()->getAffectedRows() == 1) {
                $InvoiceModel = InvoiceModel::factory();
                $invoice = $InvoiceModel->find($invoice_item['invoice_id'])->getData();
                if (! empty($invoice)) {
                    $total = (float) $invoice['total'] - (float) $invoice_item['amount'];
                    $InvoiceModel->modify(array(
                        'total' => $total
                    ));
                    AppController::jsonResponse(array(
                        'status' => 'OK',
                        'code' => 200,
                        'text' => '',
                        'total' => $total
                    ));
                }
                AppController::jsonResponse(array(
                    'status' => 'OK',
                    'code' => 200,
                    'text' => ''
                ));
            }
            AppController::jsonResponse(array(
                'status' => 'ERR',
                'code' => 100,
                'text' => ''
            ));
        }
        exit();
    }


    public function DeleteLogo()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged() && $this->isInvoiceReady()) {
            $InvoiceConfigModel = InvoiceConfigModel::factory();
            $arr = $InvoiceConfigModel->find(1)->getData();
            if (! empty($arr) && ! empty($arr['y_logo'])) {
                @clearstatcache();
                if (is_file($arr['y_logo'])) {
                    @unlink($arr['y_logo']);
                }
                $InvoiceConfigModel->set('id', 1)->modify(array(
                    'y_logo' => ':NULL'
                ));
            }
        }
        exit();
    }


    public function EditItem()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged() && $this->isInvoiceReady()) {
            if (isset($_POST['invoice_edit'])) {
                InvoiceItemModel::factory()->set('id', $_POST['id'])->modify($_POST);
                $response = array(
                    'code' => 200
                );
                AppController::jsonResponse($response);
            }
            if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
                $this->set('arr', InvoiceItemModel::factory()->select('t1.*, t2.currency')
                    ->join('Invoice', 't2.id=t1.invoice_id', 'left outer')
                    ->find($_GET['id'])
                    ->getData());
                $this->set('config_arr', InvoiceConfigModel::factory()->find(1)
                    ->getData());
            }
        }
    }


    public function GetInvoices()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged() && $this->isInvoiceReady()) {
            $InvoiceModel = InvoiceModel::factory();
            if (isset($_GET['foreign_id'])) {
                $foreign_arr = $this->get('foreign_arr');
                if ((int) $_GET['foreign_id'] > 0 && $foreign_arr !== FALSE && ! empty($foreign_arr)) {
                    $InvoiceModel->where('t1.foreign_id', $_GET['foreign_id']);
                }
            } else {
                $InvoiceModel->where('t1.foreign_id', $this->getForeignId());
            }
            if (isset($_GET['q']) && ! empty($_GET['q'])) {
                $q = $InvoiceModel->escapeStr($_GET['q']);
                $q = str_replace(array(
                    '%',
                    '_'
                ), array(
                    '\%',
                    '\_'
                ), $q);
                $InvoiceModel->where('t1.uuid LIKE', "%$q%")
                    ->orWhere('t1.order_id LIKE', "%$q%")
                    ->orWhere('t1.b_company LIKE', "%$q%")
                    ->orWhere('t1.b_name LIKE', "%$q%")
                    ->orWhere('t1.b_email LIKE', "%$q%")
                    ->orWhere('t1.s_company LIKE', "%$q%")
                    ->orWhere('t1.s_name LIKE', "%$q%")
                    ->orWhere('t1.s_email LIKE', "%$q%");
            }
            $column = 'created';
            $direction = 'DESC';
            if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                'ASC',
                'DESC'
            ))) {
                $column = $_GET['column'];
                $direction = strtoupper($_GET['direction']);
            }
            $total = $InvoiceModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $data = $InvoiceModel->orderBy("`$column` $direction")
                ->limit($rowCount, $offset)
                ->findAll()
                ->getData();
            foreach ($data as $k => $v) {
                $data[$k]['total_formated'] = UtilComponent::formatCurrencySign(number_format($v['total'], 2), ! empty($v['currency']) ? $v['currency'] : $this->option_arr['o_currency']);
            }
            AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
        }
        exit();
    }

    public function GetItems()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged() && $this->isInvoiceReady()) {
            $InvoiceItemModel = InvoiceItemModel::factory();
            $column = 'id';
            $direction = 'ASC';
            if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                'ASC',
                'DESC'
            ))) {
                $column = $_GET['column'];
                $direction = strtoupper($_GET['direction']);
            }
            $InvoiceItemModel->where('t1.id', - 1);
            if (isset($_GET['invoice_id']) && (int) $_GET['invoice_id'] > 0) {
                $InvoiceItemModel->reset()->where('t1.invoice_id', $_GET['invoice_id']);
            }
            if (isset($_GET['tmp']) && ! empty($_GET['tmp'])) {
                $InvoiceItemModel->reset()->where('t1.tmp', $_GET['tmp']);
            }
            $data = $InvoiceItemModel->select('t1.*, t2.currency')
                ->join('Invoice', 't2.id=t1.invoice_id', 'left outer')
                ->orderBy("`$column` $direction")
                ->findAll()
                ->getData();
            foreach ($data as $k => $v) {
                $data[$k]['unit_price_formated'] = UtilComponent::formatCurrencySign(number_format($v['unit_price'], 2), ! empty($v['currency']) ? $v['currency'] : $this->option_arr['o_currency']);
                $data[$k]['amount_formated'] = UtilComponent::formatCurrencySign(number_format($v['amount'], 2), ! empty($v['currency']) ? $v['currency'] : $this->option_arr['o_currency']);
            }
            AppController::jsonResponse(compact('data', 'column', 'direction'));
        }
        exit();
    }


    public function Index()
    {
        $this->checkLogin();
        if (! $this->isInvoiceReady()) {
            $this->set('status', 2);
            return;
        }
        if (isset($_POST['invoice_post'])) {
            if (isset($_FILES['y_logo']) && ! empty($_FILES['y_logo']['tmp_name'])) {
                $Image = new ImageComponent();
                $Image->setAllowedExt(array(
                    'png',
                    'gif',
                    'jpg',
                    'jpeg',
                    'jpe',
                    'jfif',
                    'jif',
                    'jfi'
                ))->setAllowedTypes(array(
                    'image/png',
                    'image/gif',
                    'image/jpg',
                    'image/jpeg',
                    'image/pjpeg'
                ));
                if ($Image->load($_FILES['y_logo'])) {
                    $hash = md5(uniqid(rand(), true));
                    $original = 'app/web/invoices/' . $hash . '.' . $Image->getExtension();
                    $thumb = 'app/web/invoices/' . $hash . '_thumb.png';
                    if ($Image->save($original)) {
                        $Image->loadImage($original)
                            ->resizeSmart(120, 60)
                            ->saveImage($thumb);
                        $_POST['y_logo'] = $thumb;
                        @unlink($original);
                    }
                } else {
                    $time = time();
                    $_SESSION[$this->invoiceErrors][$time] = $Image->getError();
                    UtilComponent::redirect(BASE_URL . "index.php?controller=Invoice&action=Index&err=PIN03&errTime=" . $time);
                }
            }
            $data = array();
            $data['p_accept_payments'] = isset($_POST['p_accept_payments']) ? 1 : 0;
            $data['p_accept_paypal'] = isset($_POST['p_accept_paypal']) ? 1 : 0;
            $data['p_accept_authorize'] = isset($_POST['p_accept_authorize']) ? 1 : 0;
            $data['p_accept_creditcard'] = isset($_POST['p_accept_creditcard']) ? 1 : 0;
            $data['p_accept_cash'] = isset($_POST['p_accept_cash']) ? 1 : 0;
            $data['p_accept_bank'] = isset($_POST['p_accept_bank']) ? 1 : 0;
            $data['si_include'] = isset($_POST['si_include']) ? 1 : 0;
            $data['si_shipping_address'] = isset($_POST['si_shipping_address']) ? 1 : 0;
            $data['si_company'] = isset($_POST['si_company']) ? 1 : 0;
            $data['si_name'] = isset($_POST['si_name']) ? 1 : 0;
            $data['si_address'] = isset($_POST['si_address']) ? 1 : 0;
            $data['si_street_address'] = isset($_POST['si_street_address']) ? 1 : 0;
            $data['si_city'] = isset($_POST['si_city']) ? 1 : 0;
            $data['si_state'] = isset($_POST['si_state']) ? 1 : 0;
            $data['si_zip'] = isset($_POST['si_zip']) ? 1 : 0;
            $data['si_phone'] = isset($_POST['si_phone']) ? 1 : 0;
            $data['si_fax'] = isset($_POST['si_fax']) ? 1 : 0;
            $data['si_email'] = isset($_POST['si_email']) ? 1 : 0;
            $data['si_url'] = isset($_POST['si_url']) ? 1 : 0;
            $data['si_date'] = isset($_POST['si_date']) ? 1 : 0;
            $data['si_terms'] = isset($_POST['si_terms']) ? 1 : 0;
            $data['si_is_shipped'] = isset($_POST['si_is_shipped']) ? 1 : 0;
            $data['si_shipping'] = isset($_POST['si_shipping']) ? 1 : 0;
            $data['o_qty_is_int'] = isset($_POST['o_qty_is_int']) ? 1 : 0;
            $data['o_use_qty_unit_price'] = isset($_POST['o_use_qty_unit_price']) ? 1 : 0;
            InvoiceConfigModel::factory()->set('id', 1)->modify(array_merge($_POST, $data));
            UtilComponent::redirect(BASE_URL . "index.php?controller=Invoice&action=Index&err=PIN02&tab_id=" . $_POST['tab_id']);
        }
        $this->set('timezones', $this->sortTimezones(__('timezones', true)));
        $this->set('country_arr', CountryModel::factory()->select('t1.*, t2.content AS `name`')
            ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
            ->orderBy('`name` ASC')
            ->findAll()
            ->getData());
        $this->set('arr', InvoiceConfigModel::factory()->find(1)
            ->getData())
            ->appendJs('tinymce.min.js', THIRD_PARTY_PATH . 'tiny_mce_4.1.1/')
            ->appendJs('jquery.validate.min.js', $this->getConst('PLUGIN_LIBS_PATH'))
            ->appendJs('Invoice.js', $this->getConst('PLUGIN_JS_PATH'));
    }


    public function Invoices()
    {
        $this->checkLogin();
        if (! $this->isInvoiceReady()) {
            $this->set('status', 2);
            return;
        }
        $this->set('invoice_config_arr', InvoiceConfigModel::factory()->find(1)
            ->getData())
            ->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/')
            ->appendJs('Invoice.js', $this->getConst('PLUGIN_JS_PATH'))
            ->appendCss('plugin_invoice.css', $this->getConst('PLUGIN_CSS_PATH'))
            ->appendJs('index.php?controller=Admin&action=Messages', BASE_URL, true);
    }


    public function Payment()
    {
        $this->setLayout('Empty');
        $arr = InvoiceModel::factory()->where('t1.uuid', $_POST['uuid'])
            ->limit(1)
            ->findAll()
            ->getData();
        if (empty($arr)) {
            return;
        }
        $arr = $arr[0];
        $config_arr = InvoiceConfigModel::factory()->find(1)->getData();
        $data = array();
        if ($_POST['payment_method'] == 'creditcard') {
            $data['cc_type'] = $_POST['cc_type'];
            $data['cc_num'] = $_POST['cc_num'];
            $data['cc_code'] = $_POST['cc_code'];
            $data['cc_exp_month'] = $_POST['cc_exp_month'];
            $data['cc_exp_year'] = $_POST['cc_exp_year'];
        }
        $data['payment_method'] = $_POST['payment_method'];
        InvoiceModel::factory()->set('id', $arr['id'])->modify($data);
        switch ($_POST['payment_method']) {
            case 'paypal':
                $this->set('params', array(
                    'target' => '_self',
                    'name' => 'pinPaypal',
                    'id' => 'pinPaypal',
                    'business' => $config_arr['p_paypal_address'],
                    'item_name' => $arr['uuid'],
                    'custom' => $arr['uuid'],
                    'amount' => $arr['paid_deposit'],
                    'currency_code' => $arr['currency'],
                    'return' => BASE_URL . 'index.php?controller=Invoice&action=View&uuid=' . $arr['uuid'],
                    'notify_url' => BASE_URL . 'index.php?controller=Invoice&action=ConfirmPaypal&cid=' . $arr['foreign_id']
                ));
                break;
            case 'authorize':
                $this->set('params', array(
                    'name' => 'pinAuthorize',
                    'id' => 'pinAuthorize',
                    'timezone' => $config_arr['p_authorize_tz'],
                    'transkey' => $config_arr['p_authorize_key'],
                    'x_login' => $config_arr['p_authorize_mid'],
                    'x_description' => $arr['uuid'],
                    'x_amount' => $arr['paid_deposit'],
                    'x_invoice_num' => $arr['uuid'],
                    'x_receipt_link_url' => BASE_URL . 'index.php?controller=Invoice&action=View&uuid=' . $arr['uuid'],
                    'x_relay_url' => BASE_URL . 'index.php?controller=Invoice&action=ConfirmAuthorize&cid=' . $arr['foreign_id']
                ));
                break;
        }
        $this->set('config_arr', $config_arr)
            ->resetCss()
            ->resetJs()
            ->appendCss('invoice.css', $this->getConst('PLUGIN_CSS_PATH'));
    }


    public function Prints()
    {
        $this->View();
    }


    public function SaveItem()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged() && $this->isInvoiceReady()) {
            $InvoiceItemModel = InvoiceItemModel::factory();
            if (! in_array($_POST['column'], $InvoiceItemModel->getI18n())) {
                $InvoiceItemModel->where('id', $_GET['id'])
                    ->limit(1)
                    ->modifyAll(array(
                    $_POST['column'] => $_POST['value']
                ));
            } else {
                MultiLangModel::factory()->updateMultiLang(array(
                    $this->getLocaleId() => array(
                        $_POST['column'] => $_POST['value']
                    )
                ), $_GET['id'], 'InvoiceItem');
            }
        }
        exit();
    }


    public function Send()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged() && $this->isInvoiceReady()) {
            if (isset($_GET['uuid']) && ! empty($_GET['uuid'])) {
                $arr = InvoiceModel::factory()->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.y_country AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
                    ->join('MultiLang', "t3.model='Country' AND t3.foreign_id=t1.b_country AND t3.field='name' AND t3.locale='" . $this->getLocaleId() . "'", 'left outer')
                    ->join('MultiLang', "t4.model='Country' AND t4.foreign_id=t1.s_country AND t4.field='name' AND t4.locale='" . $this->getLocaleId() . "'", 'left outer')
                    ->select("t1.*, t2.content as y_country_title, t3.content as b_country_title, t3.content as s_country_title,  AES_DECRYPT(t1.cc_type, '" . SALT . "') AS cc_type,  AES_DECRYPT(t1.cc_num, '" . SALT . "') AS cc_num,  AES_DECRYPT(t1.cc_exp_month, '" . SALT . "') AS cc_exp_month,  AES_DECRYPT(t1.cc_exp_year, '" . SALT . "') AS cc_exp_year,  AES_DECRYPT(t1.cc_code, '" . SALT . "') AS cc_code")
                    ->where('t1.uuid', $_GET['id'])
                    ->where('t1.order_id', $_GET['uuid'])
                    ->limit(1)
                    ->findAll()
                    ->getData();
                $this->set('arr', ! empty($arr) ? $arr[0] : array());
                $this->set('config_arr', InvoiceConfigModel::factory()->find(1)
                    ->getData());
            }
            if (isset($_POST['uuid']) && ! empty($_POST['uuid'])) {
                $b_send = (isset($_POST['b_send']) && isset($_POST['b_email']) && ! empty($_POST['b_email']) && ValidationComponent::Email($_POST['b_email']));
                $s_send = (isset($_POST['s_send']) && isset($_POST['s_email']) && ! empty($_POST['s_email']) && ValidationComponent::Email($_POST['s_email']));
                if (! $b_send && ! $s_send) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 101,
                        'text' => 'Email(s) not selected.'
                    ));
                }
                $arr = InvoiceModel::factory()->where('t1.uuid', $_POST['id'])
                    ->where('t1.order_id', $_POST['uuid'])
                    ->limit(1)
                    ->findAll()
                    ->getData();
                if (empty($arr)) {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 102,
                        'text' => 'Invoice not found.'
                    ));
                }
                $arr = $arr[0];
                $arr['items'] = InvoiceItemModel::factory()->where('t1.invoice_id', $arr['id'])
                    ->findAll()
                    ->getData();
                $confi_arr = InvoiceConfigModel::factory()->find(1)->getData();
                $arr['y_logo'] = '<img src="' . BASE_URL . $confi_arr['y_logo'] . '" />';
                $arr['o_use_qty_unit_price'] = $confi_arr['o_use_qty_unit_price'];
                $view_url = BASE_URL . 'index.php?controller=Invoice&action=View&id=' . $_POST['id'] . '&uuid=' . $_POST['uuid'];
                $view_url = '<a href="' . $view_url . '">' . $view_url . '</a>';
                $Email = new EmailComponent();
                if ($this->option_arr['o_send_email'] == 'smtp') {
                    $Email->setTransport('smtp')
                        ->setSmtpHost($this->option_arr['o_smtp_host'])
                        ->setSmtpPort($this->option_arr['o_smtp_port'])
                        ->setSmtpUser($this->option_arr['o_smtp_user'])
                        ->setSmtpPass($this->option_arr['o_smtp_pass']);
                }
                if ($b_send && $s_send) {
                    $Email->setTo($_POST['b_email'])->setCc($_POST['s_email']);
                } elseif ($b_send && ! $s_send) {
                    $Email->setTo($_POST['b_email']);
                } elseif (! $b_send && $s_send) {
                    $Email->setTo($_POST['s_email']);
                }
                $message = '';
                if ($arr['status'] == 'not_paid') {
                    $message .= '<p>' . __('plugin_invoice_i_send_invoice_link', true) . '</p>';
                    $message .= $view_url . '<br/><br/><br/><br/>';
                }
                $message .= $this->Tokenizer($arr);
                $result = $Email->setContentType('text/html')
                    ->setFrom($arr['y_email'])
                    ->setReplyTo($arr['y_email'])
                    ->setSubject(__('plugin_invoice_send_subject', true))
                    ->send($message);
                if ($result) {
                    AppController::jsonResponse(array(
                        'status' => 'OK',
                        'code' => 200,
                        'text' => 'Email has been sent.'
                    ));
                } else {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 100,
                        'text' => 'Email has not been sent.'
                    ));
                }
            }
        }
    }


    private function Tokenizer($a)
    {
        $config = InvoiceConfigModel::factory()->find(1)->getData();
        $items = "";
        if (isset($a['items']) && is_array($a['items']) && ! empty($a['items'])) {
            $items .= '<table style="width: 100%; border-collapse: collapse">';
            $items .= '<tr>';
            $items .= '<td style="border-bottom: solid 1px #000; border-top: solid 1px #000">' . __('plugin_invoice_i_description', true) . '</td>';
            if ($a['o_use_qty_unit_price'] == 1) {
                $items .= '<td style="border-bottom: solid 1px #000; border-top: solid 1px #000; text-align: right">' . __('plugin_invoice_i_qty', true) . '</td>';
                $items .= '<td style="border-bottom: solid 1px #000; border-top: solid 1px #000; text-align: right">' . __('plugin_invoice_i_unit', true) . '</td>';
            }
            $items .= '<td style="border-bottom: solid 1px #000; border-top: solid 1px #000; text-align: right">' . __('plugin_invoice_i_amount', true) . '</td>';
            $items .= '</tr>';
            foreach ($a['items'] as $item) {
                $items .= '<tr>';
                $items .= sprintf('<td>%s<br>%s</td>', $item['name'], $item['description']);
                if ($a['o_use_qty_unit_price'] == 1) {
                    $items .= sprintf('<td style="text-align: right">%s</td>', number_format($item['qty'], (int) $config['o_qty_is_int'] === 0 ? 2 : 0));
                    $items .= sprintf('<td style="text-align: right">%s</td>', number_format($item['unit_price'], 2));
                }
                $items .= sprintf('<td style="text-align: right">%s</td>', number_format($item['amount'], 2));
                $items .= '</tr>';
            }
            $items .= '</table>';
        }
        $statuses = __('plugin_invoice_statuses', true);
        $_yesno = __('plugin_invoice_yesno', true);
        return str_replace(array(
            '{uuid}',
            '{order_id}',
            '{issue_date}',
            '{due_date}',
            '{created}',
            '{modified}',
            '{status}',
            '{subtotal}',
            '{discount}',
            '{tax}',
            '{shipping}',
            '{total}',
            '{paid_deposit}',
            '{amount_due}',
            '{currency}',
            '{notes}',
            '{y_logo}',
            '{y_company}',
            '{y_name}',
            '{y_street_address}',
            '{y_country}',
            '{y_city}',
            '{y_state}',
            '{y_zip}',
            '{y_phone}',
            '{y_fax}',
            '{y_email}',
            '{y_url}',
            '{b_billing_address}',
            '{b_company}',
            '{b_name}',
            '{b_address}',
            '{b_street_address}',
            '{b_country}',
            '{b_city}',
            '{b_state}',
            '{b_zip}',
            '{b_phone}',
            '{b_fax}',
            '{b_email}',
            '{b_url}',
            '{s_shipping_address}',
            '{s_company}',
            '{s_name}',
            '{s_address}',
            '{s_street_address}',
            '{s_country}',
            '{s_city}',
            '{s_state}',
            '{s_zip}',
            '{s_phone}',
            '{s_fax}',
            '{s_email}',
            '{s_url}',
            '{s_date}',
            '{s_terms}',
            '{s_is_shipped}',
            '{items}'
        ), array(
            $a['uuid'],
            $a['order_id'],
            UtilComponent::formatDate($a['issue_date'], 'Y-m-d', $this->option_arr['o_date_format']),
            UtilComponent::formatDate($a['due_date'], 'Y-m-d', $this->option_arr['o_date_format']),
            ! empty($a['created']) ? date($this->option_arr['o_date_format'] . " H:i:s", strtotime($a['created'])) : NULL,
            ! empty($a['modified']) ? date($this->option_arr['o_date_format'] . " H:i:s", strtotime($a['modified'])) : NULL,
            $statuses[$a['status']],
            number_format($a['subtotal'], 2),
            number_format($a['discount'], 2),
            number_format($a['tax'], 2),
            number_format($a['shipping'], 2),
            number_format($a['total'], 2),
            number_format($a['paid_deposit'], 2),
            number_format($a['amount_due'], 2),
            $a['currency'],
            $a['notes'],
            $a['y_logo'],
            $a['y_company'],
            $a['y_name'],
            $a['y_street_address'],
            $a['y_country_title'],
            $a['y_city'],
            $a['y_state'],
            $a['y_zip'],
            $a['y_phone'],
            $a['y_fax'],
            $a['y_email'],
            $a['y_url'],
            $a['b_billing_address'],
            $a['b_company'],
            $a['b_name'],
            $a['b_address'],
            $a['b_street_address'],
            $a['b_country_title'],
            $a['b_city'],
            $a['b_state'],
            $a['b_zip'],
            $a['b_phone'],
            $a['b_fax'],
            $a['b_email'],
            $a['b_url'],
            $a['s_shipping_address'],
            $a['s_company'],
            $a['s_name'],
            $a['s_address'],
            $a['s_street_address'],
            $a['s_country_title'],
            $a['s_city'],
            $a['s_state'],
            $a['s_zip'],
            $a['s_phone'],
            $a['s_fax'],
            $a['s_email'],
            $a['s_url'],
            UtilComponent::formatDate($a['s_date'], 'Y-m-d', $this->option_arr['o_date_format']),
            $a['s_terms'],
            $_yesno[$a['s_is_shipped']],
            $items
        ), $config['y_template']);
    }


    public function SaveInvoice()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $value = $_POST['value'];
            InvoiceModel::factory()->where('id', $_GET['id'])
                ->limit(1)
                ->modifyAll(array(
                $_POST['column'] => $value
            ));
        }
        exit();
    }


    public function Update()
    {
        $this->checkLogin();
        if (! $this->isInvoiceReady()) {
            $this->set('status', 2);
            return;
        }
        $InvoiceModel = InvoiceModel::factory();
        if (isset($_POST['invoice_update'])) {
            $arr = $InvoiceModel->find($_POST['id'])->getData();
            if (empty($arr) || $arr['foreign_id'] != $this->getForeignId()) {
                UtilComponent::redirect(BASE_URL . "index.php?controller=Invoice&action=Invoices&err=PIN04");
            }
            $data = array();
            $data['foreign_id'] = $arr['foreign_id'];
            $data['modified'] = ':NOW()';
            $data['issue_date'] = ! empty($_POST['issue_date']) ? UtilComponent::formatDate($_POST['issue_date'], $this->option_arr['o_date_format']) : NULL;
            $data['due_date'] = ! empty($_POST['due_date']) ? UtilComponent::formatDate($_POST['due_date'], $this->option_arr['o_date_format']) : NULL;
            $data['s_date'] = ! empty($_POST['s_date']) ? UtilComponent::formatDate($_POST['s_date'], $this->option_arr['o_date_format']) : NULL;
            $data = array_merge($_POST, $data);
            if (! $InvoiceModel->validates($data)) {
                UtilComponent::redirect(BASE_URL . "index.php?controller=Invoice&action=Invoices&err=PIN06");
            }
            $InvoiceModel->set('id', $_POST['id'])->modify($data);
            UtilComponent::redirect(BASE_URL . "index.php?controller=Invoice&action=Update&id=" . $_POST['id'] . "&err=PIN05");
        }
        $arr = $InvoiceModel->find($_GET['id'])->getData();
        if (empty($arr) || $arr['foreign_id'] != $this->getForeignId()) {
            UtilComponent::redirect(BASE_URL . "index.php?controller=Invoice&action=Invoices&err=PIN04");
        }
        $this->set('country_arr', CountryModel::factory()->select('t1.*, t2.content AS `name`')
            ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
            ->orderBy('`name` ASC')
            ->findAll()
            ->getData());
        $this->set('arr', $arr)
            ->set('config_arr', InvoiceConfigModel::factory()->find(1)
            ->getData())
            ->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/')
            ->appendJs('jquery.validate.min.js', $this->getConst('PLUGIN_LIBS_PATH'))
            ->appendJs('Invoice.js', $this->getConst('PLUGIN_JS_PATH'))
            ->appendJs('index.php?controller=Admin&action=Messages', BASE_URL, true);
    }


    public function View()
    {
        $this->setLayout('Empty');
        $arr = InvoiceModel::factory()->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.y_country AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
            ->join('MultiLang', "t3.model='Country' AND t3.foreign_id=t1.b_country AND t3.field='name' AND t3.locale='" . $this->getLocaleId() . "'", 'left outer')
            ->join('MultiLang', "t4.model='Country' AND t4.foreign_id=t1.s_country AND t4.field='name' AND t4.locale='" . $this->getLocaleId() . "'", 'left outer')
            ->select("t1.*, t2.content as y_country_title, t3.content as b_country_title, t3.content as s_country_title,  AES_DECRYPT(t1.cc_type, '" . SALT . "') AS cc_type,  AES_DECRYPT(t1.cc_num, '" . SALT . "') AS cc_num,  AES_DECRYPT(t1.cc_exp_month, '" . SALT . "') AS cc_exp_month,  AES_DECRYPT(t1.cc_exp_year, '" . SALT . "') AS cc_exp_year,  AES_DECRYPT(t1.cc_code, '" . SALT . "') AS cc_code")
            ->where('t1.uuid', @$_GET['id'])
            ->where('t1.order_id', @$_GET['uuid'])
            ->limit(1)
            ->findAll()
            ->getData();
        if (empty($arr)) {
            UtilComponent::redirect(BASE_URL . "index.php?controller=Invoice&action=Invoices&err=PIN04");
        }
        $arr = $arr[0];
        $arr['items'] = InvoiceItemModel::factory()->where('t1.invoice_id', $arr['id'])
            ->findAll()
            ->getData();
        $confi_arr = InvoiceConfigModel::factory()->find(1)->getData();
        $arr['y_logo'] = '<img src="' . BASE_URL . $confi_arr['y_logo'] . '" />';
        $arr['o_use_qty_unit_price'] = $confi_arr['o_use_qty_unit_price'];
        $this->set('arr', $arr)
            ->set('config_arr', $confi_arr)
            ->set('template', $this->Tokenizer($arr))
            ->resetCss()
            ->resetJs()
            ->appendJs('jquery-1.8.3.min.js', $this->getConst('PLUGIN_LIBS_PATH'))
            ->appendJs('jquery.validate.min.js', $this->getConst('PLUGIN_LIBS_PATH'))
            ->appendJs('Invoice.js', $this->getConst('PLUGIN_JS_PATH'))
            ->appendCss('invoice.css', $this->getConst('PLUGIN_CSS_PATH'));
    }
}
?>