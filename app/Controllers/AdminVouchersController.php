<?php
namespace App\Controllers;
use App\Models\VoucherModel;
use App\Controllers\Components\UtilComponent;
use App\Models\VoucherProductModel;
use App\Models\ProductModel;
use App\Models\MultiLangModel;

class AdminVouchersController extends AdminController
{
    public function CheckCode()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (! isset($_GET['code']) || empty($_GET['code'])) {
                echo 'false';
                exit();
            }
            $VoucherModel = VoucherModel::factory()->where('t1.code', $_GET['code']);
            if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
                $VoucherModel->where('t1.id !=', $_GET['id']);
            }
            echo $VoucherModel->findCount()->getData() == 0 ? 'true' : 'false';
        }
        exit();
    }
    
    public function Create()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            if (isset($_POST['voucher_create'])) {
                $data = array();
                $data['code'] = $_POST['code'];
                $data['discount'] = $_POST['discount'];
                $data['type'] = $_POST['type'];
                $data['valid'] = $_POST['valid'];
                switch ($_POST['valid']) {
                    case 'fixed':
                        $data['date_from'] = UtilComponent::formatDate($_POST['f_date'], $this->option_arr['o_date_format']);
                        $data['date_to'] = $data['date_from'];
                        $data['time_from'] = $_POST['f_hour_from'] . ":" . $_POST['f_minute_from'] . ":00";
                        $data['time_to'] = $_POST['f_hour_to'] . ":" . $_POST['f_minute_to'] . ":00";
                        break;
                    case 'period':
                        $data['date_from'] = UtilComponent::formatDate($_POST['p_date_from'], $this->option_arr['o_date_format']);
                        $data['date_to'] = UtilComponent::formatDate($_POST['p_date_to'], $this->option_arr['o_date_format']);
                        $data['time_from'] = $_POST['p_hour_from'] . ":" . $_POST['p_minute_from'] . ":00";
                        $data['time_to'] = $_POST['p_hour_to'] . ":" . $_POST['p_minute_to'] . ":00";
                        break;
                    case 'recurring':
                        $data['every'] = $_POST['r_every'];
                        $data['time_from'] = $_POST['r_hour_from'] . ":" . $_POST['r_minute_from'] . ":00";
                        $data['time_to'] = $_POST['r_hour_to'] . ":" . $_POST['r_minute_to'] . ":00";
                        break;
                }
                $id = VoucherModel::factory()->setAttributes($data)
                    ->insert()
                    ->getInsertId();
                if ($id !== false && (int) $id > 0) {
                    if (isset($_POST['product_id']) && count($_POST['product_id']) > 0) {
                        $VoucherProductModel = VoucherProductModel::factory();
                        $VoucherProductModel->begin();
                        foreach ($_POST['product_id'] as $product_id) {
                            $VoucherProductModel->reset()
                                ->setAttributes(array(
                                'voucher_id' => $id,
                                'product_id' => $product_id
                            ))
                                ->insert();
                        }
                        $VoucherProductModel->commit();
                    }
                    $err = 'AV01';
                } else {
                    $err = 'AV02';
                }
                UtilComponent::redirect(sprintf("%s?controller=AdminVouchers&action=Index&err=%s", $_SERVER['PHP_SELF'], $err));
            } else {
                $ProductModel = ProductModel::factory()->select('t1.*, t2.content AS name')->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer');
                $product_arr = $ProductModel->orderBy('`name` ASC')
                    ->findAll()
                    ->getData();
                $this->set('product_arr', $product_arr);
                $this->appendCss('chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
                $this->appendJs('chosen.jquery.min.js', THIRD_PARTY_PATH . 'harvest/chosen/');
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('AdminVouchers.js');
            }
        } else {
            $this->set('status', 2);
        }
    }

    public function DeleteVoucher()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
                if (VoucherModel::factory()->set('id', $_GET['id'])
                    ->erase()
                    ->getAffectedRows() == 1) {
                    VoucherProductModel::factory()->where('voucher_id', $_GET['id'])->eraseAll();
                    AppController::jsonResponse(array(
                        'status' => 'OK',
                        'code' => 200,
                        'text' => 'Voucher has been deleted.'
                    ));
                } else {
                    AppController::jsonResponse(array(
                        'status' => 'ERR',
                        'code' => 100,
                        'text' => 'Voucher has not been deleted.'
                    ));
                }
            }
            AppController::jsonResponse(array(
                'status' => 'ERR',
                'code' => 101,
                'text' => 'Missing or empty params.'
            ));
        }
        exit();
    }

    public function DeleteVoucherBulk()
    {
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['record']) && ! empty($_POST['record'])) {
                VoucherModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
                VoucherProductModel::factory()->whereIn('voucher_id', $_POST['record'])->eraseAll();
                AppController::jsonResponse(array(
                    'status' => 'OK',
                    'code' => 200,
                    'text' => 'Voucher(s) has been deleted.'
                ));
            }
            AppController::jsonResponse(array(
                'status' => 'ERR',
                'code' => 101,
                'text' => 'Missing or empty params.'
            ));
        }
        exit();
    }

    public function GetVoucher()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $VoucherModel = VoucherModel::factory();
            if (isset($_GET['q']) && ! empty($_GET['q'])) {
                $q = trim($_GET['q']);
                $q = str_replace(array(
                    '%',
                    '_'
                ), array(
                    '\%',
                    '\_'
                ), $q);
                $VoucherModel->where('t1.code LIKE', "%$q%");
            }
            if (isset($_GET['valid']) && ! empty($_GET['valid'])) {
                $VoucherModel->where('t1.valid', $_GET['valid']);
            }
            $column = 'code';
            $direction = 'DESC';
            if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                'ASC',
                'DESC'
            ))) {
                $column = $_GET['column'];
                $direction = strtoupper($_GET['direction']);
            }
            $total = $VoucherModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $data = $VoucherModel->orderBy("$column $direction")
                ->limit($rowCount, $offset)
                ->findAll()
                ->getData();
            $daynames = __('daynames', true);
            foreach ($data as $k => $v) {
                $data[$k]['discount_f'] = $v['type'] == 'amount' ? UtilComponent::formatCurrencySign(number_format($v['discount'], 2), $this->option_arr['o_currency']) : $v['discount'] . '%';
                switch ($v['valid']) {
                    case 'fixed':
                        $data[$k]['valid_f'] = sprintf('%s, %s - %s', UtilComponent::formatDate($v['date_from'], 'Y-m-d', $this->option_arr['o_date_format']), substr($v['time_from'], 0, 5), substr($v['time_to'], 0, 5));
                        break;
                    case 'period':
                        $data[$k]['valid_f'] = sprintf('%s, %s &divide; %s, %s', UtilComponent::formatDate($v['date_from'], 'Y-m-d', $this->option_arr['o_date_format']), substr($v['time_from'], 0, 5), UtilComponent::formatDate($v['date_to'], 'Y-m-d', $this->option_arr['o_date_format']), substr($v['time_to'], 0, 5));
                        break;
                    case 'recurring':
                        $data[$k]['valid_f'] = sprintf('%s, %s - %s', @$daynames[$v['every']], substr($v['time_from'], 0, 5), substr($v['time_to'], 0, 5));
                        break;
                }
            }
            AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
        }
        exit();
    }

    public function GetProducts()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $ProductModel = ProductModel::factory()->select('t1.*, t2.content AS name')->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer');
            if (isset($_GET['term'])) {
                $q = $ProductModel->escapeStr(trim($_GET['term']));
                $q = str_replace(array(
                    '%',
                    '_'
                ), array(
                    '\%',
                    '\_'
                ), $q);
                $ProductModel->having(sprintf("name LIKE '%%%1\$s%%' OR t1.id LIKE '%%%1\$s%%' OR t1.sku LIKE '%%%1\$s%%'", $q), false);
            }
            if (isset($_GET['voucher_id']) && (int) $_GET['voucher_id'] > 0) {
                $ProductModel->where(sprintf("t1.id NOT IN (SELECT `product_id` FROM `%s` WHERE `voucher_id` = '%u')", VoucherProductModel::factory()->getTable(), (int) $_GET['voucher_id']));
            }
            $arr = $ProductModel->orderBy('`name` ASC')
                ->findAll()
                ->getData();
            $_arr = array();
            foreach ($arr as $v) {
                $_arr[] = array(
                    'label' => $v['name'],
                    'value' => $v['id']
                );
            }
            AppController::jsonResponse($_arr);
        }
        exit();
    }

    public function Index()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
            $this->appendJs('AdminVouchers.js');
            $this->appendJs('index.php?controller=Admin&action=Messages', BASE_URL, true);
        } else {
            $this->set('status', 2);
        }
    }

    public function SaveVoucher()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_GET['id']) && (int) $_GET['id'] > 0 && isset($_POST['column']) && isset($_POST['value']) && ! empty($_POST['column'])) {
                $VoucherModel = VoucherModel::factory();
                if (! in_array($_POST['column'], $VoucherModel->getI18n())) {
                    $VoucherModel->set('id', $_GET['id'])->modify(array(
                        $_POST['column'] => $_POST['value']
                    ));
                } else {
                    MultiLangModel::factory()->updateMultiLang(array(
                        $this->getLocaleId() => array(
                            $_POST['column'] => $_POST['value']
                        )
                    ), $_GET['id'], 'Voucher');
                }
                AppController::jsonResponse(array(
                    'status' => 'OK',
                    'code' => 200,
                    'text' => 'Voucher has been saved.'
                ));
            }
            AppController::jsonResponse(array(
                'status' => 'ERR',
                'code' => 100,
                'text' => 'Missing or empty params.'
            ));
        }
        exit();
    }

    public function UnlinkProduct()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['voucher_id']) && isset($_POST['product_id']) && (int) $_POST['voucher_id'] > 0 && (int) $_POST['product_id'] > 0) {
                if (VoucherProductModel::factory()->where('voucher_id', $_POST['voucher_id'])
                    ->where('product_id', $_POST['product_id'])
                    ->limit(1)
                    ->eraseAll()
                    ->getAffectedRows() == 1) {
                    AppController::jsonResponse(array(
                        'status' => 'OK',
                        'code' => 200,
                        'text' => 'Voucher has been unlinked with given product.'
                    ));
                }
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 100,
                    'text' => 'Voucher has not been unlinked with given product.'
                ));
            }
            AppController::jsonResponse(array(
                'status' => 'ERR',
                'code' => 101,
                'text' => 'Missing parameters'
            ));
        }
    }

    public function Update()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            if (isset($_POST['voucher_update'])) {
                $data = array();
                $data['id'] = $_POST['id'];
                $data['code'] = $_POST['code'];
                $data['discount'] = $_POST['discount'];
                $data['type'] = $_POST['type'];
                $data['valid'] = $_POST['valid'];
                switch ($_POST['valid']) {
                    case 'fixed':
                        $data['date_from'] = UtilComponent::formatDate($_POST['f_date'], $this->option_arr['o_date_format']);
                        $data['date_to'] = $data['date_from'];
                        $data['time_from'] = $_POST['f_hour_from'] . ":" . $_POST['f_minute_from'] . ":00";
                        $data['time_to'] = $_POST['f_hour_to'] . ":" . $_POST['f_minute_to'] . ":00";
                        $data['every'] = array(
                            'NULL'
                        );
                        break;
                    case 'period':
                        $data['date_from'] = UtilComponent::formatDate($_POST['p_date_from'], $this->option_arr['o_date_format']);
                        $data['date_to'] = UtilComponent::formatDate($_POST['p_date_to'], $this->option_arr['o_date_format']);
                        $data['time_from'] = $_POST['p_hour_from'] . ":" . $_POST['p_minute_from'] . ":00";
                        $data['time_to'] = $_POST['p_hour_to'] . ":" . $_POST['p_minute_to'] . ":00";
                        $data['every'] = array(
                            'NULL'
                        );
                        break;
                    case 'recurring':
                        $data['date_from'] = array(
                            'NULL'
                        );
                        $data['date_to'] = array(
                            'NULL'
                        );
                        $data['every'] = $_POST['r_every'];
                        $data['time_from'] = $_POST['r_hour_from'] . ":" . $_POST['r_minute_from'] . ":00";
                        $data['time_to'] = $_POST['r_hour_to'] . ":" . $_POST['r_minute_to'] . ":00";
                        break;
                }
                $VoucherProductModel = VoucherProductModel::factory();
                $VoucherProductModel->where('voucher_id', $_POST['id'])->eraseAll();
                if (isset($_POST['product_id']) && count($_POST['product_id']) > 0) {
                    $VoucherProductModel->begin();
                    foreach ($_POST['product_id'] as $product_id) {
                        $VoucherProductModel->reset()
                            ->setAttributes(array(
                            'voucher_id' => $_POST['id'],
                            'product_id' => $product_id
                        ))
                            ->insert();
                    }
                    $VoucherProductModel->commit();
                }
                if (VoucherModel::factory()->set('id', $data['id'])
                    ->modify($data)
                    ->getAffectedRows() == 1) {
                    $err = 'AV05';
                } else {
                    $err = 'AV06';
                }
                UtilComponent::redirect(sprintf("%s?controller=AdminVouchers&action=Index&err=%s", $_SERVER['PHP_SELF'], $err));
            } else {
                $arr = VoucherModel::factory()->find($_GET['id'])->getData();
                if (count($arr) === 0) {
                    UtilComponent::redirect(sprintf("%s?controller=AdminVouchers&action=Index&err=%s", $_SERVER['PHP_SELF'], 'AV08'));
                }
                $this->set('arr', $arr);
                $this->set('vp_arr', VoucherProductModel::factory()->select('t1.*, t2.content AS name')
                    ->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.product_id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
                    ->where('t1.voucher_id', $arr['id'])
                    ->orderBy('t1.product_id ASC')
                    ->findAll()
                    ->getDataPair('product_id', 'product_id'));
                $ProductModel = ProductModel::factory()->select('t1.*, t2.content AS name')->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer');
                $product_arr = $ProductModel->orderBy('`name` ASC')
                    ->findAll()
                    ->getData();
                $this->set('product_arr', $product_arr);
                $this->appendCss('chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
                $this->appendJs('chosen.jquery.min.js', THIRD_PARTY_PATH . 'harvest/chosen/');
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('AdminVouchers.js');
            }
        } else {
            $this->set('status', 2);
        }
    }
}
?>