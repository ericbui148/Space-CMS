<?php
namespace App\Controllers\Components\Widgets;

use App\Models\WidgetModel;
use App\Controllers\Components\ShortCodeComponent;

class HtmlWidget extends Widget 
{
	private $htmlWidgetName;
	public function init($data) {
		$this->setLocaleId($data['locale_id']);
		$this->setBaseUrl($data['base_url']);
		$this->controller = $data['controller'];
		$this->data = $data;
	}
	
	public function setName($widgetName) {
		$this->htmlWidgetName = $widgetName;
	}
	public function run($renderString = false) {
		$widget = WidgetModel::factory()
		->select('t1.*, t2.content as content')
		->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Widget' AND t2.locale = '" . $this->getFrontendLocaleId() . "' AND t2.field = 'content'", 'left outer' )
		->where('t1.name', $this->htmlWidgetName)
		->where('t1.type', WidgetModel::WIDGET_TYPE_HTML)
		->limit(1)
		->findAll()
		->getData();
		if (!empty($widget[0]['content'])) {
			$Shortcode = new ShortCodeComponent($this->controller);
			$widget[0]['content'] = $Shortcode->doShortCode($widget[0]['content']);
		}

		$data = array(
				'template' => !empty($this->data['widget']['template'])? $this->data['widget']['template'] : null,
				'html' => @$widget[0]['content'],
				'widget_id' => @$widget[0]['id']
		);

		if ($renderString) {
			return $this->output($data);
		}
		$this->render($data);
	}
}