<?php
namespace App\Controllers;

use Core\Framework\Components\ImageComponent;
use App\Models\LogoModel;
use App\Controllers\Components\UtilComponent;

class AdminLogosController extends AdminController 
{
	public $logoErrors = 'LogoErrors';
	public function Index() {
		$this->checkLogin();
		if (isset($_POST['logo_post']))
		{
			if (isset($_FILES['logo']) && !empty($_FILES['logo']['tmp_name']))
			{
				$image = new ImageComponent();
				$image
				->setAllowedExt(array('png', 'gif', 'jpg', 'jpeg', 'jpe', 'jfif', 'jif', 'jfi', 'svg'))
				->setAllowedTypes(array('image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg'));
				if ($image->load($_FILES['logo']))
				{
					$hash = md5(uniqid(rand(), true));
					$date = date('Y/m/d');
					$path = LOGO_UPLOAD_PATH.$date;
					if (!file_exists($path)) {
						mkdir($path, 0777, true);
					}
					$original = $path.'/'. $hash . '.' . $image->getExtension();
					$thumb = $path.'/' . $hash . '_thumb.'.$image->getExtension();
					if ($image->save($original))
					{
						$image->loadImage($original)->resizeSmart(120, 60)->saveImage($thumb);
						$_POST['origin_path'] = $original;
						$_POST['small_path'] = $thumb;

					}
				} else {
					$time = time();
					$_SESSION[$this->logoErrors][$time] = $image->getError();
					UtilComponent::redirect($this->baseUrl() . "index.php?controller=AdminLogos&action=Index&err=LOGO004&errTime=" . $time);
				}
			}
			$data = array();
			$logoModel = LogoModel::factory();
			$arr = $logoModel->findAll()->limit(1)->getData();
			if(!empty($arr[0])) {
				LogoModel::factory()
				->set('id', $arr[0]['id'])
				->modify(array_merge($_POST, $data));				
			} else {
				LogoModel::factory(array_merge($_POST, $data))->insert()->getInsertId();
			}
				
			UtilComponent::redirect($this->baseUrl() . "index.php?controller=AdminLogos&action=Index&err=LOGO003");
		} else {
			$logoModel = LogoModel::factory();
			$arr = $logoModel->findAll()->limit(1)->getData();
			if(!empty($arr[0])) {
				$this->set('arr', $arr[0]);
			}
			$this->appendJs('AdminLogos.js');
			
		}
	}
	
	public function UpdateFrontendLogo() {
		$this->checkLogin();
		$this->setLayout('AdminEmptyLayout');
		if (isset($_POST['logo_post']))
		{
			if (isset($_FILES['logo']) && !empty($_FILES['logo']['tmp_name']))
			{
				$image = new ImageComponent();
				$image
				->setAllowedExt(array('png', 'gif', 'jpg', 'jpeg', 'jpe', 'jfif', 'jif', 'jfi', 'svg'))
				->setAllowedTypes(array('image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg'));
				if ($image->load($_FILES['logo']))
				{
					$hash = md5(uniqid(rand(), true));
					$path = 'app/web/upload/logos';
					if (!file_exists($path)) {
						mkdir($path, 0777, true);
					}
					$original = $path.'/'. $hash . '.' . $image->getExtension();
					$thumb = $path.'/' . $hash . '_thumb.'.$image->getExtension();
					if ($image->save($original))
					{
						$_POST['origin_path'] = $original;
						$_POST['small_path'] = $thumb;

					}
				} else {
					$time = time();
					$_SESSION[$this->logoErrors][$time] = $image->getError();
					UtilComponent::redirect($this->baseUrl() . "index.php?controller=AdminLogos&action=Index&err=LOGO004&errTime=" . $time);
				}
			}
			$data = array();
			$logoModel = LogoModel::factory();
			$arr = $logoModel->findAll()->limit(1)->getData();
			if(!empty($arr[0])) {
				LogoModel::factory()
				->set('id', $arr[0]['id'])
				->modify(array_merge($_POST, $data));				
			} else {
				LogoModel::factory(array_merge($_POST, $data))->insert()->getInsertId();
			}
				
			UtilComponent::redirect($this->baseUrl() . "index.php?controller=AdminLogos&action=Index&err=LOGO003");
		} else {
			$logoModel = LogoModel::factory();
			$arr = $logoModel->findAll()->limit(1)->getData();
			if(!empty($arr[0])) {
				$this->set('arr', $arr[0]);
			}
			$this->appendJs('AdminLogos.js');
			
		}
	}

	public function DeleteLogo()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			$logoModel = LogoModel::factory();
			$arr = $logoModel->findAll()->limit(1)->getData();
			if (!empty($arr[0]) && !empty($arr[0]['logo_path']))
			{
				$logoModel->set('id', $arr[0]['id'])->modify(array('logo_path' => ':NULL'));
			}
		}
		exit;
	}
}
?>