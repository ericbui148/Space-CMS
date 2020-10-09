<?php
namespace App\Controllers\Components\Widgets;

use App\Controllers\Components\UtilComponent;
use App\Models\PageModel;
use App\Models\PCategoryPageModel;
use App\Models\ArticleModel;
use App\Models\ACategoryArticleModel;
use App\Models\ACategoryModel;
use App\Models\PCategoryModel;
use App\Models\RouterModel;

class BreadcumWidget extends Widget 
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
		$categoryModelClass = null;
		switch ($this->pageType) {
			case 'article':
			    $articleId = (int)$this->controller->request->params['id'];
			    $article = ArticleModel::factory()->select('t1.*, t2.content as `name`')->join('MultiLang', "t2.model='Article' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='article_name'", 'left outer')->find($articleId)->getData();
			    $article = UtilComponent::attachSingleCustomLink($article, RouterModel::TYPE_ARTICLE, $this->getFrontendLocaleId());
			    $catId = ACategoryArticleModel::factory()->select('t1.*')->where('t1.article_id', $articleId)->limit(1)->findAll()->getDataIndex(0);
			    $catId = $catId['category_id'];
			    $categoryModelClass = "ACategory";
			    array_push($linkArr, array(
			        'name' => $article['name'],
			        'link' => $article['url']
			    ));
			    break;
			case 'page':
				$pageId = (int)$this->controller->request->params['id'];
				$page = PageModel::factory()->select('t1.*, t2.content as `name`')->join('MultiLang', "t2.model='Page' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='page_name'", 'left outer')->find($pageId)->getData();
				$page = UtilComponent::attachSingleCustomLink($page, RouterModel::TYPE_PAGE, $this->getFrontendLocaleId());
				$catId = PCategoryPageModel::factory()->select('t1.*')->where('t1.page_id', $pageId)->limit(1)->findAll()->getDataIndex(0);
				$catId = $catId['category_id'];
				$categoryModelClass = "PCategory";
				array_push($linkArr, array(
					'name' => $page['name'],
				    'link' => $page['url']
				));
				break;
			case 'article_category':
			    $catId = (int)$this->controller->request->params['category_id'];
			    $categoryModelClass = "ACategory";
			    break;
			case 'page_category':
				$catId = (int)$this->controller->request->params['category_id'];
				$categoryModelClass = "PCategory";
				break;
		}
		
		$routerType = null;
		$categoryModel =  null;
		switch ($this->pageType) {
			case 'article':
			case 'article_category':
				$categoryModel = ACategoryModel::factory();
				$routerType = RouterModel::TYPE_ARTICLE_CATEGORY;
				break;
			case 'page_category':
			case 'page':
				$categoryModel = PCategoryModel::factory();
				$routerType = RouterModel::TYPE_PAGE_CATEGORY;
				break;
		}
		if($catId > 1 && !is_null($categoryModel)) {
		        $i = $catId;
		        while ($i > 1) {
		            $value = array();
		            $category = $categoryModel->select('t1.*, t2.content as `name`')
		            ->join ( 'MultiLang', "t2.model= '".$categoryModelClass."' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getFrontendLocaleId() . "' AND t2.field='name'", 'left outer' )
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