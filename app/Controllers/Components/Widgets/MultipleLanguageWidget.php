<?php
namespace App\Controllers\Components\Widgets;

use App\Models\WidgetModel;

class MultipleLanguageWidget extends Widget 
{
	protected $name;
	public function init($data) {
		$this->setLocaleId($data['locale_id']);
		$this->setBaseUrl($data['base_url']);
		$this->data = $data;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	public function run($renderString = false) {
		$widget = WidgetModel::factory()
		->select('t1.*')
		->where('t1.name', $this->name)
		->where('t1.type', WidgetModel::WIDGET_TYPE_MULTIPLE_LANGUAGE)
		->limit(1)
		->findAll()
		->getData();
		$data = array(
				'template' => !empty($this->data['widget']['template'])? $this->data['widget']['template'] : null,
				'metadata' => !empty($widget[0]['params'])? json_decode($widget[0]['params'], true) : [],
				'widget_id' => @$widget[0]['id'],
				'locale_id' => $this->getFrontendLocaleId()
		);

		if ($renderString) {
			return $this->output($data);
		}
		$this->render($data);
	}
}