<?php
namespace App\Controllers\Components\Widgets;

use App\Models\CmsSettingModel;

class MetaWidget extends Widget 
{
	private $metaWidgetName;
	public function init($data) {
		$this->setLocaleId($data['locale_id']);
		$this->setBaseUrl($data['base_url']);
		$this->controller = $data['controller'];
		$this->data = $data;
	}
	
	public function setName($widgetName) {
		$this->metaWidgetName = $widgetName;
	}
	public function run($renderString = false) {
		if (!empty($this->controller->{$this->metaWidgetName})) {
			$this->render(array(
					'meta_widget' => array(
							$this->metaWidgetName => $this->controller->{$this->metaWidgetName}
					),
					'meta_name' => $this->metaWidgetName
			));			
		} else {
			$meta_widget = CmsSettingModel::factory()
			->limit(1)
			->findAll()
			->getData();
			if (!empty($meta_widget[0]['meta_description'])) {
				$meta_widget[0]['og_description'] = $meta_widget[0]['meta_description'];
			}
			
			$data = array(
					'template' => !empty($this->data['widget']['template'])? $this->data['widget']['template'] : null,
					'meta_widget' => @$meta_widget[0],
					'meta_name' => $this->metaWidgetName
			);

			if ($renderString) {
				return $this->output($data);
			}
			$this->render($data);
		}
	}
}