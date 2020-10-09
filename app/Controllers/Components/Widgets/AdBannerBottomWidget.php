<?php
namespace App\Controllers\Components\Widgets;

use App\Models\BannerModel;

class AdBannerBottomWidget extends Widget 
{
	protected $name;
	public function init($data) {
		$this->setLocaleId($data['locale_id']);
		$this->setBaseUrl($data['base_url']);
		$this->controller = $data['controller'];
		$this->data = $data;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	public function run($renderString = false) {

		$data = [
			'banners' => BannerModel::factory()->where('t1.position', BannerModel::POSITION_BOTTOM)->findAll()->getData()
		];

		if ($renderString) {
			return $this->output($data);
		}
		$this->render($data);
	}
}