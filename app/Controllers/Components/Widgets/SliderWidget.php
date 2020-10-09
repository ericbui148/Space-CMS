<?php
namespace App\Controllers\Components\Widgets;

use App\Models\WidgetModel;
use App\Plugins\Gallery\Models\GalleryModel;

class SliderWidget extends Widget 
{
	public function init($data) {
		if(isset($data['locale_id'])) {
			$this->setLocaleId($data['locale_id']);
		}
		
		if(isset($data['base_url'])) {
			$this->setBaseUrl($data['base_url']);
		}
		
		if(isset($data['class'])) {
			$this->setClass($data['class']);
		}
		$this->data = $data;
		$this->controller = $data['controller'];
	}
	
	
	public function run($renderString = false) {
		$widgetRecord = WidgetModel::factory()
			->where('t1.name', $this->name)
			->where('t1.type', WidgetModel::WIDGET_TYPE_SLIDER)
			->where('t1.status', 'T')->findAll()->limit(1)->getData();
		$galleryArr = array();
		if(!empty($widgetRecord[0])) {
			$galleryArr = GalleryModel::factory()
			->select("t1.*, t2.content as `title`, t3.content as `description`")
			->join ( 'MultiLang', "t2.model='Gallery' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='" . $this->getFrontendLocaleId() . "'", 'left outer' )
			->join ( 'MultiLang', "t3.model='Gallery' AND t3.foreign_id=t1.id AND t3.field='description' AND t3.locale='" . $this->getFrontendLocaleId() . "'", 'left outer' )
			->where('t1.foreign_id', (int)$widgetRecord[0]['value'])
			->orderBy('t1.sort asc')
			->findAll()
			->getData();

		}
		$data = array(
		        'template' => !empty($widgetRecord[0]['template'])? $widgetRecord[0]['template'] : null,
		        'metadata' => !empty($widgetRecord[0]['params'])? json_decode($widgetRecord[0]['params'], true) : [],
				'gallery_arr' => $galleryArr,
				'slider_id' => (int)@$widgetRecord[0]['value'],
				'class' => $this->class
		);

		$data = array_merge($data, $this->data);

		if ($renderString) {
			return $this->output($data);
		}
		$this->render($data);
	}
		
}