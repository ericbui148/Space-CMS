<?php
namespace App\Controllers;

use App\Controllers\Components\UtilComponent;
use App\Models\CategoryModel;
use App\Models\ProductCategoryModel;
use App\Models\MultiLangModel;
use App\Plugins\Locale\Models\LocaleModel;
use App\Models\RouterModel;
use App\Models\ProductModel;
use App\Models\ItemSortModel;

class AdminCategoriesController extends AdminController
{
    const FRONTEND_CATEGORY_CONTROLLER = 'ShopCart';
    public function Create()
    {
        $this->checkLogin();
        $this->title = "Danh mục sản phẩm - Thêm danh mục";
        if ($this->isAdmin()) {
            if (isset($_POST['category_create'])) {
                $id = CategoryModel::factory()->saveNode($_POST, $_POST['parent_id']);
                if ($id !== false && (int) $id > 0) {
                    $err = 'AG01';
                    if (isset($_POST['i18n'])) {
                        MultiLangModel::factory()->saveMultiLang($_POST['i18n'], $id, 'Category');
                        $locale_arr = LocaleModel::factory()->select('t1.*')
                        ->orderBy('t1.sort ASC')
                        ->findAll()
                        ->getData();
                        $uuidNumber = $this->generateRamdomNumber(6);
                        foreach ($locale_arr as $locale) {
                            $category_arr = CategoryModel::factory ()->getNode ( $locale['id'], 1);
                            $pc_arr = [$id];
                            $friendlyUrl = $this->createFriendlyUrl($category_arr, $pc_arr, null);
                            $friendlyUrl = $locale['language_iso'].'/'.$friendlyUrl.'-'.$uuidNumber;
                            $this->createOrUpdateDynamicRouter(
                                $friendlyUrl,
                                RouterModel::TYPE_PRODUCT_CATEGORY,
                                self::FRONTEND_CATEGORY_CONTROLLER,
                                "Products",
                                "category_id",
                                $id, 
                                $locale['id']);
                        }
                    }
                    $this->createItemSorts($id); 
                } else {
                    $err = 'AG02';
                }
                UtilComponent::redirect(sprintf("%s?controller=AdminCategories&action=Index&err=%s", $_SERVER['PHP_SELF'], $err));
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
                $this->set('node_arr', CategoryModel::factory()->getNode($this->getLocaleId(), 1));
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . '/validate/');
                $this->appendJs('jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/');
                $this->appendJs('jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/');
                $this->appendCss('jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/');
                $this->appendJs ( 'tinymce.min.js', THIRD_PARTY_PATH . 'tiny_mce/' );
                $this->appendJs('AdminCategories.js');
                $this->appendJs('index.php?controller=Admin&action=Messages', BASE_URL, true);
            }
        } else {
            $this->set('status', 2);
        }
    }
    
    protected function createItemSorts($categoryId)
    {
        $ProductModel = ProductModel::factory()->select('id')
        ->where ( 'status', ProductModel::STATUS_ACTIVE )
        ->where('t1.is_featured', 1)->orderBy ( '`id` DESC' );
        $ProductModel->where(sprintf("t1.id IN (SELECT `product_id` FROM `%s` WHERE `category_id` = '%u')", ProductCategoryModel::factory()->getTable(), (int) $categoryId));
        $productArr = $ProductModel->findAll()->getData();
        if (!empty($productArr)) {
            $batchValues = [];
            $sort = 1;
            foreach ($productArr as $product) {
                $batchValues[] = [
                    $product['id'],
                    ItemSortModel::TYPE_PRODUCT_CATEGORY,
                    $categoryId,
                    $sort
                ];
                $sort++;
            }
            ItemSortModel::factory()->setBatchFields([
                'foreign_id', 'type', 'foreign_type_id', 'sort'
            ])->setBatchRows($batchValues)->insertBatch()->getAffectedRows();
        }
    }
    
    protected function needCreateSort($categoryId)
    {
        $record = ItemSortModel::factory()->where('foreign_type_id', $categoryId)->where('type', ItemSortModel::TYPE_PRODUCT_CATEGORY)->limit(1)->findAll()->first();
        return empty($record);
    }
    
    public function DeleteCategory()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $response = array();
            $CategoryModel = CategoryModel::factory();
            $CategoryModel->deleteNode($_GET['id']);
            $CategoryModel->rebuildTree(1, 1);
            ProductCategoryModel::factory()->where('category_id', $_GET['id'])->eraseAll();
            $this->deleteRouter(RouterModel::TYPE_PRODUCT_CATEGORY, self::FRONTEND_CATEGORY_CONTROLLER, 'Products', $_GET['id']);
            $response['code'] = 200;
            AppController::jsonResponse($response);
        }
        exit();
    }

    public function DeleteCategoryBulk()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_POST['record']) && count($_POST['record']) > 0) {
                $CategoryModel = CategoryModel::factory();
                $CategoryModel->whereIn('id', $_POST['record'])->eraseAll();
                foreach ($_POST['record'] as $id) {
                    $CategoryModel->deleteNode($id);
                    $CategoryModel->rebuildTree(1, 1);
                    $this->deleteRouter(RouterModel::TYPE_PRODUCT_CATEGORY, self::FRONTEND_CATEGORY_CONTROLLER, 'Products', $id);
                }
                ProductCategoryModel::factory()->whereIn('category_id', $_POST['record'])->eraseAll();
            }
        }
        exit();
    }


    public function GetCategory()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $CategoryModel = CategoryModel::factory();
            $column = 'name';
            $direction = 'ASC';
            if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                'ASC',
                'DESC'
            ))) {
                $column = $_GET['column'];
                $direction = strtoupper($_GET['direction']);
            }
            $data = $CategoryModel->getNode($this->getLocaleId(), 1);
            $total = count($data);
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $c_arr = $CategoryModel->reset()
                ->select(sprintf("t1.id, (SELECT COUNT(*) FROM `%s` WHERE `category_id` = `t1`.`id` LIMIT 1) AS `products`", ProductCategoryModel::factory()->getTable()))
                ->findAll()
                ->getDataPair('id', 'products');
            $data = array_slice($data, $offset, $rowCount);
            $stack = array();
            foreach ($data as $k => $category) {
                $data[$k]['products'] = (int) @$c_arr[$category['data']['id']];
                $data[$k]['up'] = 0;
                $data[$k]['down'] = 0;
                $data[$k]['id'] = (int) $category['data']['id'];
                if (! isset($stack[$category['deep'] . "|" . $category['data']['parent_id']])) {
                    $stack[$category['deep'] . "|" . $category['data']['parent_id']] = 0;
                }
                $stack[$category['deep'] . "|" . $category['data']['parent_id']] += 1;
                if ($stack[$category['deep'] . "|" . $category['data']['parent_id']] > 1) {
                    $data[$k]['up'] = 1;
                }
                if (isset($data[$k + 1]) && $data[$k + 1]['deep'] == $category['deep'] || $stack[$category['deep'] . "|" . $category['data']['parent_id']] < $category['siblings']) {
                    $data[$k]['down'] = 1;
                }
            }
            AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
        }
        exit();
    }


    public function Index()
    {
        $this->checkLogin();
        $this->title = "Danh mục sản phẩm - Danh sách danh mục";
        if ($this->isAdmin()) {
            $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
            $this->appendJs('AdminCategories.js');
            $this->appendJs('index.php?controller=Admin&action=Messages', BASE_URL, true);
        } else {
            $this->set('status', 2);
        }
    }



    public function SetOrder()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            $CategoryModel = CategoryModel::factory();
            $node = $CategoryModel->find($_POST['id'])->getData();
            if (count($node) > 0) {
                $CategoryModel->reset();
                $opts = array();
                switch ($_POST['direction']) {
                    case 'up':
                        $CategoryModel->where('t1.lft <', $node['lft'])->orderBy('t1.lft DESC');
                        break;
                    case 'down':
                        $CategoryModel->where('t1.lft >', $node['lft'])->orderBy('t1.lft ASC');
                        break;
                }
                $neighbour = $CategoryModel->where('t1.id !=', $node['id'])
                    ->where('t1.parent_id', $node['parent_id'])
                    ->limit(1)
                    ->findAll()
                    ->getData();
                if (count($neighbour) === 1) {
                    $neighbour = $neighbour[0];
                    $CategoryModel->reset()
                        ->set('id', $neighbour['id'])
                        ->modify(array(
                        'lft' => $node['lft'],
                        'rgt' => $node['rgt']
                    ));
                    $CategoryModel->reset()
                        ->set('id', $node['id'])
                        ->modify(array(
                        'lft' => $neighbour['lft'],
                        'rgt' => $neighbour['rgt']
                    ));
                    $CategoryModel->reset()->rebuildTree(1, 1);
                } else {}
            }
        }
        exit();
    }


    public function Update()
    {
        $this->checkLogin();
        $this->title = "Danh mục sản phẩm - Cập nhật danh mục";
        if ($this->isAdmin()) {
            $CategoryModel = CategoryModel::factory();
            if (isset($_POST['category_update'])) {
                $data = array();
                $CategoryModel->updateNode(array_merge($_POST, $data));
                if (isset($_POST['i18n'])) {
                    MultiLangModel::factory()->updateMultiLang($_POST['i18n'], $_POST['id'], 'Category');
                    $locale_arr = LocaleModel::factory()->select('t1.*')
                    ->orderBy('t1.sort ASC')
                    ->findAll()
                    ->getData();
                    $uuidNumber = $this->generateRamdomNumber(6);
                    foreach ($locale_arr as $locale) {
                        $category_arr = CategoryModel::factory ()->getNode ( $locale['id'], 1);
                        $pc_arr = [$_POST['id']];
                        if (empty($_POST['i18n'][$locale['id']]['url'])) {
                            $friendlyUrl = $this->createFriendlyUrl($category_arr, $pc_arr, null);
                            $friendlyUrl = $locale['language_iso'].'/'.$friendlyUrl.'-'.$uuidNumber;
                        } else {
                            $friendlyUrl = $_POST['i18n'][$locale['id']]['url'];
                        }

                        $this->createOrUpdateDynamicRouter(
                            $friendlyUrl,
                            RouterModel::TYPE_PRODUCT_CATEGORY,
                            self::FRONTEND_CATEGORY_CONTROLLER,
                            "Products",
                            "category_id",
                            $_POST['id'],
                            $locale['id']);
                    }
                }
                if ($this->needCreateSort($_POST['id'])) {
                    $this->createItemSorts($_POST['id']);
                }
                
                $err = 'AG05';
                UtilComponent::redirect(sprintf("%s?controller=AdminCategories&action=Index&err=%s", $_SERVER['PHP_SELF'], $err));
            } else {
                $arr = $CategoryModel->find($_GET['id'])->getData();
                if (count($arr) === 0) {
                    UtilComponent::redirect(sprintf("%s?controller=AdminCategories&action=Index&err=%s", $_SERVER['PHP_SELF'], 'AG08'));
                }
                $arr['i18n'] = MultiLangModel::factory()->getMultiLang($arr['id'], 'Category');
                $routers = RouterModel::factory()
                ->where('t1.type', RouterModel::TYPE_PRODUCT_CATEGORY)
                ->where('t1.controller', self::FRONTEND_CATEGORY_CONTROLLER)
                ->where('t1.action', "Products")
                ->where('t1.foreign_id', $_GET['id'])
                ->findAll()
                ->getData();
                if (!empty($routers)) {
                    foreach ($routers as $router) {
                        $arr['i18n'][$router['locale_id']]['url'] = $router['url'];
                    }
                }
                $this->set('arr', $arr);
                $this->set('node_arr', $CategoryModel->reset()
                    ->getNode($this->getLocaleId(), 1));
                $this->set('child_arr', $CategoryModel->reset()
                    ->getNode($this->getLocaleId(), $arr['id']));
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
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . '/validate/');
                $this->appendJs('jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/');
                $this->appendJs('jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/');
                $this->appendCss('jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/');
                $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
                $this->appendJs ( 'tinymce.min.js', THIRD_PARTY_PATH . 'tiny_mce/' );
                $this->appendJs('AdminCategories.js');
                $this->appendJs('index.php?controller=Admin&action=Messages', BASE_URL, true);
            }
        } else {
            $this->set('status', 2);
        }
    }
}
?>