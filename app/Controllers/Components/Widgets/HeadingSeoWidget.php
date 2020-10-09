<?php
namespace App\Controllers\Components\Widgets;

use App\Models\CmsSettingModel;

class HeadingSeoWidget extends Widget
{
	protected $name;
	public static function factory() {
		return new HeadingSeoWidget();
	}
	public function setName($name) {
		$this->name = $name;
	}
	public function init($data) {
		$this->setLocaleId($data['locale_id']);
		$this->setBaseUrl($data['base_url']);
		$this->data = $data;
	}
	
	public function run($renderString = false) {
		$cmsSettingModel = CmsSettingModel::factory();
		$arr = $cmsSettingModel->findAll()->limit(1)->getData();
		$keywords = array();
		if(!empty($arr)) {
			$keywords = explode(",",$arr[0]['meta_keywords']);	
		}
		
		$data = array(
			'template' => !empty($this->data['widget']['template'])? $this->data['widget']['template'] : null,
			'keywords' => $keywords
		);

		if ($renderString) {
			return $this->output($data);
		}
		$this->render($data);
	}
}