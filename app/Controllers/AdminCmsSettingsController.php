<?php
namespace App\Controllers;

use App\Models\CmsSettingModel;
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\ImageComponent;

class AdminCmsSettingsController extends AdminController
{
	public $logoErrors = 'LogoErrors';
	public function Index() {
		$this->checkLogin();
		if (isset($_POST['cms_setting_post']))
		{
			if (isset($_FILES['favicon']) && !empty($_FILES['favicon']['tmp_name']))
			{
				$image = new ImageComponent();
				$image
				->setAllowedExt(array('ico', 'gif'));
				if ($image->load($_FILES['favicon']))
				{
					$hash = md5(uniqid(rand(), true));
					$path = 'app/web/upload/favicon';
					if (!file_exists($path)) {
						mkdir($path, 0777, true);
					}
					$original = $path.'/'. $hash . '.' . $image->getExtension();
					if ($image->save($original))
					{
						$image->loadImage($original);
						$_POST['favicon'] = $original;
					}
				}
			}
			
			$data = array();
			if (isset($_POST['is_maintain']) && $_POST['is_maintain'] == 'T') {
				$data['is_maintain'] = 'T';
			} else {
				$data['is_maintain'] = 'F';
			}
			$cmsSettingModel = CmsSettingModel::factory();
			$arr = $cmsSettingModel->findAll()->limit(1)->getData();
			if(!empty($arr[0])) {
				CmsSettingModel::factory()
				->set('id', $arr[0]['id'])
				->modify(array_merge($_POST, $data));
			} else {
				CmsSettingModel::factory(array_merge($_POST, $data))->insert()->getInsertId();
			}
			
			UtilComponent::redirect($this->baseUrl() . "index.php?controller=AdminCmsSettings&action=Index&err=CMSSETTING003");
		} else {
			$cmsSettingModel = CmsSettingModel::factory();
			$arr = $cmsSettingModel->findAll()->limit(1)->getData();
			if(!empty($arr[0])) {
				$this->set('arr', $arr[0]);
			}
			$this->appendJs('AdminCmsSetting.js');
				
		}
	}
	
	public function DeleteFavicon()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			$cmsSettingModel = CmsSettingModel::factory();

			$arr = $cmsSettingModel->findAll()->limit(1)->getData();
			if (!empty($arr[0]) && !empty($arr[0]['favicon']))
			{
				@clearstatcache();
				if (is_file($arr['favicon']))
				{
					@unlink($arr['favicon']);
				}
				$cmsSettingModel->set('id', $arr[0]['id'])->modify(array('favicon' => ':NULL'));
			}
		}
		exit;
	}	
}
?>