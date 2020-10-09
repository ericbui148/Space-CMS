<?php
namespace App\Controllers;

use App\Models\FormModel;
use App\Models\SubmissionModel;
use App\Models\FileModel;
use App\Models\SubmissionDetailModel;

class AdminSubmissionsController extends AdminController
{

    public function Index()
    {
        $this->checkLogin();
        if ($this->isAdmin() || $this->isEditor()) {
            $form_arr = FormModel::factory()->where('t1.status', 'T')
                ->orderBy("form_title ASC")
                ->findAll()
                ->getData();
            $this->set('form_arr', $form_arr);
            $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
            $this->appendJs('AdminSubmissions.js');
            $this->appendJs('index.php?controller=Admin&action=Messages', BASE_URL, true);
        } else {
            $this->set('status', 2);
        }
    }

    public function DeleteSubmission()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $response = array();
            if (SubmissionModel::factory()->setAttributes(array(
                'id' => $_GET['id']
            ))
                ->erase()
                ->getAffectedRows() == 1) {
                $FileModel = FileModel::factory();
                $file_arr = $FileModel->reset()
                    ->where('submission_id', $_GET['id'])
                    ->findAll()
                    ->getData();
                foreach ($file_arr as $file) {
                    $file_path = $file['file_path'];
                    if (file_exists(INSTALL_PATH . $file_path)) {
                        if (unlink(INSTALL_PATH . $file_path)) {}
                    }
                }
                SubmissionDetailModel::factory()->where('submission_id', $_GET['id'])->eraseAll();
                $FileModel->reset()
                    ->where('submission_id', $_GET['id'])
                    ->eraseAll();
                $response['code'] = 200;
            } else {
                $response['code'] = 100;
            }
            AppController::jsonResponse($response);
        }
        exit();
    }
    
    public function DeleteSubmissionBulk()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_POST['record']) && count($_POST['record']) > 0) {
                $FileModel = FileModel::factory();
                $file_arr = $FileModel->reset()
                    ->whereIn('submission_id', $_POST['record'])
                    ->findAll()
                    ->getData();
                foreach ($file_arr as $file) {
                    $file_path = $file['file_path'];
                    if (file_exists(INSTALL_PATH . $file_path)) {
                        if (unlink(INSTALL_PATH . $file_path)) {}
                    }
                }
                SubmissionModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
                SubmissionDetailModel::factory()->whereIn('submission_id', $_POST['record'])->eraseAll();
                $FileModel->reset()
                    ->whereIn('submission_id', $_POST['record'])
                    ->eraseAll();
            }
        }
        exit();
    }

    public function GetSubmissions()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $SubmissionModel = SubmissionModel::factory()->join("Form", "t1.form_id=t2.id", "left");
            if (isset($_GET['form_id']) && (int) $_GET['form_id'] > 0) {
                $SubmissionModel->where('t1.form_id', $_GET['form_id']);
            }
            if (isset($_GET['q']) && ! empty($_GET['q'])) {
                $q = Object::escapeString($_GET['q']);
                $SubmissionModel->where("t1.id IN (SELECT t3.submission_id FROM `" . SubmissionDetailModel::factory()->getTable() . "` AS t3 WHERE t3.value LIKE '%" . $q . "%')");
            }
            $column = 'submitted_date';
            $direction = 'DESC';
            if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                'ASC',
                'DESC'
            ))) {
                $column = $_GET['column'];
                $direction = strtoupper($_GET['direction']);
            }
            $total = $SubmissionModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $data = array();
            $data = $SubmissionModel->select("t1.*, t2.form_title")
                ->orderBy("$column $direction")
                ->limit($rowCount, $offset)
                ->findAll()
                ->getData();
            foreach ($data as $k => $v) {
                $v['submitted_date'] = date($this->option_arr['o_date_format'], strtotime($v['submitted_date'])) . ', ' . date($this->option_arr['o_time_format'], strtotime($v['submitted_date']));
                $data[$k] = $v;
            }
            AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
        }
        exit();
    }

    public function View()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $submission_arr = SubmissionModel::factory()->find($_GET['submission_id'])->getData();
            $arr = FormModel::factory()->find($submission_arr['form_id'])->getData();
            $field_arr = SubmissionDetailModel::factory()->select("t1.*, t2.label, t2.heading_size")
                ->join('FormField', "t1.field_id = t2.id", 'left')
                ->where('submission_id', $_GET['submission_id'])
                ->orderBy("t2.order_id ASC")
                ->findAll()
                ->getData();
            $file_arr = array();
            $files = FileModel::factory()->where('form_id', $submission_arr['form_id'])
                ->where('submission_id', $_GET['submission_id'])
                ->findAll()
                ->getData();
            foreach ($files as $k => $v) {
                $file_arr[$v['field_id']][] = $v;
            }
            $this->set('arr', $arr);
            $this->set('submission_arr', $submission_arr);
            $this->set('field_arr', $field_arr);
            $this->set('file_arr', $file_arr);
        }
    }

    public function Print()
    {
        $this->setLayout('Print');
        $submission_arr = SubmissionModel::factory()->find($_GET['submission_id'])->getData();
        $arr = FormModel::factory()->find($submission_arr['form_id'])->getData();
        $field_arr = SubmissionDetailModel::factory()->select("t1.*, t2.label, t2.heading_size")
            ->join('FormField', "t1.field_id = t2.id", 'left')
            ->where('submission_id', $_GET['submission_id'])
            ->orderBy("t2.order_id ASC")
            ->findAll()
            ->getData();
        $file_arr = array();
        $files = FileModel::factory()->where('form_id', $submission_arr['form_id'])
            ->where('submission_id', $_GET['submission_id'])
            ->findAll()
            ->getData();
        foreach ($files as $k => $v) {
            $file_arr[$v['field_id']][] = $v;
        }
        $this->set('arr', $arr);
        $this->set('field_arr', $field_arr);
        $this->set('file_arr', $file_arr);
    }
}
?>