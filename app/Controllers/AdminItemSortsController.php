<?php
namespace App\Controllers;

use App\Models\ItemSortModel;
use App\Controllers\Components\UtilComponent;
use App\Models\StockModel;
use App\Models\ProductCategoryModel;
use App\Plugins\Gallery\Models\GalleryModel;
use App\Models\OrderStockModel;
use App\Models\ProductModel;
use App\Models\ArticleModel;
use Core\Framework\Objects;
use App\Models\ACategoryArticleModel;
use App\Models\MultiLangModel;
use App\Models\PageModel;
use App\Models\PCategoryPageModel;

class AdminItemSortsController extends AdminController
{
    
    public function GetItemSorts()
    {
        $this->setAjax ( true );
        if ($this->isXHR ()) {
            $foreignTypeId = @$_GET['foreign_type_id'];
            $type = @$_GET['type'];
            if ($type == ItemSortModel::TYPE_WIDGET_PRODUCTS || $type == ItemSortModel::TYPE_PRODUCT_CATEGORY) {
                $this->getProducts($foreignTypeId, $type);
            } elseif ($type == ItemSortModel::TYPE_WDGET_PAGES || $type == ItemSortModel::TYPE_PAGE_CATEGORY) {
                $this->getPages($foreignTypeId, $type);
            } elseif ($type == ItemSortModel::TYPE_WIDGET_ARTICLES || $type == ItemSortModel::TYPE_ARTICLE_CATEGORY) {
                $this->getArticles($foreignTypeId, $type);
            }
        }
        exit ();
    }
    
