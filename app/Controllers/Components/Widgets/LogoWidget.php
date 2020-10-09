<?php
namespace App\Controllers\Components\Widgets;

use App\Models\LogoModel;

class LogoWidget extends Widget 
{
	private $logoName;
	protected $params;
	public function init($data) {
		$this->setLocaleId($data['locale_id']);
		$this->setBaseUrl($data['base_url']);
		$this->data = $data;
	}
	
	public function setName($widgetName) {
		$this->logoName = $widgetName;
	}
	public function run($renderString = false) {
		$arr = LogoModel::factory()->findAll()->limit(1)->getData();
		$data = array(
				'template' => !empty($this->data['widget']['template'])? $this->data['widget']['template'] : null,
				'logo' => @$arr[0]['origin_path'],
				'params' => $this->data
		);

		if ($renderString) {
			return $this->output($data);
		}
		$this->render($data);
	}
}