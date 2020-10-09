<?php
namespace App\Controllers\Components\Widgets;

use App\Models\WidgetModel;

class SocialLinkWidget extends Widget 
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
		->select('t1.*, t2.content as content')
		->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Widget' AND t2.locale = '" . $this->getFrontendLocaleId() . "' AND t2.field = 'content'", 'left outer' )
		->where('t1.name', $this->name)
		->where('t1.type', WidgetModel::WIDGET_TYPE_SOCIAL_LINK)
		->limit(1)
		->findAll()
		->getData();
		$data = array(
				'template' => !empty($this->data['widget']['template'])? $this->data['widget']['template'] : null,
				'metadata' => !empty($widget[0]['params'])? json_decode($widget[0]['params'], true) : [],
				'widget_id' => @$widget[0]['id'],
				'widget_name' => @$this->data['widget_name']
		);

		if ($renderString) {
			return $this->output($data);
		}
		$this->render($data);
	}
}