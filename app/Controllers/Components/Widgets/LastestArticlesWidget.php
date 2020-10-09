<?php
namespace App\Controllers\Components\Widgets;

use App\Models\RouterModel;
use App\Controllers\Components\UtilComponent;
use App\Models\ArticleModel;
use App\Models\ItemSortModel;

class LastestArticlesWidget extends Widget
{
	public static function factory() {
		return new LastestArticlesWidget();
	}
	public function init($data) {
		$this->setLocaleId($data['locale_id']);
		$this->setBaseUrl($data['base_url']);
		$this->data = $data;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public static function createItemSorts($foreignTypeId)
	{
	    $arrs = ArticleModel::factory()
	    ->where('t1.status', ArticleModel::STATUS_ACTIVE)
	    ->orderBy('t1.id desc')->findAll()->getData();
	    if (!empty($arrs)) {
	        $batchValues = [];
	        $sort = 1;
	        foreach ($arrs as $article) {
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
	
	public function run($renderString = false) {
		$articleModel = ArticleModel::factory();
		$limit = isset($this->data['limit'])? $this->data['limit'] : 10;
		$arrs = $articleModel->select('t1.*, t2.content as `name`, t3.content as `short_description`,  (select count(id) from  `article_viewers`as fv where fv.article_id  = t1.id) as `num_view`')
		->join('MultiLang', "t2.model='Article' AND t2.foreign_id=t1.id AND t2.field='article_name' AND t2.locale='".$this->getFrontendLocaleId()."'", 'left outer')
		->join('MultiLang', "t3.model='Article' AND t3.foreign_id=t1.id AND t3.field='short_description' AND t3.locale='".$this->getFrontendLocaleId()."'", 'left outer')
		->where('t1.status', ArticleModel::STATUS_ACTIVE)
		->orderBy('t1.id desc')->limit($limit)->findAll()->getData();
		if (!empty($arrs)) {
		    $arrs = UtilComponent::attachCustomLinks($arrs, RouterModel::TYPE_ARTICLE, $this->getFrontendLocaleId());
		}
		$data = array(
			'template' => !empty($this->data['widget']['template'])? $this->data['widget']['template'] : null,
			'article_arr' => $arrs
		);
		if ($renderString) {
			return $this->output($data);
		}
		$this->render($data);
	}
}