    public function SetOrder()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if ($_POST['direction'] == 'up') {
                $id = $_POST['id'];
                $itemSort = ItemSortModel::factory()->find($id)->getData();
                $prevItemSort = ItemSortModel::factory()
                ->where('type',$itemSort['type'])
                ->where('foreign_type_id', $itemSort['foreign_type_id'])
                ->where('sort', $itemSort['sort'] - 1)
                ->findAll()
                ->first();
                if (!empty($prevItemSort)) {
                    ItemSortModel::factory()->set('id', $itemSort['id'])->modify([
                        'sort' => $prevItemSort['sort']
                    ]);
                    ItemSortModel::factory()->set('id', $prevItemSort['id'])->modify([
                        'sort' => $itemSort['sort']
                    ]);
                }
                
            } elseif ($_POST['direction'] == 'down') {
                $id = $_POST['id'];
                $itemSort = ItemSortModel::factory()->find($id)->getData();
                $nexttemSort = ItemSortModel::factory()
                ->where('type',$itemSort['type'])
                ->where('foreign_type_id', $itemSort['foreign_type_id'])
                ->where('sort', $itemSort['sort'] + 1)
                ->findAll()
                ->first();
                if (!empty($nexttemSort)) {
                    ItemSortModel::factory()->set('id', $itemSort['id'])->modify([
                        'sort' => $nexttemSort['sort']
                    ]);
                    ItemSortModel::factory()->set('id', $nexttemSort['id'])->modify([
                        'sort' => $itemSort['sort']
                    ]);
                }
            }
        }
        exit();
    }
    
    protected function getProducts($foreignTypeId, $type)
    {
        $productModel = ProductModel::factory()->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getLocaleId() . "' AND t2.field='name'", 'left outer')
        ->join('MultiLang', "t3.model='Product' AND t3.foreign_id=t1.id AND t3.locale='" . $this->getLocaleId() . "' AND t3.field='short_desc'", 'left outer')
        ->join('MultiLang', "t4.model='Product' AND t4.foreign_id=t1.id AND t4.locale='" . $this->getLocaleId() . "' AND t4.field='full_desc'", 'left outer')
        ->join ('ItemSort', "t5.foreign_id = t1.id AND t5.foreign_type_id = $foreignTypeId AND t5.type = $type")
        ->where ( 't1.status', ProductModel::STATUS_ACTIVE);
        if (isset($_GET['q']) && ! empty($_GET['q'])) {
            $q = str_replace(array(
                '%',
                '_'
            ), array(
                '\%',
                '\_'
            ), trim($_GET['q']));
            $productModel->where('t2.content LIKE', "%$q%");
            $productModel->orWhere('t3.content LIKE', "%$q%");
            $productModel->orWhere('t4.content LIKE', "%$q%");
            $productModel->orWhere('t1.sku LIKE', "%$q%");
        }
        
        if (isset($_GET['category_id']) && (int) $_GET['category_id'] > 0) {
            $productModel->where(sprintf("t1.id IN (SELECT `product_id` FROM `%s` WHERE `category_id` = '%u')", ProductCategoryModel::factory()->getTable(), (int) $_GET['category_id']));
        }

        if (isset($_GET['is_featured'])) {
            $productModel->where('t1.is_featured', 1);
        }
        
        $total = $productModel->findCount()->getData();
        $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
        $pages = ceil($total / $rowCount);
        $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
        $offset = ((int) $page - 1) * $rowCount;
        if ($page > $pages) {
            $page = $pages;
        }
        $data = $productModel->select(sprintf("t1.*,t5.id AS item_id, t5.sort AS sort, t5.foreign_id AS item_foreign_id, t5.foreign_type_id AS item_foreign_type_id, t5.type as item_type, t2.content AS name,  (SELECT `small_path` FROM `%1\$s`  WHERE `foreign_id` = `t1`.`id`  ORDER BY `sort` ASC  LIMIT 1) AS `pic`,  (SELECT COALESCE(SUM(`qty`), 0) FROM `%2\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `total_stock`,  (SELECT MIN(`price`) FROM `%2\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `min_price`,  (SELECT COUNT(`id`) FROM `%2\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `cnt_stock`,  (SELECT COUNT(DISTINCT `order_id`) FROM `%3\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `cnt_orders`  ", GalleryModel::factory()->getTable(), StockModel::factory()->getTable(), OrderStockModel::factory()->getTable()))
        ->orderBy("t5.sort ASC, t1.id DESC")
        ->limit($rowCount, $offset)
        ->findAll()
        ->getData();
        $counter = 1;
        $numberRows = count($data);
        foreach ($data as $k => $v) {
            $data[$k]['min_price_format'] = UtilComponent::formatCurrencySign(number_format($v['min_price'], 2), $this->option_arr['o_currency']);
            if ($v['cnt_stock'] > 1) {
                $data[$k]['min_price_format'] = __('front_price_from', true) . " " . $data[$k]['min_price_format'];
            }
            $data[$k]['item_sort']['down'] = 0;
            $data[$k]['item_sort']['up'] = 0;
            $data[$k]['item_sort']['id'] = $data[$k]['item_id'];
            $data[$k]['item_sort']['foreign_id'] = $data[$k]['item_foreign_id'];
            $data[$k]['item_sort']['foreign_type_id'] = $data[$k]['item_foreign_type_id'];
            $data[$k]['item_sort']['type'] = $data[$k]['item_type'];
            unset($data[$k]['item_id']);
            unset($data[$k]['item_foreign_id']);
            unset($data[$k]['item_foreign_type_id']);
            unset($data[$k]['item_type']);
            
            if ($counter > 1) {
                $data[$k]['item_sort']['up'] = 1;
            }
            if ($counter < $numberRows) {
                $data[$k]['item_sort']['down'] = 1;
            }
            
            $counter ++;
            
        }
        AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
        
    }
    
    protected function getArticles($foreignTypeId, $type)
    {
        $articleModel = ArticleModel::factory ()
        ->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Article' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'article_name'", 'left' )
        ->join ('ItemSort', "t3.foreign_id = t1.id AND t3.foreign_type_id = $foreignTypeId AND t3.type = $type");
        
        
        $articleModel->where(sprintf("t1.id IN (SELECT `article_id` FROM `%s` WHERE `category_id` = '%u')", ACategoryArticleModel::factory()->getTable(), (int)$foreignTypeId));
        
        if (isset ( $_GET ['q'] ) && ! empty ( $_GET ['q'] )) {
            $q = Objects::escapeString ( $_GET ['q'] );
            $articleModel->where ( 't2.content LIKE', "%$q%" );
        }
        
        
        $total = $articleModel->findCount ()->getData ();
        $rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 20;
        $pages = ceil ( $total / $rowCount );
        $page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
        $offset = (( int ) $page - 1) * $rowCount;
        if ($page > $pages) {
            $page = $pages;
        }
        $data = $articleModel->select (sprintf("t1.*, t3.id AS item_id, t3.sort AS sort, t3.foreign_id AS item_foreign_id, t3.foreign_type_id AS item_foreign_type_id, t3.type as item_type, t2.content AS article_name, (SELECT group_concat(ML.content  SEPARATOR '<br/>') FROM `%1\$s` as SC JOIN `%2\$s` as ML ON ML.foreign_id = SC.category_id AND ML.model = 'ACategory' AND ML.locale = '" . $this->getLocaleId () . "' AND ML.field = 'name' WHERE SC.article_id = t1.id) AS category", ACategoryArticleModel::factory()->getTable (), MultiLangModel::factory()->getTable()))->orderBy ( "t3.sort ASC" )->limit ( $rowCount, $offset )->findAll ()->getData ();
        $counter = 1;
        $numberRows = count($data);
        foreach ( $data as $k => $v ) {
            if (! empty ( $v ['modified'] )) {
                $v ['modified'] = UtilComponent::formatDate ( date ( 'Y-m-d', strtotime ( $v ['modified'] ) ), 'Y-m-d', $this->option_arr ['o_date_format'] ) . ', ' . UtilComponent::formatTime ( date ( 'H:i:s', strtotime ( $v ['modified'] ) ), 'H:i:s', $this->option_arr ['o_time_format'] );
            } else {
                $v ['modified'] = __ ( 'lblNA', true );
            }
            $data [$k] = $v;
            $data[$k]['item_sort']['down'] = 0;
            $data[$k]['item_sort']['up'] = 0;
            $data[$k]['item_sort']['id'] = $data[$k]['item_id'];
            $data[$k]['item_sort']['foreign_id'] = $data[$k]['item_foreign_id'];
            $data[$k]['item_sort']['foreign_type_id'] = $data[$k]['item_foreign_type_id'];
            $data[$k]['item_sort']['type'] = $data[$k]['item_type'];
            unset($data[$k]['item_id']);
            unset($data[$k]['item_foreign_id']);
            unset($data[$k]['item_foreign_type_id']);
            unset($data[$k]['item_type']);
            
            if ($counter > 1) {
                $data[$k]['item_sort']['up'] = 1;
            }
            if ($counter < $numberRows) {
                $data[$k]['item_sort']['down'] = 1;
            }
            
            $counter ++;
        }
        AppController::jsonResponse ( compact ( 'data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction' ) );
    }
    
    protected function getPages($foreignTypeId, $type)
    {
        $pageModel = PageModel::factory ()
        ->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Page' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'page_name'", 'left' )
        ->join ('ItemSort', "t3.foreign_id = t1.id AND t3.foreign_type_id = $foreignTypeId AND t3.type = $type");
                
        $pageModel->where(sprintf("t1.id IN (select `page_id` from `%1\$s` where `category_id` = $foreignTypeId )", PCategoryPageModel::factory()->getTable ()) );
        
        $total = $pageModel->findCount ()->getData ();
        $rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 20;
        $pages = ceil ( $total / $rowCount );
        $page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
        $offset = (( int ) $page - 1) * $rowCount;
        if ($page > $pages) {
            $page = $pages;
        }
        
        $data = $pageModel->select (sprintf("t1.*, , t3.id AS item_id, t3.sort AS sort, t3.foreign_id AS item_foreign_id, t3.foreign_type_id AS item_foreign_type_id, t3.type as item_type, t2.content AS page_name, (SELECT group_concat(ML.content  SEPARATOR '<br/>') FROM `%1\$s` as SC JOIN `%2\$s` as ML ON ML.foreign_id = SC.category_id AND ML.model = 'PageCategory' AND ML.locale = '" . $this->getLocaleId () . "' AND ML.field = 'name' WHERE SC.page_id = t1.id) AS category", PCategoryPageModel::factory()->getTable (), MultiLangModel::factory()->getTable()))->orderBy ( "t3.sort ASC" )->limit ( $rowCount, $offset )->findAll ()->getData ();
        $counter = 1;
        $numberRows = count($data);
        foreach ( $data as $k => $v ) {
            if (! empty ( $v ['modified'] )) {
                $v ['modified'] = UtilComponent::formatDate ( date ( 'Y-m-d', strtotime ( $v ['modified'] ) ), 'Y-m-d', $this->option_arr ['o_date_format'] ) . ', ' . UtilComponent::formatTime ( date ( 'H:i:s', strtotime ( $v ['modified'] ) ), 'H:i:s', $this->option_arr ['o_time_format'] );
            } else {
                $v ['modified'] = __ ( 'lblNA', true );
            }
            $data [$k] = $v;
            $data[$k]['item_sort']['down'] = 0;
            $data[$k]['item_sort']['up'] = 0;
            $data[$k]['item_sort']['id'] = $data[$k]['item_id'];
            $data[$k]['item_sort']['foreign_id'] = $data[$k]['item_foreign_id'];
            $data[$k]['item_sort']['foreign_type_id'] = $data[$k]['item_foreign_type_id'];
            $data[$k]['item_sort']['type'] = $data[$k]['item_type'];
            unset($data[$k]['item_id']);
            unset($data[$k]['item_foreign_id']);
            unset($data[$k]['item_foreign_type_id']);
            unset($data[$k]['item_type']);
            
            if ($counter > 1) {
                $data[$k]['item_sort']['up'] = 1;
            }
            if ($counter < $numberRows) {
                $data[$k]['item_sort']['down'] = 1;
            }
            
            $counter ++;
        }
        AppController::jsonResponse ( compact ( 'data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction' ) );
    }
}