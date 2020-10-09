<?php
namespace App\Controllers;

use App\Models\QueueModel;
use App\Controllers\Components\UtilComponent;

class AdminQueuesController extends AdminController
{

    public function DeleteQueue()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $response = array();
            if ($this->isAdmin()) {
                if (QueueModel::factory()->setAttributes(array(
                    'id' => $_GET['id']
                ))
                    ->erase()
                    ->getAffectedRows() == 1) {
                    $response['code'] = 200;
                } else {
                    $response['code'] = 100;
                }
            }
            AppController::jsonResponse($response);
        }
        exit();
    }

    public function DeleteQueueBulk()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if ($this->isAdmin()) {
                if (isset($_POST['record']) && count($_POST['record']) > 0) {
                    QueueModel::factory()->reset()
                        ->whereIn('id', $_POST['record'])
                        ->eraseAll();
                }
            }
        }
        exit();
    }

    public function SaveQueue()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if ($_POST['column'] == 'status') {
                if ($_POST['value'] != '') {
                    QueueModel::factory()->where('id', $_GET['id'])
                        ->limit(1)
                        ->modifyAll(array(
                        $_POST['column'] => $_POST['value']
                    ));
                }
            } else {
                QueueModel::factory()->where('id', $_GET['id'])
                    ->limit(1)
                    ->modifyAll(array(
                    $_POST['column'] => $_POST['value']
                ));
            }
        }
        exit();
    }

    public function GetQueue()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $QueueModel = QueueModel::factory();
            $QueueModel->join('Message', 't1.message_id = t2.id', 'left');
            $QueueModel->join('Subscriber', 't1.subscriber_id = t3.id', 'left');
            if (isset($_GET['q']) && ! empty($_GET['q'])) {
                $q = Object::escapeString($_GET['q']);
                $QueueModel->where("(t2.subject LIKE '%$q%' OR t3.email LIKE '%$q%')");
            }
            if (isset($_GET['subscriber_id']) && ! empty($_GET['subscriber_id'])) {
                $QueueModel->where('t1.subscriber_id', $_GET['subscriber_id']);
                $QueueModel->where('t1.status', 'completed');
            }
            if (isset($_GET['status']) && ! empty($_GET['status']) && in_array($_GET['status'], array(
                'inprogress',
                'completed'
            ))) {
                $QueueModel->where('t1.status', $_GET['status']);
            }
            $column = 'date_sent';
            $direction = 'DESC';
            if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                'ASC',
                'DESC'
            ))) {
                $column = $_GET['column'];
                $direction = strtoupper($_GET['direction']);
            }
            $total = $QueueModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $data = array();
            $arr = $QueueModel->select("t1.*, t2.subject, t3.email")
                ->orderBy("$column $direction")
                ->limit($rowCount, $offset)
                ->findAll()
                ->getData();
            foreach ($arr as $k => $v) {
                $v['date_sent'] = UtilComponent::formatDate(date('Y-m-d', strtotime($v['date_sent'])), 'Y-m-d', $this->option_arr['o_date_format']) . ', ' . UtilComponent::formatTime(date('H:i:s', strtotime($v['date_sent'])), 'H:i:s', $this->option_arr['o_time_format']);
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
            $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
            $this->appendJs('AdminQueues.js');
            $this->appendJs('index.php?controller=Admin&action=Messages&page=queue', BASE_URL, true);
        } else {
            $this->set('status', 2);
        }
    }
}
?>