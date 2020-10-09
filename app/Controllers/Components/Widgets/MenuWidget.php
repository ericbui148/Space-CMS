<?php
namespace App\Controllers\Components\Widgets;

use App\Models\WidgetModel;
use App\Models\MenuItemModel;
use App\Controllers\Components\UtilComponent;
use App\Models\TagModel;
use App\Models\RouterModel;
use App\Models\ArticleModel;
use App\Models\ACategoryModel;
use App\Models\PCategoryModel;
use App\Models\PageModel;
use App\Models\CategoryModel;

class MenuWidget extends Widget 
{
	
	public function init($data) {
		if(isset($data['locale_id'])) {
			$this->setLocaleId($data['locale_id']);
		}
		
		if(isset($data['base_url'])) {
			$this->setBaseUrl($data['base_url']);
		}
		
		if(isset($data['class'])) {
			$this->setClass($data['class']);
		}
		$this->data = $data;
		$this->option_arr = $data['option_arr'];
	}
	
	
	public function run($renderString = false) {
		$widgetRecord = WidgetModel::factory()->where('t1.name', $this->name)->where('t1.type', WidgetModel::WIDGET_TYPE_MENU)->findAll()->limit(1)->getData();
		$nodeArr = array();
		if(!empty($widgetRecord[0])) {
			$nodeArr = MenuItemModel::factory ()->getNode ( $this->getFrontendLocaleId(), 1, (int)$widgetRecord[0]['value']);
		}
		if(!empty($nodeArr)) {
			foreach ($nodeArr as &$node) {
				$referenceData = $this->getRefrenceData($node);
				$key_arr = array_keys($referenceData);
				foreach ($key_arr as $key) {
					$node['data'][$key] = $referenceData[$key];
				}
			}
		}

		$data = array(
				'template' => !empty($this->data['widget']['template'])? $this->data['widget']['template'] : null,
				'metadata' => !empty($widgetRecord[0]['params'])? json_decode($widgetRecord[0]['params'], true) : [],	
				'node_arr' => $nodeArr,
				'menu_id' => (int)@$widgetRecord[0]['value'],
				'class' => $this->class,
				'widget_name' => @$this->data['widget_name'],
				'widget' => !empty($widgetRecord[0])? $widgetRecord[0] : null
		);

		if ($renderString) {
			return $this->output($data);
		}
		$this->render($data);
	}
	
	protected function getRefrenceData($node) {
		$linkType = $node['data']['link_type'];
		$linkData = $node['data']['link_data'];
		$referenceData = array();
		switch ($linkType) {
			case MenuItemModel::LINK_TYPE_DEFAULT:
				$referenceData['link'] = $linkData;
				if ($linkData == '#') {
				    $url =  "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}#";
				    $referenceData['link'] = $url;
				}
				
				break;
			case MenuItemModel::LINK_TYPE_SINGLE_ARTICLE:
				$article = ArticleModel::factory()->select("t1.id as `id`, t2.content as `name`")->join('MultiLang', "t2.model='Article' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='article_name'", 'left outer')->find((int)$linkData)->getData();
				if (!empty($article)) {
					$article = UtilComponent::attachSingleCustomLink($article, RouterModel::TYPE_ARTICLE, $this->getFrontendLocaleId());
					$referenceData['link'] = !empty($article['url'])? $article['url'] : '/';
				}

				break;
			case MenuItemModel::LINK_TYPE_ARTICE_CATEGORY:
				$category = ACategoryModel::factory()->select("t1.id as `id`, t2.content as `name`")->join('MultiLang', "t2.model='ACategory' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='name'", 'left outer')->find((int)$linkData)->getData();
				$category = UtilComponent::attachSingleCustomLink($category, RouterModel::TYPE_ARTICLE_CATEGORY, $this->getFrontendLocaleId());
				$referenceData['link'] = !empty($category['url'])? $category['url'] : '/';
				break;
			case MenuItemModel::LINK_TYPE_PAGE_CATEGORY:
					$category = PCategoryModel::factory()->select("t1.id as `id`, t2.content as `name`")->join('MultiLang', "t2.model='PCategory' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='name'", 'left outer')->find((int)$linkData)->getData();
					$category = UtilComponent::attachSingleCustomLink($category, RouterModel::TYPE_PAGE_CATEGORY, $this->getFrontendLocaleId());
					$referenceData['link'] = !empty($category['url'])? $category['url'] : '/';
					break;				
			case MenuItemModel::LINK_TYPE_PAGE:
				$page = PageModel::factory()->select("t1.id as `id`, t2.content as `name`")->join('MultiLang', "t2.model='Page' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='page_name'", 'left outer')->find((int)$linkData)->getData();
				if (!empty($page)) {
				    $page = UtilComponent::attachSingleCustomLink($page, RouterModel::TYPE_PAGE, $this->getFrontendLocaleId());
				    $referenceData['link'] = !empty($page['url'])? $page['url'] : '/';
				}
				break;
			case MenuItemModel::LINK_TYPE_TAG:
				$tag = TagModel::factory()->find((int)$linkData)->getData();
				$tag = UtilComponent::attachSingleCustomLink($tag, RouterModel::TYPE_TAG, $this->getFrontendLocaleId());
				$referenceData['link'] = !empty($tag['url'])? $tag['url'] : '/';
				break;
			case MenuItemModel::LINK_TYPE_PRODUCT_CATEGORY:
			    $category = CategoryModel::factory()->select("t1.id as `id`, t2.content as `name`")->join('MultiLang', "t2.model='Category' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='name'", 'left outer')->find((int)$linkData)->getData();
			    $category = UtilComponent::attachSingleCustomLink($category, RouterModel::TYPE_PRODUCT_CATEGORY, $this->getFrontendLocaleId());
			    $referenceData['link'] = !empty($category['url'])? $category['url'] : '/';
			    break;
		}
		
		return $referenceData;
	}
	
}