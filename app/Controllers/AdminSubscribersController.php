<?php
namespace App\Controllers;

use App\Models\SubscriberModel;
use App\Controllers\Components\UtilComponent;
use App\Models\GroupSubscriberModel;
use App\Models\GroupModel;
use App\Plugins\Country\Models\CountryModel;
use Core\Framework\Components\CSVComponent;
use App\Models\QueueModel;
use App\Models\MessageModel;
use App\Models\FileModel;

class AdminSubscribersController extends AdminController
{

    public function CheckEmail()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (! isset($_GET['email']) || empty($_GET['email'])) {
                echo 'false';
                exit();
            }
            $SubscriberModel = SubscriberModel::factory()->where('t1.email', $_GET['email']);
            if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
                $SubscriberModel->where('t1.id !=', $_GET['id']);
            }
            echo $SubscriberModel->findCount()->getData() == 0 ? 'true' : 'false';
        }
        exit();
    }
    
    public function Create()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            if (isset($_POST['subscriber_create'])) {
                $SubscriberModel = SubscriberModel::factory();
                $data = array();
                if (isset($_POST['birthday']) && ! empty($_POST['birthday'])) {
                    $data['birthday'] = UtilComponent::formatDate($_POST['birthday'], $this->option_arr['o_date_format']);
                    unset($_POST['birthday']);
                }
                $data['ip'] = UtilComponent::getClientIp();
                $data = array_merge($_POST, $data);
                $id = $SubscriberModel->setAttributes($data)
                    ->insert()
                    ->getInsertId();
                if ($id !== false && (int) $id > 0) {
                    if (isset($_POST['group_id'])) {
                        $GroupSubscriberModel = GroupSubscriberModel::factory();
                        $GroupSubscriberModel->begin();
                        foreach ($_POST['group_id'] as $group_id) {
                            $data = array();
                            $data['group_id'] = $group_id;
                            $data['subscriber_id'] = $id;
                            $GroupSubscriberModel->reset()
                                ->setAttributes($data)
                                ->insert();
                        }
                        $GroupSubscriberModel->commit();
                    }
                    $err = 'AS03';
                } else {
                    $err = 'AS04';
                }
                UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminSubscribers&action=Index&err=$err");
            } else {
                $group_arr = GroupModel::factory()->where('status', 'T')
                    ->orderBy('group_title ASC')
                    ->findAll()
                    ->getData();
                $country_arr = CountryModel::factory()->select('t1.id, t2.content AS country_title')
                    ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
                    ->orderBy('`country_title` ASC')
                    ->findAll()
                    ->getData();
                $this->set('group_arr', $group_arr);
                $this->set('country_arr', $country_arr);
                $this->appendJs('chosen.jquery.min.js', THIRD_PARTY_PATH . 'chosen/');
                $this->appendCss('chosen.css', THIRD_PARTY_PATH . 'chosen/');
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('additional-methods.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('AdminSubscribers.js');
            }
        } else {
            $this->set('status', 2);
        }
    }

    public function Imports()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            if (isset($_POST['subscriber_import'])) {
                $map = array(
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'website',
                    'gender',
                    'age',
                    'birthday',
                    'address',
                    'city',
                    'state',
                    'country_id',
                    'zip',
                    'company_name',
                    'subscribed'
                );
                switch ($_POST['source']) {
                    case 'excel':
                        $subscribers = $_POST['subscribers'];
                        $rows = explode("\n", $subscribers);
                        if (count($rows) > 0) {
                            $SubscriberModel = SubscriberModel::factory();
                            $GroupSubscriberModel = GroupSubscriberModel::factory();
                            $valid = false;
                            foreach ($rows as $key => $row) {
                                if (! empty($row) && $key == 0) {
                                    $fields = explode("\t", $row);
                                    if (count($fields) != 15) {
                                        break;
                                    } else {
                                        $same = true;
                                        foreach ($fields as $k => $field) {
                                            if (trim($field) != $map[$k]) {
                                                $same = false;
                                                break;
                                            }
                                        }
                                        if ($same == false) {
                                            break;
                                        }
                                    }
                                }
                                if (! empty($row) && $key > 0) {
                                    $data = array();
                                    $fields = explode("\t", $row);
                                    $country_data = array();
                                    $country_arr = CountryModel::factory()->select('t1.*')
                                        ->findAll()
                                        ->getData();
                                    foreach ($country_arr as $country) {
                                        $country_data[$country['alpha_2']] = $country['id'];
                                    }
                                    foreach ($fields as $k => $field) {
                                        if ($map[$k] == 'country_id') {
                                            if (! empty($country_data)) {
                                                $data['country_id'] = $country_data[$field];
                                            } else {
                                                $data['country_id'] = ':NULL';
                                            }
                                        } else {
                                            $data[$map[$k]] = str_replace("\r", "", $field);
                                        }
                                    }
                                    if ($SubscriberModel->reset()
                                        ->where('t1.email', $data['email'])
                                        ->findCount()
                                        ->getData() == 0) {
                                        $id = $SubscriberModel->reset()
                                            ->debug(1)
                                            ->setAttributes($data)
                                            ->insert()
                                            ->getInsertId();
                                        if ($id !== false && (int) $id > 0) {
                                            if (isset($_POST['group_id']) && ! empty($_POST['group_id'])) {
                                                $gs_data = array();
                                                $gs_data['subscriber_id'] = $id;
                                                $gs_data['group_id'] = $_POST['group_id'];
                                                $GroupSubscriberModel->reset()
                                                    ->setAttributes($gs_data)
                                                    ->insert();
                                            }
                                        }
                                    } else {
                                        if (isset($_POST['update_subscribers'])) {
                                            $SubscriberModel->reset()->where('t1.email', $data['email']);
                                            if (isset($_POST['group_id']) && ! empty($_POST['group_id'])) {
                                                $SubscriberModel->where("t1.id IN(SELECT TGS.subscriber_id FROM `" . $GroupSubscriberModel->getTable() . "` AS TGS WHERE TGS.group_id = " . $_POST['group_id'] . ")");
                                            }
                                            $subscriber_arr = $SubscriberModel->limit(1)
                                                ->findAll()
                                                ->getData();
                                            if (count($subscriber_arr) > 0) {
                                                $id = $subscriber_arr[0]['id'];
                                                $data['modified'] = date('Y-m-d H:i:s');
                                                $SubscriberModel->reset()
                                                    ->where('id', $id)
                                                    ->limit(1)
                                                    ->modifyAll($data);
                                            }
                                        }
                                    }
                                    $valid = true;
                                }
                            }
                            if ($valid == true) {
                                if (isset($_POST['update_subscribers'])) {
                                    UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminSubscribers&action=Index&err=AS20");
                                } else {
                                    UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminSubscribers&action=Index&err=AS09");
                                }
                            } else {
                                UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminSubscribers&action=Index&err=AS21");
                            }
                        } else {
                            UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminSubscribers&action=Index&err=AS21");
                        }
                        break;
                    case 'csv':
                        if (isset($_FILES['csv']) && ! empty($_FILES['csv']['tmp_name'])) {
                            if (UtilComponent::getFileExtension($_FILES['csv']['name']) == 'csv') {
                                $csv_data = $this->loadCSV($_FILES['csv']);
                                $SubscriberModel = SubscriberModel::factory();
                                $GroupSubscriberModel = GroupSubscriberModel::factory();
                                $country_data = array();
                                $country_arr = CountryModel::factory()->select('t1.*')
                                    ->findAll()
                                    ->getData();
                                foreach ($country_arr as $country) {
                                    $country_data[$country['alpha_2']] = $country['id'];
                                }
                                $valid = true;
                                foreach ($csv_data as $row) {
                                    if (count($row) != 15) {
                                        $valid = false;
                                        break;
                                    } else {
                                        $same = true;
                                        foreach ($row as $col => $whatever) {
                                            if (! in_array($col, $map)) {
                                                $same = false;
                                                break;
                                            }
                                        }
                                    }
                                    if ($same == false) {
                                        $valid = false;
                                        break;
                                    }
                                    if (isset($row['country_id']) && ! empty($country_data)) {
                                        $row['country_id'] = $country_data[$row['country_id']];
                                    } else {
                                        $row['country_id'] = ':NULL';
                                    }
                                    if (isset($row['id'])) {
                                        unset($row['id']);
                                    }
                                    if (isset($row['created'])) {
                                        unset($row['created']);
                                    }
                                    if (isset($row['modified'])) {
                                        unset($row['modified']);
                                    }
                                    if ($SubscriberModel->reset()
                                        ->where('t1.email', $row['email'])
                                        ->findCount()
                                        ->getData() == 0) {
                                        $id = $SubscriberModel->reset()
                                            ->setAttributes($row)
                                            ->insert()
                                            ->getInsertId();
                                        if ($id !== false && (int) $id > 0) {
                                            if (isset($_POST['group_id']) && ! empty($_POST['group_id'])) {
                                                $data = array();
                                                $data['subscriber_id'] = $id;
                                                $data['group_id'] = $_POST['group_id'];
                                                $GroupSubscriberModel->reset()
                                                    ->setAttributes($data)
                                                    ->insert();
                                            }
                                        }
                                    } else {
                                        if (isset($_POST['update_subscribers'])) {
                                            $SubscriberModel->reset()->where('t1.email', $row['email']);
                                            if (isset($_POST['group_id']) && ! empty($_POST['group_id'])) {
                                                $SubscriberModel->where("t1.id IN(SELECT TGS.subscriber_id FROM `" . $GroupSubscriberModel->getTable() . "` AS TGS WHERE TGS.group_id = " . $_POST['group_id'] . ")");
                                            }
                                            $subscriber_arr = $SubscriberModel->findAll()->getData();
                                            if (count($subscriber_arr) > 0) {
                                                $id = $subscriber_arr[0]['id'];
                                                $row['modified'] = date('Y-m-d H:i:s');
                                                $SubscriberModel->reset()
                                                    ->set('id', $id)
                                                    ->modify($row);
                                            }
                                        }
                                    }
                                }
                                if ($valid == true) {
                                    if (isset($_POST['update_subscribers'])) {
                                        UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminSubscribers&action=Index&err=AS12");
                                    } else {
                                        UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminSubscribers&action=Index&err=AS09");
                                    }
                                } else {
                                    UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminSubscribers&action=Import&err=AS21");
                                }
                            } else {
                                UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminSubscribers&action=Import&err=AS10");
                            }
                        } else {
                            UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminSubscribers&action=Import&err=AS11");
                        }
                        break;
                }
            } else {
                $group_arr = GroupModel::factory()->where('status', 'T')
                    ->orderBy('group_title ASC')
                    ->findAll()
                    ->getData();
                $this->set('group_arr', $group_arr);
                $this->appendJs('chosen.jquery.min.js', THIRD_PARTY_PATH . 'chosen/');
                $this->appendCss('chosen.css', THIRD_PARTY_PATH . 'chosen/');
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('additional-methods.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('AdminSubscribers.js');
            }
        } else {
            $this->set('status', 2);
        }
    }
    
    public function DeleteSubscriber()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $response = array();
            if ($this->isAdmin()) {
                if (SubscriberModel::factory()->reset()
                    ->setAttributes(array(
                    'id' => $_GET['id']
                ))
                    ->erase()
                    ->getAffectedRows() == 1) {
                    GroupSubscriberModel::factory()->where('subscriber_id', $_GET['id'])->eraseAll();
                    $response['code'] = 200;
                } else {
                    $response['code'] = 100;
                }
            }
            AppController::jsonResponse($response);
        }
        exit();
    }

    public function DeleteSubscriberBulk()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if ($this->isAdmin() || $this->isEditor()) {
                if (isset($_POST['record']) && count($_POST['record']) > 0) {
                    SubscriberModel::factory()->reset()
                        ->whereIn('id', $_POST['record'])
                        ->eraseAll();
                    GroupSubscriberModel::factory()->whereIn('subscriber_id', $_POST['record'])->eraseAll();
                }
            }
        }
        exit();
    }

    public function ExportSubscriber()
    {
        $this->checkLogin();
        if (isset($_POST['record']) && is_array($_POST['record'])) {
            $arr = SubscriberModel::factory()->select("t1.*")
                ->whereIn('id', $_POST['record'])
                ->findAll()
                ->getData();
            $pair = CountryModel::factory()->findAll()->getDataPair('id', 'alpha_2');
            foreach ($arr as $k => $v) {
                if (! empty($v['country_id'])) {
                    $v['country_id'] = $pair[$v['country_id']];
                }
                unset($v['id']);
                unset($v['ip']);
                unset($v['modified']);
                unset($v['created']);
                $arr[$k] = $v;
            }
            $csv = new CSVComponent();
            $csv->setHeader(true)
                ->setName("Selected-Subscribers-" . time() . ".csv")
                ->process($arr)
                ->download();
        }
        exit();
    }

    public function ExportAllSubscribers()
    {
        $this->checkLogin();
        $arr = SubscriberModel::factory()->select("t1.*")
            ->findAll()
            ->getData();
        $pair = CountryModel::factory()->findAll()->getDataPair('id', 'alpha_2');
        foreach ($arr as $k => $v) {
            if (! empty($v['country_id'])) {
                $v['country_id'] = $pair[$v['country_id']];
            }
            unset($v['id']);
            unset($v['ip']);
            unset($v['modified']);
            unset($v['created']);
            $arr[$k] = $v;
        }
        $csv = new CSVComponent();
        $csv->setHeader(true)
            ->setName("All-Subscribers-" . time() . ".csv")
            ->process($arr)
            ->download();
        exit();
    }

    public function GetSubscriber()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $SubscriberModel = SubscriberModel::factory();
            if (isset($_GET['q']) && ! empty($_GET['q'])) {
                $q = Object::escapeString($_GET['q']);
                $SubscriberModel->where("(t1.first_name LIKE'%$q%' OR t1.last_name LIKE'%$q%' OR t1.email LIKE'%$q%')");
            }
            if (isset($_GET['first_name']) && ! empty($_GET['first_name'])) {
                $fname = Object::escapeString($_GET['first_name']);
                $SubscriberModel->where("(t1.first_name LIKE'%$fname%')");
            }
            if (isset($_GET['last_name']) && ! empty($_GET['last_name'])) {
                $lname = Object::escapeString($_GET['last_name']);
                $SubscriberModel->where("(t1.last_name LIKE'%$lname%')");
            }
            if (isset($_GET['email']) && ! empty($_GET['email'])) {
                $email = Object::escapeString($_GET['email']);
                $SubscriberModel->where("(t1.email LIKE'%$email%')");
            }
            if (isset($_GET['country_id']) && ! empty($_GET['country_id'])) {
                $SubscriberModel->where("t1.country_id", $_GET['country_id']);
            }
            if (isset($_GET['group_id']) && ! empty($_GET['group_id'])) {
                $SubscriberModel->where("t1.id IN(SELECT TGS.subscriber_id FROM `" . GroupSubscriberModel::factory()->getTable() . "` AS TGS WHERE TGS.group_id = " . $_GET['group_id'] . ")");
            }
            if (isset($_GET['gender']) && ! empty($_GET['gender']) && in_array($_GET['gender'], array(
                'F',
                'M'
            ))) {
                $SubscriberModel->where('t1.gender', $_GET['gender']);
            }
            if (isset($_GET['subscribed']) && ! empty($_GET['subscribed']) && in_array($_GET['subscribed'], array(
                'T',
                'F'
            ))) {
                $SubscriberModel->where('t1.subscribed', $_GET['subscribed']);
            }
            if (isset($_GET['subscribed_from']) && $_GET['subscribed_from'] != '' && isset($_GET['subscribed_to']) && $_GET['subscribed_to'] != '') {
                $subscribed_from = UtilComponent::formatDate($_GET['subscribed_from'], $this->option_arr['o_date_format']);
                $subscribed_to = UtilComponent::formatDate($_GET['subscribed_to'], $this->option_arr['o_date_format']);
                $SubscriberModel->where("(t1.created >= '$subscribed_from' AND t1.created <= '$subscribed_to')");
            } else {
                if (isset($_GET['subscribed_from']) && $_GET['subscribed_from'] != '') {
                    $subscribed_from = UtilComponent::formatDate($_GET['subscribed_from'], $this->option_arr['o_date_format']);
                    $SubscriberModel->where("(t1.created >= '$subscribed_from')");
                } else if (isset($_GET['subscribed_to']) && $_GET['subscribed_to'] != '') {
                    $subscribed_to = UtilComponent::formatDate($_GET['subscribed_to'], $this->option_arr['o_date_format']);
                    $SubscriberModel->where("(t1.created <= '$subscribed_to')");
                }
            }
            if (isset($_GET['age_from']) && $_GET['age_from'] != '' && isset($_GET['age_to']) && $_GET['age_to'] != '') {
                $SubscriberModel->where('t1.age >=', $_GET['age_from']);
                $SubscriberModel->where('t1.age <=', $_GET['age_to']);
            } else {
                if (isset($_GET['age_from']) && $_GET['age_from'] != '') {
                    $SubscriberModel->where('t1.age >=', $_GET['age_from']);
                } else if (isset($_GET['age_to']) && $_GET['age_to'] != '') {
                    $SubscriberModel->where('t1.age <=', $_GET['age_to']);
                }
            }
            $column = 'first_name';
            $direction = 'ASC';
            if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                'ASC',
                'DESC'
            ))) {
                $column = $_GET['column'];
                $direction = strtoupper($_GET['direction']);
            }
            $total = $SubscriberModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $gs_table = GroupSubscriberModel::factory()->getTable();
            $g_table = GroupModel::factory()->getTable();
            $q_table = QueueModel::factory()->getTable();
            $data = array();
            $arr = $SubscriberModel->select("t1.*,  (SELECT COUNT(subscriber_id) FROM `" . $q_table . "` AS TQ WHERE TQ.subscriber_id=t1.id AND TQ.status='completed') AS total_sent,  (SELECT date_sent FROM `" . $q_table . "` AS TQ WHERE TQ.subscriber_id=t1.id AND TQ.status='completed' ORDER BY date_sent DESC LIMIT 1) AS last_sent,  (SELECT GROUP_CONCAT(CONCAT(TG.id, '~:~', TG.group_title) SEPARATOR '~::~') FROM " . $gs_table . " AS TGS LEFT OUTER JOIN " . $g_table . " AS TG ON TGS.group_id=TG.id WHERE TGS.subscriber_id=t1.id) as groups")
                ->orderBy("$column $direction")
                ->limit($rowCount, $offset)
                ->findAll()
                ->getData();
            foreach ($arr as $k => $v) {
                $group_arr = explode("~::~", $v['groups']);
                $group_temp_arr = array();
                foreach ($group_arr as $group) {
                    list ($group_id, $group_title) = explode("~:~", $group);
                    $group_temp_arr[] = '<a href="' . BASE_URL . 'index.php?controller=AdminGroups&action=Update&id=' . $group_id . '">' . $group_title . '</a>';
                }
                $v['groups'] = join("<br/>", $group_temp_arr);
                if (! empty($v['last_sent'])) {
                    $v['last_sent'] = date($this->option_arr['o_date_format'], strtotime($v['last_sent'])) . ', ' . date($this->option_arr['o_time_format'], strtotime($v['last_sent']));
                }
                $name_arr = array();
                $name_email_arr = array();
                if (! empty($v['first_name'])) {
                    $name_arr[] = $v['first_name'];
                }
                if (! empty($v['last_name'])) {
                    $name_arr[] = $v['last_name'];
                }
                if (! empty($name_arr)) {
                    $name_email_arr[] = join(" ", $name_arr);
                }
                if (! empty($v['email'])) {
                    $name_email_arr[] = $v['email'];
                }
                $v['name_email'] = join("<br/>", $name_email_arr);
                $data[$k] = $v;
            }
            AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
        }
        exit();
    }

    public function Index()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            $group_arr = GroupModel::factory()->where('status', 'T')
                ->orderBy('group_title ASC')
                ->findAll()
                ->getData();
            $country_arr = CountryModel::factory()->select('t1.id, t2.content AS country_title')
                ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
                ->orderBy('`country_title` ASC')
                ->findAll()
                ->getData();
            $message_arr = MessageModel::factory()->where('status', 'T')
                ->orderBy('subject ASC')
                ->findAll()
                ->getData();
            $this->set('group_arr', $group_arr);
            $this->set('country_arr', $country_arr);
            $this->set('message_arr', $message_arr);
            $this->appendJs('chosen.jquery.min.js', THIRD_PARTY_PATH . 'chosen/');
            $this->appendCss('chosen.css', THIRD_PARTY_PATH . 'chosen/');
            $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
            $this->appendJs('AdminSubscribers.js');
            $this->appendJs('index.php?controller=Admin&action=Messages&page=subscriber', BASE_URL, true);
        } else {
            $this->set('status', 2);
        }
    }


    public function SaveSubscriber()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if ($_POST['column'] == 'first_name') {
                if ($_POST['value'] != '') {
                    SubscriberModel::factory()->where('id', $_GET['id'])
                        ->limit(1)
                        ->modifyAll(array(
                        $_POST['column'] => $_POST['value']
                    ));
                }
            } else {
                SubscriberModel::factory()->where('id', $_GET['id'])
                    ->limit(1)
                    ->modifyAll(array(
                    $_POST['column'] => $_POST['value']
                ));
            }
        }
        exit();
    }


    public function StatusSubscriber()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_POST['record']) && count($_POST['record']) > 0) {
                SubscriberModel::factory()->whereIn('id', $_POST['record'])->modifyAll(array(
                    'status' => ":IF(`status`='F','T','F')"
                ));
            }
        }
        exit();
    }

    public function Update()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            $SubscriberModel = SubscriberModel::factory();
            if (isset($_POST['subscriber_update'])) {
                $data = array();
                if (isset($_POST['birthday']) && ! empty($_POST['birthday'])) {
                    $data['birthday'] = UtilComponent::formatDate($_POST['birthday'], $this->option_arr['o_date_format']);
                }
                unset($_POST['birthday']);
                $data['ip'] = $_SERVER['REMOTE_ADDR'];
                if (! isset($_POST['subscribed'])) {
                    $data['subscribed'] = 'F';
                }
                $data['modified'] = date('Y-m-d H:i:s');
                $data = array_merge($_POST, $data);
                $SubscriberModel->reset()
                    ->where('id', $_POST['id'])
                    ->limit(1)
                    ->modifyAll($data);
                $GroupSubscriberModel = GroupSubscriberModel::factory();
                $GroupSubscriberModel->where('subscriber_id', $_POST['id'])->eraseAll();
                if (isset($_POST['group_id'])) {
                    $GroupSubscriberModel->reset()->begin();
                    foreach ($_POST['group_id'] as $group_id) {
                        $data = array();
                        $data['subscriber_id'] = $_POST['id'];
                        $data['group_id'] = $group_id;
                        $GroupSubscriberModel->reset()
                            ->setAttributes($data)
                            ->insert();
                    }
                    $GroupSubscriberModel->commit();
                }
                UtilComponent::redirect(BASE_URL . "index.php?controller=AdminSubscribers&action=Index&err=AS01");
            } else {
                $arr = $SubscriberModel->find($_GET['id'])->getData();
                if (count($arr) === 0) {
                    UtilComponent::redirect(BASE_URL . "index.php?controller=AdminSubscribers&action=Index&err=AS08");
                }
                $this->set('arr', $arr);
                $group_arr = GroupModel::factory()->where('status', 'T')
                    ->orderBy('group_title ASC')
                    ->findAll()
                    ->getData();
                $country_arr = CountryModel::factory()->select('t1.id, t2.content AS country_title')
                    ->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
                    ->orderBy('`country_title` ASC')
                    ->findAll()
                    ->getData();
                $subscriber_group_arr = GroupSubscriberModel::factory()->where('subscriber_id', $_GET['id'])
                    ->findAll()
                    ->getData();
                $group_id_arr = array();
                if (! empty($subscriber_group_arr)) {
                    foreach ($subscriber_group_arr as $v) {
                        $group_id_arr[] = $v['group_id'];
                    }
                }
                $messages_sent = QueueModel::factory()->where('status', 'completed')
                    ->where('subscriber_id', $_GET['id'])
                    ->findCount()
                    ->getData();
                $this->set('group_arr', $group_arr);
                $this->set('country_arr', $country_arr);
                $this->set('group_id_arr', $group_id_arr);
                $this->set('messages_sent', $messages_sent);
                $this->appendJs('chosen.jquery.min.js', THIRD_PARTY_PATH . 'chosen/');
                $this->appendCss('chosen.css', THIRD_PARTY_PATH . 'chosen/');
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('additional-methods.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('AdminSubscribers.js');
            }
        } else {
            $this->set('status', 2);
        }
    }


    public function Send()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $response = array();
            $SubscriberModel = SubscriberModel::factory();
            $QueueModel = QueueModel::factory();
            $message_arr = MessageModel::factory()->find($_POST['message_id'])->getData();
            $file_arr = FileModel::factory()->where('t1.message_id', $_POST['message_id'])
                ->findAll()
                ->getData();
            $subscriber_id_arr = explode(",", $_GET['id']);
            $subscriber_arr = $SubscriberModel->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.country_id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
                ->select('t1.*, t2.content as country_title')
                ->whereIn('t1.id', $subscriber_id_arr)
                ->findAll()
                ->getData();
            foreach ($subscriber_arr as $subscriber) {
                if (! empty($subscriber['email'])) {
                    $data = AppController::getData($subscriber, $this->option_arr, SALT, false);
                    $message = str_replace($data['search'], $data['replace'], $message_arr['tinymce_message']);
                    $subject = str_replace($data['search'], $data['replace'], $message_arr['subject']);
                    $data = AppController::getData($subscriber, $this->option_arr, SALT, true);
                    $plain_message = str_replace($data['search'], $data['replace'], $message_arr['plain_message']);
                    AppController::sendMessage($subscriber['email'], $subject, $message, $plain_message, $this->option_arr, $file_arr);
                    $data = array();
                    $data['message_id'] = $_POST['message_id'];
                    $data['subscriber_id'] = $_GET['id'];
                    $data['date_sent'] = date("Y-m-d H:i:s");
                    $data['status'] = 'completed';
                    $QueueModel->reset()
                        ->setAttributes($data)
                        ->insert();
                }
            }
            $response['code'] = 200;
            AppController::jsonResponse($response);
        }
        exit();
    }


    public function AddToGroup()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $response = array();
            if (! empty($_POST['group_id']) && ! empty($_POST['subscriber_id'])) {
                $GroupSubscriberModel = GroupSubscriberModel::factory();
                foreach ($_POST['group_id'] as $group_id) {
                    foreach ($_POST['subscriber_id'] as $subscriber_id) {
                        if ($_POST['remove'] == 1) {
                            $GroupSubscriberModel->reset()
                                ->where('subscriber_id', $subscriber_id)
                                ->eraseAll();
                        }
                        $count = $GroupSubscriberModel->reset()
                            ->where('group_id', $group_id)
                            ->where('subscriber_id', $subscriber_id)
                            ->findCount()
                            ->getData();
                        if ($count == 0) {
                            $data = array();
                            $data['group_id'] = $group_id;
                            $data['subscriber_id'] = $subscriber_id;
                            $GroupSubscriberModel->reset()
                                ->setAttributes($data)
                                ->insert();
                        }
                    }
                }
            }
            $response['code'] = 200;
            AppController::jsonResponse($response);
        }
        exit();
    }
}
?>