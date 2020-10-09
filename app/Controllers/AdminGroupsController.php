<?php
namespace App\Controllers;

use App\Models\GroupModel;
use App\Models\GroupSubscriberModel;
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\CSVComponent;
use App\Models\SubscriberModel;

class AdminGroupsController extends AdminController
{

    public function CheckGroupName()
    {
        $this->setAjax(true);
        if ($this->isXHR() && isset($_GET['group_title'])) {
            $GroupModel = GroupModel::factory();
            if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
                $GroupModel->where('t1.id !=', $_GET['id']);
            }
            echo $GroupModel->where('t1.group_title', $_GET['group_title'])
                ->findCount()
                ->getData() == 0 ? 'true' : 'false';
        }
        exit();
    }

    public function Create()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            if (isset($_POST['group_create'])) {
                $GroupModel = GroupModel::factory();
                $id = $GroupModel->setAttributes($_POST)
                    ->insert()
                    ->getInsertId();
                if ($id !== false && (int) $id > 0) {
                    $err = 'AG03';
                } else {
                    $err = 'AG04';
                }
                UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminGroups&action=Update&id=$id&err=$err");
            } else {
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('additional-methods.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('AdminGroups.js');
            }
        } else {
            $this->set('status', 2);
        }
    }

    public function DeleteGroup()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $response = array();
            if ($this->isAdmin()) {
                if (GroupModel::factory()->reset()
                    ->setAttributes(array(
                    'id' => $_GET['id']
                ))
                    ->erase()
                    ->getAffectedRows() == 1) {
                    GroupSubscriberModel::factory()->where('group_id', $_GET['id'])->eraseAll();
                    $response['code'] = 200;
                } else {
                    $response['code'] = 100;
                }
            }
            AppController::jsonResponse($response);
        }
        exit();
    }

    public function DeleteGroupBulk()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if ($this->isAdmin()) {
                if (isset($_POST['record']) && count($_POST['record']) > 0) {
                    GroupModel::factory()->reset()
                        ->whereIn('id', $_POST['record'])
                        ->eraseAll();
                    GroupSubscriberModel::factory()->whereIn('group_id', $_POST['record'])->eraseAll();
                }
            }
        }
        exit();
    }

    public function ExportGroup()
    {
        $this->checkLogin();
        if (isset($_POST['record']) && is_array($_POST['record'])) {
            $arr = GroupModel::factory()->whereIn('id', $_POST['record'])
                ->findAll()
                ->getData();
            $csv = new CSVComponent();
            $csv->setHeader(true)
                ->setName("Groups-" . time() . ".csv")
                ->process($arr)
                ->download();
        }
        exit();
    }

    public function GetGroup()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $GroupModel = GroupModel::factory();
            if (isset($_GET['q']) && ! empty($_GET['q'])) {
                $q = Object::escapeString($_GET['q']);
                $GroupModel->where('t1.group_title LIKE', "%$q%");
            }
            if (isset($_GET['status']) && ! empty($_GET['status']) && in_array($_GET['status'], array(
                'T',
                'F'
            ))) {
                $GroupModel->where('t1.status', $_GET['status']);
            }
            $column = 'group_title';
            $direction = 'ASC';
            if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                'ASC',
                'DESC'
            ))) {
                $column = $_GET['column'];
                $direction = strtoupper($_GET['direction']);
            }
            $total = $GroupModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $tbl1_name = GroupSubscriberModel::factory()->getTable();
            $tbl2_name = SubscriberModel::factory()->getTable();
            $data = $GroupModel->select("t1.*, (SELECT COUNT(*) FROM `" . $tbl1_name . "` as t2 WHERE t2.group_id = t1.id) as total,  (SELECT COUNT(*) FROM `" . $tbl1_name . "` as t3 WHERE t3.group_id = t1.id AND t3.subscriber_id IN(SELECT t4.id FROM `" . $tbl2_name . "` AS t4 WHERE t4.subscribed='T') ) as subscribed,  (SELECT COUNT(*) FROM `" . $tbl1_name . "` as t3 WHERE t3.group_id = t1.id AND t3.subscriber_id IN(SELECT t4.id FROM `" . $tbl2_name . "` AS t4 WHERE t4.subscribed='F') ) as unsubscribed")
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
            $this->appendJs('AdminGroups.js');
            $this->appendJs('index.php?controller=Admin&action=Messages&page=list', BASE_URL, true);
        } else {
            $this->set('status', 2);
        }
    }

    public function SaveGroup()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if ($_POST['column'] == 'group_title') {
                if ($_POST['value'] != '') {
                    $GroupModel = GroupModel::factory();
                    $check = $GroupModel->where('t1.group_title', $_POST['value'])
                        ->findCount()
                        ->getData() == 0 ? true : false;
                    if ($check == true) {
                        $GroupModel->reset()
                            ->where('id', $_GET['id'])
                            ->limit(1)
                            ->modifyAll(array(
                            $_POST['column'] => $_POST['value']
                        ));
                    }
                }
            } else {
                GroupModel::factory()->where('id', $_GET['id'])
                    ->limit(1)
                    ->modifyAll(array(
                    $_POST['column'] => $_POST['value']
                ));
            }
        }
        exit();
    }


    public function StatusGroup()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_POST['record']) && count($_POST['record']) > 0) {
                GroupModel::factory()->whereIn('id', $_POST['record'])->modifyAll(array(
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
            $GroupModel = GroupModel::factory();
            if (isset($_POST['group_update'])) {
                $data = array();
                if (isset($_POST['send_confirm'])) {
                    $data['send_confirm'] = 'T';
                } else {
                    $data['send_confirm'] = 'F';
                }
                if (isset($_POST['send_response'])) {
                    $data['send_response'] = 'T';
                } else {
                    $data['send_response'] = 'F';
                }
                unset($_POST['send_confirm']);
                unset($_POST['send_response']);
                $GroupModel->reset()
                    ->where('id', $_POST['id'])
                    ->limit(1)
                    ->modifyAll(array_merge($_POST, $data));
                UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminGroups&action=Update&id=" . $_POST['id'] . "&tab_id=" . $_POST['tab_id'] . "&err=AG01");
            } else {
                $tbl1_name = GroupSubscriberModel::factory()->getTable();
                $tbl2_name = SubscriberModel::factory()->getTable();
                $arr = $GroupModel->select("t1.*, (SELECT COUNT(*) FROM `" . $tbl1_name . "` as t2 WHERE t2.group_id = t1.id) as total,  (SELECT COUNT(*) FROM `" . $tbl1_name . "` as t3 WHERE t3.group_id = t1.id AND t3.subscriber_id IN(SELECT t4.id FROM `" . $tbl2_name . "` AS t4 WHERE t4.subscribed='T') ) as subscribed,  (SELECT COUNT(*) FROM `" . $tbl1_name . "` as t3 WHERE t3.group_id = t1.id AND t3.subscriber_id IN(SELECT t4.id FROM `" . $tbl2_name . "` AS t4 WHERE t4.subscribed='F') ) as unsubscribed")
                    ->find($_GET['id'])
                    ->getData();
                if (count($arr) === 0) {
                    UtilComponent::redirect(BASE_URL . "index.php?controller=AdminGroups&action=Index&err=AG08");
                }
                $this->set('arr', $arr);
                $this->appendJs('tinymce.min.js', THIRD_PARTY_PATH . 'tinymce/');
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('additional-methods.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('AdminGroups.js');
            }
        } else {
            $this->set('status', 2);
        }
    }
}
?>