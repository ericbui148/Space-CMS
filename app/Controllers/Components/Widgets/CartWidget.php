<?php
namespace App\Controllers\Components\Widgets;

use App\Models\ProductModel;
use App\Controllers\Components\UtilComponent;
use App\Models\RouterModel;
use App\Models\TaxModel;
use App\Models\StockModel;
use App\Plugins\Gallery\Models\GalleryModel;
use App\Controllers\ShopCartAppController;
use App\Models\ProductCategoryModel;

class CartWidget extends Widget 
{
    public function init($data)
    {
        $this->setLocaleId($data['locale_id']);
        $this->setBaseUrl($data['base_url']);
        $this->controller = $data['controller'];
        $this->option_arr = $this->controller->option_arr;
        
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function run($renderString = false)
    {
        $widget_data = [];
        if (!empty($this->controller->cart) && !$this->controller->cart->isEmpty() && (int) $this->controller->option_arr['o_disable_orders'] === 0) {
            $data = $this->GetCart();
            if (isset($_SESSION[$this->controller->defaultTax]) && (int) $_SESSION[$this->controller->defaultTax] > 0) {
                foreach ($data['tax_arr'] as $item) {
                    if ($item['id'] == $_SESSION[$this->controller->defaultTax]) {
                        $widget_data['o_tax'] = $item['tax'];
                        $widget_data['o_shipping'] = $item['shipping'];
                        $widget_data['o_free'] = $item['free'];
                        break;
                    }
                }
            }
            $widget_data['cart_arr'] = $data['cart_arr'];
            $widget_data['arr'] = $data['arr'];
            $widget_data['extra_arr'] = $data['extra_arr'];
            $widget_data['attr_arr'] = $data['attr_arr'];
            $widget_data['stock_arr'] = $data['stock_arr'];
            $widget_data['tax_arr'] = $data['tax_arr'];
            $widget_data['image_arr'] = $data['image_arr'];
        }
        
        if ($renderString) {
            return $this->output($widget_data);
        }
        $this->render($widget_data);
    }
    
    protected function GetCart()
    {
        $order_arr = $product_id = $stock_id = array();
        $cart_arr = $this->get('cart_arr');
        foreach ($cart_arr as $cart_item) {
            if (! isset($order_arr[$cart_item['stock_id']])) {
                $order_arr[$cart_item['stock_id']] = 0;
            }
            $order_arr[$cart_item['stock_id']] += $cart_item['qty'];
            $product_id[] = $cart_item['product_id'];
            if (! empty($cart_item['stock_id'])) {
                $stock_id[] = $cart_item['stock_id'];
            }
        }
        $arr = ProductModel::factory()->select(sprintf("t1.*, t2.content AS name,  (SELECT GROUP_CONCAT(`category_id`) FROM `%1\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `category_ids`", ProductCategoryModel::factory()->getTable()))
        ->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='name'", 'left outer')
        ->join('Stock', 't3.product_id=t1.id', 'inner')
        ->whereIn('t1.id', $product_id)
        ->groupBy('t1.id')
        ->findAll()
        ->toArray('category_ids', ',')
        ->getData();
        if (!empty($arr)) {
            $arr = UtilComponent::attachCustomLinks($arr, RouterModel::TYPE_PRODUCT, $this->getFrontendLocaleId());
        }
        
        $tax_arr = TaxModel::factory()->select('t1.*, t2.content AS location')
        ->join('MultiLang', "t2.model='Tax' AND t2.foreign_id=t1.id AND t2.field='location' AND t2.locale='" . $this->getFrontendLocaleId() . "'", 'left outer')
        ->orderBy('`location` ASC')
        ->findAll()
        ->getData();
        $tmp_stock_arr = StockModel::factory()->whereIn('t1.id', $stock_id)
        ->findAll()
        ->getData();
        $stock_arr = array();
        foreach ($tmp_stock_arr as $stock) {
            $stock_arr[$stock['id']] = $stock;
        }
        $image_arr = StockModel::factory()->select('t1.id, t2.small_path')
        ->join('Gallery', 't2.id=t1.image_id', 'left outer')
        ->whereIn('t1.id', $stock_id)
        ->findAll()
        ->getDataPair('id', 'small_path');
        foreach ($image_arr as $id => $img) {
            if (empty($img)) {
                $gallery_arr = GalleryModel::factory()->select('t1.*')
                ->where("`foreign_id` = (SELECT TS.`product_id` FROM `" . StockModel::factory()->getTable() . "` AS TS WHERE TS.id='$id')")
                ->limit(1)
                ->findAll()
                ->getData();
                if (! empty($gallery_arr)) {
                    $image_arr[$id] = $gallery_arr[0]['small_path'];
                }
            }
        }
        $extra_arr = ShopCartAppController::GetExtrasList($product_id, $this->getFrontendLocaleId());
        $attr_arr = ShopCartAppController::GetAttr($product_id, $this->getFrontendLocaleId());
        return compact('arr', 'extra_arr', 'order_arr', 'attr_arr', 'stock_arr', 'tax_arr', 'image_arr', 'cart_arr');
    }
    
    public function get($key = NULL) {
        if (is_null ( $key )) {
            return $this->controller->tpl;
        }
        if (array_key_exists ( $key, $this->controller->tpl )) {
            return $this->controller->tpl [$key];
        }
        return FALSE;
    }

}