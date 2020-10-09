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
use App\Models\VoucherModel;
use App\Models\VoucherProductModel;

class PromotionProductsWidget extends Widget{
    public static function factory() {
        return new PromotionProductsWidget();
    }
    public function init($data) {
        $this->setLocaleId($data['locale_id']);
        $this->setBaseUrl($data['base_url']);
        $this->option_arr = $data['option_arr'];
    }
    
    
    public function run($renderString = false) {
        $todayInWeek = strtolower(date('l'));
        $today = date('Y-m-d H:i:s');
        $time = date('H:i:s');
        $vouchers = [];
        $periodVouchers = VoucherModel::factory()->select('t1.*, t2.product_id as `product_id`')->join('VoucherProduct', 't2.voucher_id = t1.id')->where("CONCAT(t1.date_from, ' ', t1.time_from) <=", $today)->where("CONCAT(t1.date_to,' ', t1.time_to) >=", $today)->findAll()->getData();
        $fixVouchers = VoucherModel::factory()->select('t1.*, t2.product_id as `product_id`')->join('VoucherProduct', 't2.voucher_id = t1.id')->where('t1.date_from', $today)->where('t1.time_from <=', $time)->where('t1.time_to >=', $time)->findAll()->getData();
        $recurringVouchers = VoucherModel::factory()->select('t1.*, t2.product_id as `product_id`')->join('VoucherProduct', 't2.voucher_id = t1.id')->where('t1.every', $todayInWeek)->where('t1.time_from <=', $time)->where('t1.time_to >=', $time)->findAll()->getData();
        $vouchers = array_merge($periodVouchers, $fixVouchers, $recurringVouchers);
        $field = "id";
        usort($vouchers, function($a, $b) use ($field) {
            return strcmp($a[$field], $b[$field]);
        });
        $productIds = array_column($vouchers, 'product_id');
        $productArr = [];
        if (!empty($productIds)) {
            $ProductModel = ProductModel::factory();
            
            $ProductModel = ProductModel::factory ()->join ( 'MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='name'", 'left outer' )
            ->join ( 'MultiLang', "t3.model='Product' AND t3.foreign_id=t1.id AND t3.locale='" . $this->getFrontendLocaleId() . "' AND t3.field='short_desc'", 'left outer' )->whereIn ( 't1.id', $productIds);
            
            $imagePath = 'medium_path';
            $imageType = GalleryModel::SOURCE_TYPE_PRODUCT_IMAGE;
            $productArr = $ProductModel->select ( sprintf ( "t1.*,
				t2.content AS `name`,t3.content AS `short_desc`,
				(SELECT MIN(`price`) FROM `%2\$s`  WHERE `product_id` = `t1`.`id` AND `qty` > 0  LIMIT 1) AS `min_price`,
				(SELECT MAX(`price`) FROM `%2\$s`  WHERE `product_id` = `t1`.`id` AND `qty` > 0  LIMIT 1) AS `max_price`,
				(SELECT `id` FROM `%2\$s`  WHERE `product_id` = `t1`.`id` AND `qty` > 0  ORDER BY `price` ASC  LIMIT 1) AS `stockId`,
				(SELECT `qty` FROM `%2\$s`  WHERE `id` = `stockId`  LIMIT 1) AS `stockQty`,
				(SELECT GROUP_CONCAT(CONCAT_WS('_', attribute_id, attribute_parent_id))  FROM `%3\$s`  WHERE `product_id` = `t1`.`id` AND `stock_id` = `stockId`  LIMIT 1) AS `stockId_attr`,
				(SELECT `".$imagePath."` FROM `%1\$s`  WHERE `source_type` = $imageType AND `foreign_id` = `t1`.`id`  ORDER BY ISNULL(`sort`), `sort` ASC, `id` ASC  LIMIT 1) AS `pic`,
				(SELECT GROUP_CONCAT(`category_id`) FROM `%4\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `category_ids`,
				(SELECT GROUP_CONCAT(CONCAT_WS('.', `id`, IF(`type`='single',NULL,(SELECT `id` FROM `%6\$s` WHERE `extra_id` = te.id ORDER BY `price` ASC LIMIT 1)))) FROM `%5\$s` AS `te`
				WHERE `product_id` = t1.id AND `is_mandatory` = '1' LIMIT 1) AS `m_extras`", GalleryModel::factory ()->getTable (), StockModel::factory ()->getTable (), StockAttributeModel::factory ()->getTable (), ProductCategoryModel::factory ()->getTable (), ExtraModel::factory ()->getTable (), ExtraItemModel::factory ()->getTable (), AttributeModel::factory ()->getTable () ) )
				->orderBy ( '`id` ASC' )
				->limit (12)
				->findAll ()
				->toArray ( 'category_ids', ',' )
				->toArray ( 'm_extras', ',' )
				->getData ();
				if (!empty($productArr)) {
				    $productArr = UtilComponent::attachCustomLinks($productArr, RouterModel::TYPE_PRODUCT, $this->getFrontendLocaleId());
				    foreach ($productArr as &$p) {
				        $p['gallery_arr'] = GalleryModel::factory()->select('t1.small_path, t1.medium_path, t1.large_path, t1.alt')
				        ->where('t1.foreign_id', $p['id'])
				        ->where('t1.source_type', GalleryModel::SOURCE_TYPE_PRODUCT_IMAGE)
				        ->orderBy('t1.sort ASC')
				        ->findAll()
				        ->getData();
				    }
				}
				foreach ($vouchers as $v) {
				    foreach ($productArr as &$product) {
				        if ($product['id'] == $v['product_id']) {
				            $product['type_sale'] = $v['type'];
				            $product['discount_sale'] = $v['discount'];
				            $product['name_sale'] = $v['code'];
				            $product['date_to_sale'] = $v['date_to'];
				        }
				    }
				}
        }
        
		$data = array(
		    'product_arr' => $productArr,
		    'option_arr' => $this->option_arr,
		);
		if ($renderString) {
		    return $this->output($data);
		}
		$this->render($data);
    }
}