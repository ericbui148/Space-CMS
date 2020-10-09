<?php
namespace App\Controllers;

use App\Models\TagModel;
use App\Controllers\Components\UtilComponent;
use App\Plugins\Locale\Models\LocaleModel;
use App\Models\RouterModel;
use App\Models\ProductTagModel;
use App\Models\MultiLangModel;

class AdminTagsController extends AdminController
{
    const FRONTEND_TAG_CONTROLLER = 'Site';
    
    public function DeleteTag()
    {
        $this->setAjax(true);
        
        if ($this->isXHR() && $this->isLoged())
        {
            if (isset($_GET['id']) && (int) $_GET['id'] > 0 && TagModel::factory()->set('id', $_GET['id'])->erase()->getAffectedRows() == 1)
            {
                ProductTagModel::factory()->where('tag_id', $_GET['id'])->eraseAll();
                $this->deleteRouter(RouterModel::TYPE_TAG, self::FRONTEND_TAG_CONTROLLER, 'Tag', $_GET['id']);
                MultiLangModel::factory()->where ( 'model', 'Tag' )->where ( 'foreign_id', $_GET ['id'] )->eraseAll ();
                AppController::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => ''));
            }
            AppController::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => ''));
        }
        exit;
    }
    
    public function DeleteTagBulk()
    {
        $this->setAjax(true);
        
        if ($this->isXHR() && $this->isLoged())
        {
            if (isset($_POST['record']) && !empty($_POST['record']))
            {
                TagModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
                ProductTagModel::factory()->whereIn('tag_id', $_POST['record'])->eraseAll();
                MultiLangModel::factory()->where ( 'model', 'Tag' )->whereIn ( 'foreign_id', $_POST ['record'] )->eraseAll ();
                foreach ($_POST['record'] as $id) {
                    $this->deleteRouter(RouterModel::TYPE_TAG, self::FRONTEND_TAG_CONTROLLER, 'Tag', $id);
                }
                AppController::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => ''));
            }
            AppController::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => ''));
        }
        exit;
    }
    
    public function GetTag()
    {
        $this->setAjax(true);
        
        if ($this->isXHR() && $this->isLoged())
        {
            $TagModel = TagModel::factory()->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Tag' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'name'", 'left' );
            if (isset($_GET['q']) && !empty($_GET['q']))
            {
                $q = str_replace(array('_', '%'), array('\_', '\%'), trim($_GET['q']));
                $TagModel->where('t2.content LIKE', "%$q%");
            }
            
            if (isset($_GET['is_active']) && strlen($_GET['is_active']) > 0 && in_array($_GET['is_active'], array(1, 0)))
            {
                $TagModel->where('t1.status', $_GET['is_active']);
            }
            
            $column = 'id';
            $direction = 'DESC';
            
            $total = $TagModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages)
            {
                $page = $pages;
            }
            
            $data = $TagModel
            ->select('t1.*, t2.content as name')
            ->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
            
            AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
        }
        exit;
    }
    
    public function Index()
    {
        $this->checkLogin();
        $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
        $this->appendJs('AdminTags.js');
        $this->appendJs('index.php?controller=Admin&action=Messages', $this->baseUrl(), true);
    }
    public function Create()
    {
        $this->checkLogin();
        if (isset($_POST['tag_create']))
        {
            $data = array();
            $err = 'TAG0013';
            $id = TagModel::factory(array_merge($_POST, $data))->insert()->getInsertId();
            if (!empty($id)) {
                if (isset($_POST['i18n'])) {
                    MultiLangModel::factory()->saveMultiLang($_POST['i18n'], $id, 'Tag');
                    $locale_arr = LocaleModel::factory()->select('t1.*')
                    ->orderBy('t1.sort ASC')
                    ->findAll()
                    ->getData();
                    $uuidNumber = $this->generateRamdomNumber(6);
                    foreach ($locale_arr as $locale) {
                        $name = $_POST['i18n'][$locale['id']]['name'];
                        if (empty($name)) {
                            $name = $_POST['i18n'][1]['name'];
                        }
                        $friendlyUrl = $this->createFriendlyUrl(null, null, $name);
                        $friendlyUrl = $locale['language_iso'].'/tag/'.$friendlyUrl.'-'.$uuidNumber;
                        $this->createOrUpdateDynamicRouter(
                            $friendlyUrl,
                            RouterModel::TYPE_TAG,
                            self::FRONTEND_TAG_CONTROLLER,
                            "Tag",
                            "id",
                            $id,
                            $locale['id']);
                    }
                }
            }
            if($err == 'TAG0013' || $err == 'TAG0014')
            {
                UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminTags&action=Index&err=$err");
            }else{
                UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminTags&action=Update&id=$id&err=$err");
            }
        } else {
            $locale_arr = LocaleModel::factory()->select('t1.*, t2.file')
            ->join('LocaleLanguage', 't2.iso=t1.language_iso', 'left outer')
            ->where('t2.file IS NOT NULL')
            ->orderBy('t1.sort ASC')
            ->findAll()
            ->getData();
            $lp_arr = array();
            foreach ($locale_arr as $v) {
                $lp_arr[$v['id'] . "_"] = $v['file'];
            }
            $this->set('lp_arr', $locale_arr);
            $this->set('locale_str', AppController::jsonEncode($lp_arr));
            $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
            $this->appendJs('jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/');
            $this->appendJs('jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/');
            $this->appendCss('jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/');
            $this->appendJs('AdminTags.js');
        }
    }
    public function SaveTag()
    {
        $this->setAjax(true);
        
        if ($this->isXHR() && $this->isLoged())
        {
            $TagModel = TagModel::factory();
            $TagModel->set('id', $_GET['id'])->modify(array($_POST['column'] => $_POST['value']));
        }
        exit;
    }
    
    public function Update()
    {
        $this->checkLogin();
            
        if (isset($_POST['tag_update']) && isset($_POST['id']) && (int) $_POST['id'] > 0)
        {
            $err = 'TAG06';
            $data = array();
            $postData = $_POST;
            
            TagModel::factory()->set('id', $postData['id'])->modify(array_merge($postData, $data));
            if (isset($_POST['i18n'])) {
                MultiLangModel::factory()->updateMultiLang($_POST['i18n'], $_POST['id'], 'Tag');
                $locale_arr = LocaleModel::factory()->select('t1.*')
                ->orderBy('t1.sort ASC')
                ->findAll()
                ->getData();
                $uuidNumber = $this->generateRamdomNumber(6);
                foreach ($locale_arr as $locale) {
                    $name = $_POST['i18n'][$locale['id']]['name'];
                    if (empty($name)) {
                        $name = $_POST['i18n'][1]['name'];
                    }
                    if (empty($_POST['url'])) {
                        $friendlyUrl = $this->createFriendlyUrl(null, null, $name);
                        $friendlyUrl = $locale['language_iso'].'/tag/'.$friendlyUrl.'-'.$uuidNumber;
                    } else {
                        $friendlyUrl = $_POST['url'];
                    }
                    
                    $this->createOrUpdateDynamicRouter(
                        $friendlyUrl,
                        RouterModel::TYPE_TAG,
                        self::FRONTEND_TAG_CONTROLLER,
                        "Tag",
                        "id",
                        $_POST['id'],
                        $locale['id']);
                }
            }
            
            if($err == 'TAG06')
            {
                UtilComponent::redirect($this->baseUrl() . "index.php?controller=AdminTags&action=Index&err=CON06");
            }else{
                UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminTags&action=Update&id=".$postData['id']."&err=$err");
            }
            
        } else {
            $arr = TagModel::factory()->find($_GET['id'])->getData();
            
            if (empty($arr))
            {
                UtilComponent::redirect($this->baseUrl(). "index.php?controller=AdminTags&action=Index&err=ADEP07");
            }
            $arr['i18n'] = MultiLangModel::factory()->getMultiLang($arr['id'], 'Tag');
            $routers = RouterModel::factory()
            ->where('t1.type', RouterModel::TYPE_TAG)
            ->where('t1.controller', self::FRONTEND_TAG_CONTROLLER)
            ->where('t1.action', "Tag")
            ->where('t1.foreign_id', $_GET['id'])
            ->findAll()
            ->getData();
            if (!empty($routers)) {
                foreach ($routers as $router) {
                    $arr['i18n'][$router['locale_id']]['url'] = $router['url'];
                }
            }
            $this->set('arr', $arr);
            $locale_arr = LocaleModel::factory()->select('t1.*, t2.file')
            ->join('LocaleLanguage', 't2.iso=t1.language_iso', 'left outer')
            ->where('t2.file IS NOT NULL')
            ->orderBy('t1.sort ASC')
            ->findAll()
            ->getData();
            $lp_arr = array();
            foreach ($locale_arr as $v) {
                $lp_arr[$v['id'] . "_"] = $v['file'];
            }
            $this->set('lp_arr', $locale_arr);
            $this->set('locale_str', AppController::jsonEncode($lp_arr));
            $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
            $this->appendJs('jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/');
            $this->appendJs('jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/');
            $this->appendCss('jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/');
            $this->appendJs('AdminTags.js');
        }
    }
    
}
?>