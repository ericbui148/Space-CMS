<?php
namespace App\Controllers\Components;

use App\Controllers\Components\Widgets\WidgetFactory;
use App\Models\WidgetModel;
use Core\Framework\Components\Component;

class WidgetRenderComponent extends Component
{
    protected $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }
	public function output($widgetName) {
        $widgetRecord = WidgetModel::factory()->where('t1.name', $widgetName)->findAll()->limit(1)->getData();
		if (empty($widgetRecord)) return '';
		$type = $widgetRecord[0]['type'];
        $widget = WidgetFactory::getInstance($type);
		if(!is_null($widgetName)) {
			$widget->setName($widgetName);
		}
		$params = array(
		    'locale_id' => $this->controller->getFrontendLocaleId() == NULL? 1 : $this->controller->getFrontendLocaleId(),
			'base_url' => $this->controller->baseUrl(),
			'option_arr' => $this->controller->tpl['option_arr'],
            'controller' => $this->controller
		);
		$widget->init($params);

		return $widget->run(true);
		
	}
}