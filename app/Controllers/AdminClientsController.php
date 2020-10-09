<?php
namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\AddressModel;
use App\Controllers\Components\UtilComponent;
use App\Plugins\Country\Models\CountryModel;
use Core\Framework\Components\CSVComponent;
use App\Models\OrderModel;
use App\Models\MultiLangModel;

class AdminClientsController extends AdminController
{
    public function CheckEmail()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_GET['email']) && ! empty($_GET['email'])) {
                $ClientModel = ClientModel::factory()->where('t1.email', $_GET['email']);
                if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
                    $ClientModel->where('t1.id !=', $_GET['id']);
                }
                echo 0 == $ClientModel->findCount()->getData() ? 'true' : 'false';
            } else {
                echo 'false';
            }
        }
        exit();
    }

    public function Create()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            if (isset($_POST['create_form'])) {
                $ClientModel = ClientModel::factory();
                if (0 == $ClientModel->where('t1.email', $_POST['email'])
                    ->findCount()
                    ->getData()) {
                    $data = array();
                    $client_id = $ClientModel->reset()
                        ->setAttributes(array_merge($_POST, $data))
                        ->insert()
                        ->getInsertId();
                    if ($client_id !== false && (int) $client_id > 0) {
                        if (isset($_POST['name'])) {
                            $AddressModel = AddressModel::factory();
                            $AddressModel->begin();
                            foreach ($_POST['name'] as $k => $v) {
                                if (! empty($v)) {
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
                                        'is_default_shipping' => ($_POST['is_default_shipping'] == $k ? 1 : 0),
                                        'is_default_billing' => ($_POST['is_default_billing'] == $k ? 1 : 0)
                                    ))
                                        ->insert();
                                }
                            }
                            $AddressModel->commit();
                        }
                        $err = 'AC01';
                    } else {
                        $err = 'AC02';
                    }
                } else {
                    $err = 'AC07';
                }
                UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminClients&action=Index&err=" . $err);
            } else {
                $this->set('country_arr', CountryModel::factory()->select('t1.*, t2.content AS name')
                    ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
                    ->orderBy('`name` ASC')
                    ->findAll()
                    ->getData());
                $this->appendCss('chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/')
                    ->appendJs('chosen.jquery.min.js', THIRD_PARTY_PATH . 'harvest/chosen/')
                    ->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/')
                    ->appendJs('AdminClients.js');
            }
        } else {
            $this->set('status', 2);
        }
    }

    public function DeleteClient()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $response = array();
            if (ClientModel::factory()->set('id', $_GET['id'])
                ->erase()
                ->getAffectedRows() == 1) {
                AddressModel::factory()->where('client_id', $_GET['id'])->eraseAll();
                $response['code'] = 200;
            } else {
                $response['code'] = 100;
            }
            AppController::jsonResponse($response);
        }
        exit();
    }

    public function DeleteClientBulk()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_POST['record']) && count($_POST['record']) > 0) {
                ClientModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
                AddressModel::factory()->whereIn('client_id', $_POST['record'])->eraseAll();
            }
        }
        exit();
    }


    public function DeleteAddress()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            if (AddressModel::factory()->set('id', $_POST['id'])
                ->erase()
                ->getAffectedRows() == 1) {
                $resp = array(
                    'code' => 200
                );
            } else {
                $resp = array(
                    'code' => 100
                );
            }
            AppController::jsonResponse($resp);
        } else {
            $this->set('status', 2);
        }
    }

    public function ExportClient()
    {
        $this->checkLogin();
        if (isset($_POST['record']) && is_array($_POST['record'])) {
            $arr = ClientModel::factory()->whereIn('id', $_POST['record'])
                ->findAll()
                ->getData();
            $csv = new CSVComponent();
            $csv->setHeader(true)
                ->setName("Clients-" . time() . ".csv")
                ->process($arr)
                ->download();
        }
        exit();
    }

    public function GetAddresses()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $this->set('address_arr', AddressModel::factory()->where('t1.client_id', $_GET['id'])
                ->orderBy('FIELD(`is_default_shipping`, 1, 0), FIELD(`is_default_billing`, 1, 0), t1.id ASC')
                ->findAll()
                ->getData());
            $this->set('country_arr', CountryModel::factory()->select('t1.*, t2.content AS name')
                ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
                ->orderBy('`name` ASC')
                ->findAll()
                ->getData());
        }
    }

    public function GetClient()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $ClientModel = ClientModel::factory();
            if (isset($_GET['q']) && ! empty($_GET['q'])) {
                $q = trim($_GET['q']);
                $q = str_replace(array(
                    '%',
                    '_'
                ), array(
                    '\%',
                    '\_'
                ), $q);
                $ClientModel->where('t1.email LIKE', "%$q%");
                $ClientModel->orWhere('t1.client_name LIKE', "%$q%");
                $ClientModel->orWhere('t1.phone LIKE', "%$q%");
                $ClientModel->orWhere('t1.client_name LIKE', "%$q%");
                $ClientModel->orWhere(sprintf("t1.id IN (SELECT `client_id` FROM `%2\$s` WHERE `name` LIKE '%%%1\$s%%')", $ClientModel->escapeStr($q), AddressModel::factory()->getTable()));
            }
            if (isset($_GET['client_ids']) && ! empty($_GET['client_ids'])) {
                $ClientModel->where("t1.id IN(" . $_GET['client_ids'] . ")");
            }
            $column = 'client_name';
            $direction = 'ASC';
            if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                'ASC',
                'DESC'
            ))) {
                $column = $_GET['column'];
                $direction = strtoupper($_GET['direction']);
            }
            $total = $ClientModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $data = $ClientModel->select(sprintf("t1.id, t1.client_name, t1.email, t1.status,  (SELECT COUNT(*) FROM `%1\$s` WHERE `client_id` = `t1`.`id` LIMIT 1) AS `orders`,  (SELECT `created` FROM `%1\$s` WHERE `client_id` = `t1`.`id` ORDER BY `created` DESC LIMIT 1) AS `last_order`", OrderModel::factory()->getTable()))
                ->orderBy("$column $direction")
                ->limit($rowCount, $offset)
                ->findAll()
                ->getData();
            AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
        }
        exit();
    }

    public function Index()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
            $this->appendJs('AdminClients.js');
            $this->appendJs('index.php?controller=Admin&action=Messages', BASE_URL, true);
        } else {
            $this->set('status', 2);
        }
    }


    public function SaveClient()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $ClientModel = ClientModel::factory();
            if (! in_array($_POST['column'], @$ClientModel->getI18n())) {
                $ClientModel->where('id', $_GET['id'])
                    ->limit(1)
                    ->modifyAll(array(
                    $_POST['column'] => $_POST['value']
                ));
            } else {
                MultiLangModel::factory()->updateMultiLang(array(
                    $this->getLocaleId() => array(
                        $_POST['column'] => $_POST['value']
                    )
                ), $_GET['id'], 'Client');
            }
        }
        exit();
    }


    public function Update()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            if (isset($_POST['update_form'])) {
                if (ClientModel::factory()->set('id', $_POST['id'])
                    ->modify($_POST)
                    ->getAffectedRows() == 1) {
                    $err = 'AC05';
                } else {
                    $err = 'AC06';
                }
                if (isset($_POST['name'])) {
                    $AddressModel = AddressModel::factory();
                    $AddressModel->begin();
                    foreach ($_POST['name'] as $k => $v) {
                        if (empty($v)) {
                            continue;
                        }
                        if (strpos($k, 'new_') === 0) {
                            $AddressModel->reset()
                                ->setAttributes(array(
                                'client_id' => $_POST['id'],
                                'country_id' => $_POST['country_id'][$k],
                                'state' => $_POST['state'][$k],
                                'city' => $_POST['city'][$k],
                                'zip' => $_POST['zip'][$k],
                                'address_1' => $_POST['address_1'][$k],
                                'address_2' => $_POST['address_2'][$k],
                                'name' => $_POST['name'][$k],
                                'is_default_shipping' => ($_POST['is_default_shipping'] == $k ? 1 : 0),
                                'is_default_billing' => ($_POST['is_default_billing'] == $k ? 1 : 0)
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
                                'is_default_shipping' => ($_POST['is_default_shipping'] == $k ? 1 : 0),
                                'is_default_billing' => ($_POST['is_default_billing'] == $k ? 1 : 0)
                            ));
                        }
                    }
                    $AddressModel->commit();
                }
                UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminClients&action=Index&err=" . $err);
            } else {
                $arr = ClientModel::factory()->select(sprintf("t1.*, AES_DECRYPT(`password`, '%s') AS `password`, (SELECT COUNT(*) FROM `%s` WHERE `client_id` = `t1`.`id` LIMIT 1) AS `orders`", SALT, OrderModel::factory()->getTable()))
                    ->find($_GET['id'])
                    ->getData();
                if (count($arr) === 0) {
                    UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminClients&action=Index&err=AC08");
                }
                $this->set('address_arr', AddressModel::factory()->where('t1.client_id', $arr['id'])
                    ->orderBy('FIELD(`is_default_shipping`,1,0), FIELD(`is_default_billing`,1,0), t1.id ASC')
                    ->findAll()
                    ->getData());
                $this->set('arr', $arr);
                $this->set('country_arr', CountryModel::factory()->select('t1.*, t2.content AS name')
                    ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
                    ->orderBy('`name` ASC')
                    ->findAll()
                    ->getData());
                $this->appendCss('chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/')
                    ->appendJs('chosen.jquery.min.js', THIRD_PARTY_PATH . 'harvest/chosen/')
                    ->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/')
                    ->appendJs('AdminClients.js');
            }
        } else {
            $this->set('status', 2);
        }
    }
}
?>