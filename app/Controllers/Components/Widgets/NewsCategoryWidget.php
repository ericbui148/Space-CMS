<?php
namespace App\Controllers\Components\Widgets;

use App\Models\WidgetModel;
use App\Models\MultiLangModel;
use App\Models\RouterModel;
use App\Controllers\Components\UtilComponent;
use App\Models\ArticleModel;
use App\Models\ACategoryModel;
use App\Models\ACategoryArticleModel;
use App\Models\ItemSortModel;

class NewsCategoryWidget extends Widget
{
	public static function factory() {
		return new NewsCategoryWidget();
	}
	public function init($data) {
		$this->setLocaleId($data['locale_id']);
		$this->setBaseUrl($data['base_url']);
		$this->data = $data;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function run($renderString = false) {
	    $articleModel = ArticleModel::factory()
    	    ->join('MultiLang', "t2.model='Article' AND t2.foreign_id=t1.id AND t2.field='article_name' AND t2.locale='".$this->getFrontendLocaleId()."'", 'left outer')
    	    ->join('MultiLang', "t3.model='Article' AND t3.foreign_id=t1.id AND t3.field='article_short_description' AND t3.locale='".$this->getFrontendLocaleId()."'", 'left outer');
		$widgetRecord = WidgetModel::factory()->where('t1.name', $this->name)->where('t1.type', WidgetModel::WIDGET_TYPE_ARTICLE_CAT)->findAll()->limit(1)->getData();
		$metadata = !empty($widgetRecord[0]['params'])? json_decode($widgetRecord[0]['params'], true) : [];
		if (!empty($metadata['limit'])) {
			$this->data['limit'] = $metadata['limit'];
		}
		$limit = isset($this->data['limit'])? $this->data['limit'] : 6;
		$foreignTypeId = $widgetRecord[0]['value'];
		$itemSortType = ItemSortModel::TYPE_WIDGET_ARTICLES;
		$arrs = [];
		if(!empty($foreignTypeId)) {
		    $articleModel = $articleModel->join ('ItemSort', "t4.foreign_id = t1.id AND t4.foreign_type_id = $foreignTypeId AND t4.type = $itemSortType", 'left');
			$arrs = $articleModel->select('t1.*, t2.content as `name`, t3.content as `short_description`,  (select count(id) from  `article_viewers`as fv where fv.article_id  = t1.id) as `num_view`')
			->where( sprintf ( "t1.id IN (SELECT `article_id` FROM `%s` WHERE `category_id` = ".$foreignTypeId.")", ACategoryArticleModel::factory ()->getTable ()))
			->orderBy('t4.sort ASC, t1.on_date desc')->limit($limit)->findAll()->getData();
		}
		if(!empty($foreignTypeId)) {
			$sectionCategoryModel = ACategoryModel::factory();
			$category = $sectionCategoryModel->find ( (int)$foreignTypeId )->getData ();
			$category ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $category['id'], 'ACategory' );
			$category['name'] = $category['i18n'][$this->getFrontendLocaleId()]['name'];
		}
		if (!empty($arrs)) {
		    $arrs = UtilComponent::attachCustomLinks($arrs, RouterModel::TYPE_ARTICLE, $this->getFrontendLocaleId());
		}
		
		$data = array(
				'template' => !empty($this->data['widget']['template'])? $this->data['widget']['template'] : null,
				'metadata' => $metadata,
				'widget_name' => !empty($this->data['widget_name'])?$this->data['widget_name'] : null,
				'article_arr' => $arrs,
				'category' => isset($category)? $category : array(),
				'params' => $this->data
		);

		if ($renderString) {
			return $this->output($data);
		}
		$this->render($data);
	}

	public static function createItemSorts($foreignTypeId)
	{
		$articleModel = ArticleModel::factory()->select('id')
		->where( sprintf ( "t1.id IN (SELECT `article_id` FROM `%s` WHERE `category_id` = ".$foreignTypeId.")", ACategoryArticleModel::factory ()->getTable ()))
		->where ( 'status', ArticleModel::STATUS_ACTIVE )->orderBy ( '`id` DESC' );
	    
	    $articleArr = $articleModel->findAll()->getData();
	    if (!empty($articleArr)) {
	        $batchValues = [];
	        $sort = 1;
	        foreach ($articleArr as $article) {
	            $batchValues[] = [
	                $article['id'],
	                ItemSortModel::TYPE_WIDGET_ARTICLES,
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