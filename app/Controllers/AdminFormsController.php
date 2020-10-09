<?php
namespace App\Controllers;

use App\Models\FormModel;
use App\Models\UserModel;
use App\Models\SubmissionModel;
use App\Models\UserFormModel;
use App\Controllers\Components\UtilComponent;
use App\Models\FileModel;
use App\Models\FormFieldModel;
use App\Models\SubmissionDetailModel;
use Core\Framework\Components\CSVComponent;

class AdminFormsController extends AdminController
{

    public function Index()
    {
        $this->checkLogin();
        if ($this->isAdmin() || $this->isEditor()) {
            $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
            $this->appendJs('AdminForms.js');
            $this->appendJs('index.php?controller=Admin&action=Messages', BASE_URL, true);
        } else {
            $this->set('status', 2);
        }
    }


    public function GetForm()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $FormModel = FormModel::factory();
            if (isset($_GET['q']) && ! empty($_GET['q'])) {
                $q = Object::escapeString($_GET['q']);
                $FormModel->where('t1.form_title LIKE', "%$q%");
            }
            if (isset($_GET['status']) && ! empty($_GET['status']) && in_array($_GET['status'], array(
                'T',
                'F'
            ))) {
                $FormModel->where('t1.status', $_GET['status']);
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
            $total = $FormModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $data = $FormModel->select("t1.*,  (SELECT COUNT(*) FROM `" . SubmissionModel::factory()->getTable() . "` AS t2 WHERE t2.form_id = t1.id) as cnt_submissions,  (SELECT CONCAT(submitted_date, '~:~', t3.id) FROM `" . SubmissionModel::factory()->getTable() . "` AS t3 WHERE t3.form_id=t1.id ORDER BY submitted_date DESC LIMIT 1) AS date_time")
                ->orderBy("$column $direction")
                ->limit($rowCount, $offset)
                ->findAll()
                ->getData();
            foreach ($data as $k => $v) {
                if (! empty($v['date_time'])) {
                    list ($date_time, $submission_id) = explode("~:~", $v['date_time']);
                    $v['date_time'] = date($this->option_arr['o_date_format'], strtotime($date_time)) . ', ' . date($this->option_arr['o_time_format'], strtotime($date_time));
                    $v['submission_id'] = $submission_id;
                }
                $data[$k] = $v;
            }
            AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
        }
        exit();
    }

    public function Create()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            if (isset($_POST['form_create'])) {
                $data = array();
                $data['subject'] = 'Form submission';
                $data['confirm_message'] = 'Thank you!';
                $id = FormModel::factory(array_merge($_POST, $data))->insert()->getInsertId();
                if ($id !== false && (int) $id > 0) {
                    UserFormModel::factory()->setAttributes(array(
                        'form_id' => $id,
                        'user_id' => '1'
                    ))->insert();
                    $err = 'AF03';
                } else {
                    $err = 'AF04';
                }
                UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminForms&action=Update&id=" . $id . "&tab_id=tabs-1" . "&err=$err");
            } else {
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('AdminForms.js');
            }
        } else {
            $this->set('status', 2);
        }
    }

    public function DeleteForm()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $response = array();
            if (FormModel::factory()->setAttributes(array(
                'id' => $_GET['id']
            ))
                ->erase()
                ->getAffectedRows() == 1) {
                $FileModel = FileModel::factory();
                $file_arr = $FileModel->where('form_id', $_GET['id'])
                    ->findAll()
                    ->getData();
                foreach ($file_arr as $file) {
                    $file_path = $file['file_path'];
                    if (file_exists(INSTALL_PATH . $file_path)) {
                        if (unlink(INSTALL_PATH . $file_path)) {}
                    }
                }
                FormFieldModel::factory()->where('form_id', $_GET['id'])->eraseAll();
                $FileModel->reset()
                    ->where('form_id', $_GET['id'])
                    ->eraseAll();
                SubmissionModel::factory()->where('form_id', $_GET['id'])->eraseAll();
                SubmissionDetailModel::factory()->where('form_id', $_GET['id'])->eraseAll();
                $response['code'] = 200;
            } else {
                $response['code'] = 100;
            }
            AppController::jsonResponse($response);
        }
        exit();
    }


    public function DeleteFormBulk()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_POST['record']) && count($_POST['record']) > 0) {
                $FileModel = FileModel::factory();
                $file_arr = $FileModel->reset()
                    ->whereIn('form_id', $_POST['record'])
                    ->findAll()
                    ->getData();
                foreach ($file_arr as $file) {
                    $file_path = $file['file_path'];
                    if (file_exists(INSTALL_PATH . $file_path)) {
                        if (unlink(INSTALL_PATH . $file_path)) {}
                    }
                }
                FormModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
                FormFieldModel::factory()->whereIn('form_id', $_POST['record'])->eraseAll();
                $FileModel->reset()
                    ->whereIn('form_id', $_POST['record'])
                    ->eraseAll();
                SubmissionModel::factory()->where('form_id', $_GET['id'])->eraseAll();
                SubmissionDetailModel::factory()->where('form_id', $_GET['id'])->eraseAll();
            }
        }
        exit();
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

    public function ExportForm()
    {
        $this->checkLogin();
        if (isset($_POST['record']) && is_array($_POST['record'])) {
            $arr = FormModel::factory()->whereIn('id', $_POST['record'])
                ->findAll()
                ->getData();
            $csv = new CSVComponent();
            $csv->setHeader(true)
                ->setName("Forms-" . time() . ".csv")
                ->process($arr)
                ->download();
        }
        exit();
    }

    public function SetActive()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $FormModel = FormModel::factory();
            $arr = $FormModel->find($_POST['id'])->getData();
            if (count($arr) > 0) {
                switch ($arr['is_active']) {
                    case 'T':
                        $sql_status = 'F';
                        break;
                    case 'F':
                        $sql_status = 'T';
                        break;
                    default:
                        return;
                }
                $FormModel->reset()
                    ->setAttributes(array(
                    'id' => $_POST['id']
                ))
                    ->modify(array(
                    'is_active' => $sql_status
                ));
            }
        }
        exit();
    }

    public function SaveForm()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            FormModel::factory()->where('id', $_GET['id'])
                ->limit(1)
                ->modifyAll(array(
                $_POST['column'] => $_POST['value']
            ));
        }
        exit();
    }


    public function StatusForm()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_POST['record']) && count($_POST['record']) > 0) {
                FormModel::factory()->whereIn('id', $_POST['record'])->modifyAll(array(
                    'status' => ":IF(`status`='F','T','F')"
                ));
            }
        }
        exit();
    }

    public function Update()
    {
        $this->checkLogin();
        if ($this->isAdmin() || $this->isEditor()) {
            if (isset($_POST['form_update'])) {
                $data = array();
                FormModel::factory()->where('id', $_POST['id'])
                    ->limit(1)
                    ->modifyAll(array_merge($_POST, $data));
                $UserFormModel = UserFormModel::factory();
                $UserFormModel->where('form_id', $_POST['id'])->eraseAll();
                if (isset($_POST['user_id'])) {
                    $UserFormModel->reset()->begin();
                    foreach ($_POST['user_id'] as $user_id) {
                        $data = array();
                        $data['form_id'] = $_POST['id'];
                        $data['user_id'] = $user_id;
                        $UserFormModel->reset()
                            ->setAttributes($data)
                            ->insert();
                    }
                    $UserFormModel->commit();
                }
                UtilComponent::redirect(BASE_URL . "index.php?controller=AdminForms&action=Update&id=" . $_POST['id'] . "&tab_id=" . $_POST['tab_id'] . "&err=AF01");
            } else {
                $FormFieldModel = FormFieldModel::factory();
                $arr = FormModel::factory()->find($_GET['id'])->getData();
                if (count($arr) === 0) {
                    UtilComponent::redirect(BASE_URL . "index.php?controller=AdminForms&action=Index&err=AF08");
                }
                $field_arr = $FormFieldModel->where('form_id', $_GET['id'])
                    ->orderBy("order_id ASC")
                    ->findAll()
                    ->getData();
                $cnt_captcha = $FormFieldModel->reset()
                    ->where('form_id', $_GET['id'])
                    ->where('type', 'captcha')
                    ->findCount()
                    ->getData();
                $user_arr = UserModel::factory()->where('t1.status', 'T')
                    ->findAll()
                    ->getData();
                $user_form_arr = UserFormModel::factory()->where('form_id', $_GET['id'])
                    ->findAll()
                    ->getData();
                $user_id_arr = array();
                if (! empty($user_form_arr)) {
                    foreach ($user_form_arr as $v) {
                        $user_id_arr[] = $v['user_id'];
                    }
                }
                $this->set('arr', $arr);
                $this->set('field_arr', $field_arr);
                $this->set('cnt_captcha', $cnt_captcha);
                $this->set('user_arr', $user_arr);
                $this->set('user_id_arr', $user_id_arr);
                $this->appendJs('jquery.miniColors.min.js', THIRD_PARTY_PATH . 'miniColors/');
                $this->appendCss('jquery.miniColors.css', THIRD_PARTY_PATH . 'miniColors/');
                $this->appendJs('jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/');
                $this->appendCss('jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/');
                $this->appendJs('chosen.jquery.min.js', THIRD_PARTY_PATH . 'harvest/chosen/');
                $this->appendCss('chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
                $this->appendJs('tinymce.min.js', THIRD_PARTY_PATH . 'tiny_mce/');
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
                $this->appendJs('index.php?controller=Admin&action=Messages', BASE_URL, true);
                $this->appendJs('AdminForms.js');
            }
        } else {
            $this->set('status', 2);
        }
    }

    public function LoadForm()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $field_arr = FormFieldModel::factory()->where('form_id', $_GET['id'])
                ->orderBy("order_id ASC")
                ->findAll()
                ->getData();
            $this->set('field_arr', $field_arr);
        }
    }

    public function SortFields()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $response = array();
            $FormFieldModel = FormFieldModel::factory();
            foreach ($_POST['field_item'] as $k => $v) {
                $FormFieldModel->reset()
                    ->set('id', $v)
                    ->modify(array(
                    'order_id' => $k + 1
                ));
            }
            $response['code'] = 200;
            AppController::jsonResponse($response);
        }
        exit();
    }


    public function AddField()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $FormFieldModel = FormFieldModel::factory();
            $field_arr = $FormFieldModel->reset()
                ->where('form_id', $_GET['id'])
                ->limit(1)
                ->orderBy("order_id DESC")
                ->findAll()
                ->getData();
            $order_id = 1;
            if (! empty($field_arr)) {
                $order_id = $field_arr[0]['order_id'] + 1;
            }
            $data = array();
            $data['form_id'] = $_GET['id'];
            $data['type'] = $_GET['type'];
            $data['order_id'] = $order_id;
            $data['error_required'] = __('lblRequiredField', true, false);
            if ($_GET['type'] == 'heading') {
                $data['label'] = __('lblMediumHeading', true, false);
            } else {
                $data['label'] = __('lblFieldLabel', true, false);
            }
            if ($_GET['type'] == 'fileupload') {
                $data['extensions'] = __('lblDefaultExtensions', true, false);
            }
            $field_id = null;
            if ($_GET['type'] != 'captcha') {
                $field_id = $FormFieldModel->reset()
                    ->setAttributes($data)
                    ->insert()
                    ->getInsertId();
            } else {
                $cnt_captcha = $FormFieldModel->reset()
                    ->where('form_id', $_GET['id'])
                    ->where('type', 'captcha')
                    ->findCount()
                    ->getData();
                if ($cnt_captcha == 0) {
                    $field_id = $FormFieldModel->reset()
                        ->setAttributes($data)
                        ->insert()
                        ->getInsertId();
                }
            }
            $field_arr = $FormFieldModel->reset()
                ->where('form_id', $_GET['id'])
                ->orderBy("order_id ASC")
                ->findAll()
                ->getData();
            $arr = FormModel::factory()->find($_GET['id'])->getData();
            $this->set('field_arr', $field_arr);
            $this->set('field_id', $field_id);
            $this->set('arr', $arr);
        }
    }


    public function DeleteField()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $FormFieldModel = FormFieldModel::factory();
            $_arr = $FormFieldModel->find($_GET['field_id'])->getData();
            $FormFieldModel->reset()
                ->setAttributes(array(
                'id' => $_GET['field_id']
            ))
                ->erase();
            $field_arr = $FormFieldModel->reset()
                ->where('form_id', $_GET['id'])
                ->orderBy("order_id ASC")
                ->findAll()
                ->getData();
            $arr = FormModel::factory()->find($_arr['form_id'])->getData();
            $this->set('field_arr', $field_arr);
            $this->set('arr', $arr);
        }
    }


    public function EditField()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $field_arr = FormFieldModel::factory()->find($_GET['id'])->getData();
            $arr = FormModel::factory()->find($field_arr['form_id'])->getData();
            $this->set('field_arr', $field_arr);
            $this->set('arr', $arr);
        }
    }

    public function SaveField()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $FormFieldModel = FormFieldModel::factory();
            $data = array();
            $arr = $FormFieldModel->find($_POST['field_id'])->getData();
            if ($arr['type'] == 'radio' || $arr['type'] == 'checkbox' || $arr['type'] == 'dropdown') {
                $option = array();
                $option_arr = explode("\n", str_replace("\r", "", $_POST['option_data']));
                foreach ($option_arr as $k => $v) {
                    $string = preg_replace('/\s+/', '', $v);
                    if ($string != '') {
                        $option[] = $v;
                    }
                }
                $_POST['option_data'] = implode("\n", $option);
            }
            $FormFieldModel->reset()
                ->set('id', $_POST['field_id'])
                ->modify($_POST);
            $field_arr = $FormFieldModel->reset()
                ->where('form_id', $_GET['id'])
                ->orderBy("order_id ASC")
                ->findAll()
                ->getData();
            $arr = FormModel::factory()->find($_GET['id'])->getData();
            $this->set('arr', $arr);
            $this->set('field_arr', $field_arr);
        }
    }


    public function Preview()
    {
        $this->setLayout('Empty');
        $arr = FormModel::factory()->find($_GET['id'])->getData();
        $field_arr = FormFieldModel::factory()->where('form_id', $_GET['id'])
            ->orderBy("order_id ASC")
            ->findAll()
            ->getData();
        $this->set('arr', $arr);
        $this->set('field_arr', $field_arr);
    }

    public function Code()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $arr = FormModel::factory()->find($_GET['id'])->getData();
            $field_arr = FormFieldModel::factory()->where('form_id', $_GET['id'])
                ->orderBy("order_id ASC")
                ->findAll()
                ->getData();
            $this->set('arr', $arr);
            $this->set('field_arr', $field_arr);
        }
    }
}
?>