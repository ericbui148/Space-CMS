<?php
namespace App\Controllers\Components\Widgets;

use App\Models\ProductModel;
use App\Plugins\Gallery\Models\GalleryModel;
use App\Models\AttributeModel;
use App\Models\ProductCategoryModel;
use App\Models\ExtraModel;
use App\Models\ExtraItemModel;
use App\Models\StockModel;
use App\Controllers\Components\UtilComponent;
use App\Models\RouterModel;
use App\Models\StockAttributeModel;
use App\Models\ItemSortModel;
use App\Models\WidgetModel;

class FeaturedProductsWidget extends Widget
{
    const DEFAULT_NUMBER_RECORD = 12;
    
	public static function factory() {
	    return new FeaturedProductsWidget();
	}
	public function init($data) {
		$this->setLocaleId($data['locale_id']);
		$this->setBaseUrl($data['base_url']);
		$this->option_arr = $data['option_arr'];
	}
		
	
	public function run($renderString = false) {
	    $widget = WidgetModel::factory()
	    ->where('t1.name', $this->name)
	    ->where('t1.type', WidgetModel::WIDGET_TYPE_FEATURED_PRODUCTS)
	    ->findAll()
	    ->limit(1)
	    ->first();
	    $data = [];
	    if (!empty($widget)) {
	        $limit = self::DEFAULT_NUMBER_RECORD;
	        $params = json_decode($widget['params'], true);
	        if (!empty($params['limit'])) {
	            $limit = $params['limit'];
	        }
	        
	        $productArr = self::getProducts($widget['id'], $this->getFrontendLocaleId(), $limit, true);
	        $data = array(
	            'product_arr' => $productArr,
	            'option_arr' => $this->option_arr,
	            'widget_id' => $widget['id']
	        );
	    }
		if ($renderString) {
		    return $this->output($data);
		}
		$this->render($data);
	}
	
	public static function getProducts($foreignTypeId, $localeId, $limit = self::DEFAULT_NUMBER_RECORD, $sorted = true)
	{
        $sortType = ItemSortModel::TYPE_WIDGET_PRODUCTS;
	    $ProductModel = ProductModel::factory ()
	    ->join ( 'MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.locale='" . $localeId . "' AND t2.field='name'", 'left outer' )
	    ->join ( 'MultiLang', "t3.model='Product' AND t3.foreign_id=t1.id AND t3.locale='" . $localeId . "' AND t3.field='short_desc'", 'left outer' )
	    ->join ('ItemSort', "t4.foreign_id = t1.id AND t4.foreign_type_id = $foreignTypeId AND t4.type = $sortType", "left")
	    ->where ( 't1.status', ProductModel::STATUS_ACTIVE)
	    ->where('t1.is_featured', 1);
	    
	    $imagePath = 'medium_path';
	    $imageType = GalleryModel::SOURCE_TYPE_PRODUCT_IMAGE;
	    $productArr = $ProductModel->select ( sprintf ( "t1.*,
				t2.content AS `name`,
				(SELECT MIN(`price`) FROM `%2\$s`  WHERE `product_id` = `t1`.`id` AND `qty` > 0  LIMIT 1) AS `min_price`,
				(SELECT MAX(`price`) FROM `%2\$s`  WHERE `product_id` = `t1`.`id` AND `qty` > 0  LIMIT 1) AS `max_price`,
				(SELECT `id` FROM `%2\$s`  WHERE `product_id` = `t1`.`id` AND `qty` > 0  ORDER BY `price` ASC  LIMIT 1) AS `stockId`,
				(SELECT `qty` FROM `%2\$s`  WHERE `id` = `stockId`  LIMIT 1) AS `stockQty`,
				(SELECT GROUP_CONCAT(CONCAT_WS('_', attribute_id, attribute_parent_id))  FROM `%3\$s`  WHERE `product_id` = `t1`.`id` AND `stock_id` = `stockId`  LIMIT 1) AS `stockId_attr`,
				(SELECT `".$imagePath."` FROM `%1\$s`  WHERE `source_type` = $imageType AND `foreign_id` = `t1`.`id`  ORDER BY ISNULL(`sort`), `sort` ASC, `id` ASC  LIMIT 1) AS `pic`,
				(SELECT GROUP_CONCAT(`category_id`) FROM `%4\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `category_ids`,
				(SELECT GROUP_CONCAT(CONCAT_WS('.', `id`, IF(`type`='single',NULL,(SELECT `id` FROM `%6\$s` WHERE `extra_id` = te.id ORDER BY `price` ASC LIMIT 1)))) FROM `%5\$s` AS `te`
				WHERE `product_id` = t1.id AND `is_mandatory` = '1' LIMIT 1) AS `m_extras`", GalleryModel::factory ()->getTable (), StockModel::factory ()->getTable (), StockAttributeModel::factory ()->getTable (), ProductCategoryModel::factory ()->getTable (), ExtraModel::factory ()->getTable (), ExtraItemModel::factory ()->getTable (), AttributeModel::factory ()->getTable () ) )
				->orderBy ( 't4.sort ASC, t1.id DESC' )
				->limit ($limit)
				->findAll ()
				->toArray ( 'category_ids', ',' )
				->toArray ( 'm_extras', ',' )
				->getData ();
		if (!empty($productArr)) {
		    $productArr = UtilComponent::attachCustomLinks($productArr, RouterModel::TYPE_PRODUCT, $localeId);
		    $productArr = UtilComponent::attachVouchers($productArr, 'm');
		}
		return $productArr;
	}
	
	public static function createItemSorts($foreignTypeId)
	{
	    $ProductModel = ProductModel::factory()->select('id')->where ( 'status', ProductModel::STATUS_ACTIVE )->where('t1.is_featured', 1)->orderBy ( '`id` DESC' );
	    
	    $productArr = $ProductModel->findAll()->getData();
	    if (!empty($productArr)) {
	        $batchValues = [];
	        $sort = 1;
	        foreach ($productArr as $product) {
	            $batchValues[] = [
	                $product['id'],
	                ItemSortModel::TYPE_WIDGET_PRODUCTS,
	                $foreignTypeId,
	                $sort
	            ];
	            $sort++;
	        }
	        ItemSortModel::factory()->setBatchFields([
	            'foreign_id', 'type', 'foreign_type_id', 'sort'
	        ])->setBatchRows($batchValues)->insertBatch()->getAffectedRows();
	    }
	}
}