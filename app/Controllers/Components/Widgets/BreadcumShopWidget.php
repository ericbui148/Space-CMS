<?php
namespace App\Controllers\Components\Widgets;

use App\Controllers\Components\UtilComponent;
use App\Models\RouterModel;
use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\ProductCategoryModel;

class BreadcumShopWidget extends Widget 
{
	protected $pageType;
	public function init($data) {
		$this->setLocaleId($data['locale_id']);
		$this->setBaseUrl($data['base_url']);
		$this->pageType = $data['page_type'];
		$this->controller = $data['controller'];
		$this->data = $data;
	}
	
	public function setName($widgetName) {
		$this->name = $widgetName;
	}
	public function run($renderString = false) {

		$linkArr = array();
		$catId = 0;
		switch ($this->pageType) {
			case 'product':
			    $productId = (int)$this->controller->request->params['id'];
			    $product = ProductModel::factory()->select('t1.*, t2.content as `name`')->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='name'", 'left outer')->find($productId)->getData();
			    $product = UtilComponent::attachSingleCustomLink($product, RouterModel::TYPE_PRODUCT, $this->getFrontendLocaleId());
			    $catId = ProductCategoryModel::factory()->select('t1.*')->where('t1.product_id', $productId)->limit(1)->findAll()->getDataIndex(0);
			    $catId = $catId['category_id'];
			    array_push($linkArr, array(
			        'name' => $product['name'],
			        'link' => $product['url']
			    ));
			    break;
			case 'product_category':
				$catId = (int)$this->controller->request->params['category_id'];
				break;
		}
		
		$routerType = null;
		$categoryModel =  null;
		switch ($this->pageType) {
			case 'product':
			case 'product_category':
				$categoryModel = CategoryModel::factory();
				$routerType = RouterModel::TYPE_PRODUCT_CATEGORY;
				break;
		}
		if($catId > 1 && !is_null($categoryModel)) {
		        $i = $catId;
		        while ($i > 1) {
		            $value = array();
		            $category = $categoryModel->select('t1.*, t2.content as `name`')
		            ->join ( 'MultiLang', "t2.model='Category' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='name'", 'left outer' )
		            ->find($i)->getData();
		            $category = UtilComponent::attachSingleCustomLink($category, $routerType, $this->getFrontendLocaleId()); 
		            $value['name'] = $category['name'];
		            $value['link'] = $category['url'];
		            array_push($linkArr, $value);
		            $i = $category['parent_id'];
		        }
			
		}
		$data = array(
				'template' => !empty($this->data['widget']['template'])? $this->data['widget']['template'] : null,
				'link_arr' => $linkArr,
		);

		if ($renderString) {
			return $this->output($data);
		}
		$this->render($data);
	}
}