<?php
namespace App\Controllers\Components\Widgets;

use App\Models\RouterModel;
use App\Controllers\Components\UtilComponent;
use App\Models\ArticleModel;

class LastPagesWidget extends Widget
{
	private $name;
	public static function factory() {
		return new LastPagesWidget();
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
		$sectionModel = ArticleModel::factory();
		$limit = isset($this->data['limit'])? $this->data['limit'] : 10;
		$arrs = $sectionModel->select('t1.*, t2.content as `name`, t3.content as `short_description`,  (select count(id) from  `section_viewers`as fv where fv.section_id  = t1.id) as `num_view`')
		->join('MultiLang', "t2.model='Section' AND t2.foreign_id=t1.id AND t2.field='section_name' AND t2.locale='".$this->getFrontendLocaleId()."'", 'left outer')
		->join('MultiLang', "t3.model='Section' AND t3.foreign_id=t1.id AND t3.field='section_short_description' AND t3.locale='".$this->getFrontendLocaleId()."'", 'left outer')
		->orderBy('t1.id desc')->limit($limit)->findAll()->getData();			
		$arrs = UtilComponent::attachCustomLinks($arrs, RouterModel::TYPE_PAGE);
		$data = array(
				'template' => !empty($this->data['widget']['template'])? $this->data['widget']['template'] : null,
				'page_arr' => $arrs
		);
		if ($renderString) {
			return $this->output($data);
		}
		$this->render($data);
	}
}