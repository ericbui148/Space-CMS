<?php

namespace App\Controllers;

use App\Models\MessageModel;
use App\Models\FileModel;
use Core\Framework\Components\UploadComponent;
use Core\Framework\Components\CSVComponent;
use App\Models\QueueModel;
use App\Controllers\Components\UtilComponent;
use App\Models\SubscriberModel;
use App\Models\GroupSubscriberModel;
use App\Models\GroupModel;
use Core\Framework\Components\ToolkitComponent;

class AdminMessagesController extends AdminController
{

    public function Create()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            if (isset($_POST['message_create'])) {
                $MessageModel = MessageModel::factory();
                $id = $MessageModel->setAttributes($_POST)
                    ->insert()
                    ->getInsertId();
                if ($id !== false && (int) $id > 0) {
                    if (isset($_FILES['files']) && ! empty($_FILES['files']['tmp_name'])) {
                        $files = array();
                        foreach ($_FILES['files'] as $k => $l) {
                            foreach ($l as $i => $v) {
                                if (! array_key_exists($i, $files)) {
                                    $files[$i] = array();
                                }
                                $files[$i][$k] = $v;
                            }
                        }
                        $FileModel = FileModel::factory();
                        foreach ($files as $file) {
                            $data = array();
                            $data['message_id'] = $id;
                            $handle = new UploadComponent();
                            if ($handle->load($file)) {
                                $hash = md5(uniqid(rand(), true));
                                $file_ext = $handle->getExtension();
                                $file_path = UPLOAD_PATH . 'files/' . $id . "_" . $hash . '.' . $file_ext;
                                if ($handle->save($file_path)) {
                                    $data['file_path'] = $file_path;
                                    $data['file_name'] = $file['name'];
                                    $data['mime_type'] = $file['type'];
                                    $data['hash'] = $hash;
                                    $FileModel->reset()
                                        ->setAttributes($data)
                                        ->insert();
                                }
                            }
                        }
                    }
                    $err = 'AM03';
                } else {
                    $err = 'AM04';
                }
                UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminMessages&action=Update&id=$id&err=$err");
            } else {
                $this->appendJs('tinymce.min.js', THIRD_PARTY_PATH . 'tinymce/');
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('additional-methods.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('AdminMessages.js');
            }
        } else {
            $this->set('status', 2);
        }
    }

    public function DeleteMessage()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $response = array();
            if ($this->isAdmin()) {
                if (MessageModel::factory()->setAttributes(array(
                    'id' => $_GET['id']
                ))
                    ->erase()
                    ->getAffectedRows() == 1) {
                    $FileModel = FileModel::factory();
                    $FileModel->where('message_id', $_GET['id']);
                    $file_arr = $FileModel->findAll()->getData();
                    foreach ($file_arr as $f) {
                        $file_path = $f['file_path'];
                        if (file_exists(INSTALL_PATH . $file_path)) {
                            if (unlink(INSTALL_PATH . $file_path)) {}
                        }
                    }
                    $FileModel->reset()
                        ->where('message_id', $_GET['id'])
                        ->eraseAll();
                    $response['code'] = 200;
                } else {
                    $response['code'] = 100;
                }
            }
            AppController::jsonResponse($response);
        }
        exit();
    }

    public function DeleteMessageBulk()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if ($this->isAdmin()) {
                if (isset($_POST['record']) && count($_POST['record']) > 0) {
                    MessageModel::factory()->reset()
                        ->whereIn('id', $_POST['record'])
                        ->eraseAll();
                    $FileModel = FileModel::factory();
                    $FileModel->whereIn('message_id', $_POST['record']);
                    $file_arr = $FileModel->findAll()->getData();
                    foreach ($file_arr as $f) {
                        $file_path = $f['file_path'];
                        if (file_exists(INSTALL_PATH . $file_path)) {
                            if (unlink(INSTALL_PATH . $file_path)) {}
                        }
                    }
                    $FileModel->reset()
                        ->whereIn('message_id', $_POST['record'])
                        ->eraseAll();
                }
            }
        }
        exit();
    }

    public function ExportMessage()
    {
        $this->checkLogin();
        if (isset($_POST['record']) && is_array($_POST['record'])) {
            $arr = MessageModel::factory()->select("t1.*")
                ->whereIn('id', $_POST['record'])
                ->findAll()
                ->getData();
            $csv = new CSVComponent();
            $csv->setHeader(true)
                ->setName("Messages-" . time() . ".csv")
                ->process($arr)
                ->download();
        }
        exit();
    }

    public function Index()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
            $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
            $this->appendJs('AdminMessages.js');
            $this->appendJs('index.php?controller=Admin&action=Messages&page=message', BASE_URL, true);
        } else {
            $this->set('status', 2);
        }
    }

    public function GetMessage()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $MessageModel = MessageModel::factory();
            if (isset($_GET['q']) && ! empty($_GET['q'])) {
                $q = Object::escapeString($_GET['q']);
                $MessageModel->where("(t1.subject LIKE '%$q%' OR t1.tinymce_message LIKE '%$q%' OR t1.plain_message LIKE '%$q%')");
            }
            if (isset($_GET['status']) && ! empty($_GET['status']) && in_array($_GET['status'], array(
                'T',
                'F'
            ))) {
                $MessageModel->where('t1.status', $_GET['status']);
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
            $total = $MessageModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $data = array();
            $arr = $MessageModel->select("t1.*, 	(SELECT TQ.date_sent FROM `" . QueueModel::factory()->getTable() . "` as TQ WHERE TQ.message_id = t1.id AND TQ.status = 'completed' ORDER BY TQ.date_sent DESC LIMIT 1) as last_sent,  (SELECT COUNT(*) FROM `" . QueueModel::factory()->getTable() . "` as TQ2 WHERE TQ2.message_id = t1.id AND TQ2.status = 'completed' LIMIT 1) as total_sent")
                ->orderBy("$column $direction")
                ->limit($rowCount, $offset)
                ->findAll()
                ->getData();
            foreach ($arr as $k => $v) {
                if (empty($v['last_sent'])) {
                    $v['last_sent'] = __('lblNeverSent', true);
                } else {
                    $v['last_sent'] = UtilComponent::formatDate(date('Y-m-d', strtotime($v['last_sent'])), 'Y-m-d', $this->option_arr['o_date_format']) . ' ' . UtilComponent::formatTime(date('H:i:s', strtotime($v['last_sent'])), 'H:i:s', $this->option_arr['o_time_format']);
                }
                $data[$k] = $v;
            }
            AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
        }
        exit();
    }
    
    public function SaveMessage()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if ($_POST['column'] == 'subject') {
                if ($_POST['value'] != '') {
                    MessageModel::factory()->where('id', $_GET['id'])
                        ->limit(1)
                        ->modifyAll(array(
                        $_POST['column'] => $_POST['value']
                    ));
                }
            } else {
                MessageModel::factory()->where('id', $_GET['id'])
                    ->limit(1)
                    ->modifyAll(array(
                    $_POST['column'] => $_POST['value']
                ));
            }
        }
        exit();
    }

    public function StatusMessage()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_POST['record']) && count($_POST['record']) > 0) {
                MessageModel::factory()->whereIn('id', $_POST['record'])->modifyAll(array(
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
            $MessageModel = MessageModel::factory();
            if (isset($_POST['message_update'])) {
                $data = array();
                $data['modified'] = date('Y-m-d H:i:s');
                $data = array_merge($_POST, $data);
                $MessageModel->reset()
                    ->where('id', $_POST['id'])
                    ->limit(1)
                    ->modifyAll($data);
                if (isset($_FILES['files']) && ! empty($_FILES['files']['tmp_name'])) {
                    $files = array();
                    foreach ($_FILES['files'] as $k => $l) {
                        foreach ($l as $i => $v) {
                            if (! array_key_exists($i, $files)) {
                                $files[$i] = array();
                            }
                            $files[$i][$k] = $v;
                        }
                    }
                    $FileModel = FileModel::factory();
                    foreach ($files as $file) {
                        $data = array();
                        $data['message_id'] = $_POST['id'];
                        $handle = new UploadComponent();
                        if ($handle->load($file)) {
                            $hash = md5(uniqid(rand(), true));
                            $file_ext = $handle->getExtension();
                            $file_path = UPLOAD_PATH . 'files/' . $_POST['id'] . "_" . $hash . '.' . $file_ext;
                            if ($handle->save($file_path)) {
                                $data['file_path'] = $file_path;
                                $data['file_name'] = $file['name'];
                                $data['mime_type'] = $file['type'];
                                $data['hash'] = $hash;
                                $FileModel->reset()
                                    ->setAttributes($data)
                                    ->insert();
                            }
                        }
                    }
                }
                if ($_POST['send'] == 0) {
                    UtilComponent::redirect(BASE_URL . "index.php?controller=AdminMessages&action=Update&id=" . $_POST['id'] . "&err=AM01");
                } else {
                    UtilComponent::redirect(BASE_URL . "index.php?controller=AdminMessages&action=Send&id=" . $_POST['id']);
                }
            } else {
                $arr = $MessageModel->find($_GET['id'])->getData();
                if (count($arr) === 0) {
                    UtilComponent::redirect(BASE_URL . "index.php?controller=AdminMessages&action=Index&err=AM08");
                }
                $file_arr = FileModel::factory()->where("message_id", $_GET['id'])
                    ->findAll()
                    ->getData();
                $this->set('arr', $arr);
                $this->set('file_arr', $file_arr);
                $this->appendJs('tinymce.min.js', THIRD_PARTY_PATH . 'tinymce/');
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('additional-methods.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('AdminMessages.js');
            }
        } else {
            $this->set('status', 2);
        }
    }

    public function Send()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            if (isset($_POST['message_send'])) {
                $SubscriberModel = SubscriberModel::factory();
                $message_arr = MessageModel::factory()->find($_POST['message_id'])->getData();
                $file_arr = FileModel::factory()->where('t1.message_id', $_POST['message_id'])
                    ->findAll()
                    ->getData();
                $group_id_str = implode(',', $_POST['group_id']);
                $SubscriberModel->join('MultiLang', "t2.model='Country' AND t2.foreign_id=t1.country_id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
                    ->where('subscribed', 'T')
                    ->where("t1.id IN(SELECT TGS.subscriber_id FROM `" . GroupSubscriberModel::factory()->getTable() . "` AS TGS WHERE TGS.group_id IN($group_id_str))");
                $subscriber_arr = $SubscriberModel->select('t1.*, t2.content AS country_title')
                    ->findAll()
                    ->getData();
                $err = '';
                if (count($subscriber_arr) > 0) {
                    if ($_POST['schedule'] == 'now') {
                        if ($_POST['send_in_batches'] == 'no') {
                            $QueueModel = QueueModel::factory();
                            foreach ($subscriber_arr as $subscriber) {
                                if (! empty($subscriber['email']) && $subscriber['subscribed'] == 'T') {
                                    $data = AppController::getData($subscriber, $this->option_arr, SALT, false);
                                    $message = str_replace($data['search'], $data['replace'], $message_arr['tinymce_message']);
                                    $subject = str_replace($data['search'], $data['replace'], $message_arr['subject']);
                                    $data = AppController::getData($subscriber, $this->option_arr, SALT, true);
                                    $plain_message = str_replace($data['search'], $data['replace'], $message_arr['plain_message']);
                                    AppController::sendMessage($subscriber['email'], $subject, $message, $plain_message, $this->option_arr, $file_arr);
                                    $data = array();
                                    $data['message_id'] = $_POST['message_id'];
                                    $data['subscriber_id'] = $subscriber['id'];
                                    $data['date_sent'] = date("Y-m-d H:i:s");
                                    $data['status'] = 'completed';
                                    $QueueModel->reset()
                                        ->setAttributes($data)
                                        ->insert();
                                }
                            }
                            $err = 'AM09';
                        } else {
                            $QueueModel = QueueModel::factory();
                            $start_time = time();
                            $msg_index = 1;
                            $send_time = $start_time;
                            $max_messages = $_POST['send_messages'];
                            foreach ($subscriber_arr as $subscriber) {
                                if (! empty($subscriber['email']) && $subscriber['subscribed'] == 'T') {
                                    if ($max_messages == 0) {
                                        $max_messages = $_POST['send_messages'];
                                        $send_time = $start_time + $_POST['send_minutes'] * 60 * $msg_index;
                                        $msg_index ++;
                                    }
                                    $data = array();
                                    $data['message_id'] = $_POST['message_id'];
                                    $data['subscriber_id'] = $subscriber['id'];
                                    $data['date_sent'] = date("Y-m-d H:i:s", $send_time);
                                    $data['status'] = 'inprogress';
                                    $QueueModel->reset()
                                        ->setAttributes($data)
                                        ->insert();
                                    $max_messages --;
                                }
                            }
                            $err = 'AM10';
                        }
                    } else if ($_POST['schedule'] == 'later') {
                        $_send_on = $_POST['send_on'];
                        if (count(explode(" ", $_send_on)) == 3) {
                            list ($_send_on_date, $_send_on_time, $_send_on_period) = explode(" ", $_send_on);
                            $_send_on_time = UtilComponent::formatTime($_send_on_time . ' ' . $_send_on_period, $this->option_arr['o_time_format']);
                        } else {
                            list ($_send_on_date, $_send_on_time) = explode(" ", $_send_on);
                            $_send_on_time = UtilComponent::formatTime($_send_on_time, $this->option_arr['o_time_format']);
                        }
                        $date_sent = UtilComponent::formatDate($_send_on_date, $this->option_arr['o_date_format']) . ' ' . $_send_on_time;
                        if ($_POST['send_in_batches'] == 'no') {
                            $QueueModel = QueueModel::factory();
                            foreach ($subscriber_arr as $subscriber) {
                                if (! empty($subscriber['email']) && $subscriber['subscribed'] == 'T') {
                                    $data = array();
                                    $data['message_id'] = $_POST['message_id'];
                                    $data['subscriber_id'] = $subscriber['id'];
                                    $data['date_sent'] = $date_sent;
                                    $data['status'] = 'inprogress';
                                    $QueueModel->reset()
                                        ->setAttributes($data)
                                        ->insert();
                                }
                            }
                            $err = 'AM10';
                        } else {
                            $QueueModel = QueueModel::factory();
                            $start_time = strtotime($date_sent);
                            $msg_index = 1;
                            $send_time = $start_time;
                            $max_messages = $_POST['send_messages'];
                            foreach ($subscriber_arr as $subscriber) {
                                if (! empty($subscriber['email']) && $subscriber['subscribed'] == 'T') {
                                    if ($max_messages == 0) {
                                        $max_messages = $_POST['send_messages'];
                                        $send_time = $start_time + $_POST['send_minutes'] * 60 * $msg_index;
                                        $msg_index ++;
                                    }
                                    $data = array();
                                    $data['message_id'] = $_POST['message_id'];
                                    $data['subscriber_id'] = $subscriber['id'];
                                    $data['date_sent'] = date("Y-m-d H:i:s", $send_time);
                                    $data['status'] = 'inprogress';
                                    $QueueModel->reset()
                                        ->setAttributes($data)
                                        ->insert();
                                    $max_messages --;
                                }
                            }
                            $err = 'AM10';
                        }
                    }
                    UtilComponent::redirect(BASE_URL . "index.php?controller=AdminMessages&action=Send&err=$err");
                } else {
                    UtilComponent::redirect(BASE_URL . "index.php?controller=AdminMessages&action=Send&err=AM11");
                }
            } else {
                $MessageModel = MessageModel::factory();
                $message_arr = $MessageModel->where('t1.status', 'T')
                    ->orderBy('created DESC')
                    ->findAll()
                    ->getData();
                $group_arr = GroupModel::factory()->where('status', 'T')
                    ->orderBy('group_title ASC')
                    ->findAll()
                    ->getData();
                $this->set('message_arr', $message_arr);
                $this->set('group_arr', $group_arr);
                $this->appendJs('jquery-ui-timepicker-addon.js', THIRD_PARTY_PATH . 'datetimepicker/');
                $this->appendCss('jquery-ui-timepicker-addon.css', THIRD_PARTY_PATH . 'datetimepicker/');
                $this->appendJs('chosen.jquery.min.js', THIRD_PARTY_PATH . 'chosen/');
                $this->appendCss('chosen.css', THIRD_PARTY_PATH . 'chosen/');
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('AdminMessages.js');
            }
        } else {
            $this->set('status', 2);
        }
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
            $column = 'status';
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
            $arr = $QueueModel->select("t1.*, t2.id as message_id, t3.email")
                ->orderBy("$column $direction")
                ->limit($rowCount, $offset)
                ->findAll()
                ->getData();
            foreach ($arr as $k => $v) {
                $v['date_sent'] = UtilComponent::formatDate(date('Y-m-d', strtotime($v['date_sent'])), 'Y-m-d', $this->option_arr['o_date_format']) . ' ' . UtilComponent::formatTime(date('H:i:s', strtotime($v['date_sent'])), 'H:i:s', $this->option_arr['o_time_format']);
                $data[$k] = $v;
            }
            AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
        }
        exit();
    }


    public function Preview()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            $arr = MessageModel::factory()->find($_GET['id'])->getData();
            if (count($arr) === 0) {
                UtilComponent::redirect(BASE_URL . "index.php?controller=AdminMessages&action=Index&err=AM08");
            }
            $file_arr = FileModel::factory()->where('t1.message_id', $_GET['id'])
                ->findAll()
                ->getData();
            $this->set('arr', $arr);
            $this->set('file_arr', $file_arr);
            $this->appendJs('AdminMessages.js');
        } else {
            $this->set('status', 2);
        }
    }


    public function DeleteFile()
    {
        $this->setAjax(true);
        $FileModel = FileModel::factory();
        $arr = $FileModel->find($_POST['id'])->getData();
        $json_arr = array();
        if (! empty($arr)) {
            if ($FileModel->reset()
                ->setAttributes(array(
                'id' => $_POST['id']
            ))
                ->erase()
                ->getAffectedRows() == 1) {
                if (! empty($arr['file_path']) && is_file(INSTALL_PATH . $arr['file_path'])) {
                    @unlink(INSTALL_PATH . $arr['file_path']);
                }
                $json_arr['status'] = 1;
            } else {
                $json_arr['status'] = 0;
            }
        } else {
            $json_arr['status'] = 0;
        }
        AppController::jsonResponse($json_arr);
    }


    public function GetSubscribers()
    {
        $this->setAjax(true);
        $number_of_subscribers = SubscriberModel::factory()->where('subscribed', 'T')
            ->where("t1.id IN(SELECT t2.subscriber_id FROM `" . GroupSubscriberModel::factory()->getTable() . "` t2 WHERE t2.group_id IN(" . $_GET['group_id'] . "))")
            ->findCount()
            ->getData();
        echo $number_of_subscribers;
    }

    public function DownloadFile()
    {
        $id = $_GET['id'];
        $arr = FileModel::factory()->find($id)->getData();
        if (! empty($arr)) {
            ToolkitComponent::download(@file_get_contents(BASE_URL . $arr['file_path']), $arr['file_name'], $arr['mime_type']);
            exit();
        }
    }

    public function SendTest()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $response = array();
            $message_arr = MessageModel::factory()->find($_GET['id'])->getData();
            $file_arr = FileModel::factory()->where('t1.message_id', $_GET['id'])
                ->findAll()
                ->getData();
            if (! empty($_POST['email'])) {
                $message = $message_arr['tinymce_message'];
                $plain_message = $message_arr['plain_message'];
                AppController::sendMessage($_POST['email'], $message_arr['subject'], $message, $plain_message, $this->option_arr, $file_arr);
            }
            $response['code'] = 200;
            AppController::jsonResponse($response);
        }
        exit();
    }


    public function Duplicate()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $response = array();
            $MessageModel = MessageModel::factory();
            $FileModel = FileModel::factory();
            $message_arr = $MessageModel->find($_GET['id'])->getData();
            $file_arr = $FileModel->where('t1.message_id', $_GET['id'])
                ->findAll()
                ->getData();
            $message_arr['subject'] = $message_arr['subject'] . ' - ' . __('lblCopy', true);
            unset($message_arr['id']);
            unset($message_arr['modified']);
            $message_arr['created'] = date('Y-m-d H:i:s');
            $id = $MessageModel->reset()
                ->setAttributes($message_arr)
                ->insert()
                ->getInsertId();
            if ($id !== false && (int) $id > 0) {
                foreach ($file_arr as $file) {
                    $hash = md5(uniqid(rand(), true));
                    $file_path_arr = explode('.', $file['file_path']);
                    $ext = strtolower($file_path_arr[count($file_path_arr) - 1]);
                    $data = array();
                    $data['message_id'] = $id;
                    $data['mime_type'] = $file['mime_type'];
                    $data['file_path'] = UPLOAD_PATH . 'files/' . $id . "_" . $hash . '.' . $ext;
                    $data['file_name'] = $file['file_name'];
                    $data['hash'] = $hash;
                    $FileModel->reset()
                        ->setAttributes($data)
                        ->insert();
                    copy($file['file_path'], $data['file_path']);
                }
            }
            $response['code'] = 200;
            AppController::jsonResponse($response);
        }
        exit();
    }
}
?>