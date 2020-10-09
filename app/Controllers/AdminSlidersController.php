<?php
namespace App\Controllers;

use App\Controllers\Components\UtilComponent;
use App\Models\SliderModel;
use App\Models\MultiLangModel;
use App\Plugins\Gallery\Models\GalleryModel;
use Core\Framework\Objects;

class AdminSlidersController extends AdminController
{
	public function Create()
	{
		$this->checkLogin();
		$post_max_size = UtilComponent::getPostMaxSize();
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > $post_max_size)
		{
			UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminSliders&action=Index&err=AS12");
		}
		
		if (isset($_POST['slider_create']))
		{
			$data = array();
			$err = 'SLI02';
			$data['uuid_code'] = uniqid();
			$id = SliderModel::factory(array_merge($_POST, $data))->insert()->getInsertId();
			if ($id !== false && (int) $id > 0)
			{		
				if (isset($_POST['i18n']))
				{
					MultiLangModel::factory()->saveMultiLang($_POST['i18n'], $id, 'Slider');
				}
			} else {
				$err = 'SLI03';
			}
			if($err == 'SLI02' || $err == 'SLI03')
			{
				UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminSliders&action=Index&err=$err");
			}else{
				UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminSliders&action=Update&id=$id&err=$err");
			}
		} else {
					
			$this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('additional-methods.js', THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/');
			$this->appendCss('jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/');
			$this->appendJs ( 'chosen.jquery.js', THIRD_PARTY_PATH . 'harvest/chosen/' );
			$this->appendCss ( 'chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
			
			$this->appendJs('AdminSliders.js');
		}
	}
	
	public function DeleteSlider()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			if (isset($_GET['id']) && (int) $_GET['id'] > 0 && SliderModel::factory()->set('id', $_GET['id'])->erase()->getAffectedRows() == 1)
			{
				GalleryModel::factory()->where('foreign_id', $_GET['id'])->eraseAll();
				AppController::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => ''));
			}
			AppController::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => ''));
		}
		exit;
	}
	
	public function DeleteSliderBulk()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			if (isset($_POST['record']) && !empty($_POST['record']))
			{
				SliderModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
				MultiLangModel::factory()->where('model', 'Slider')->whereIn('foreign_id', $_POST['record'])->eraseAll();
				AppController::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => ''));
			}
			AppController::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => ''));
		}
		exit;
	}
	
	public function GetSlider()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			$sliderModel = SliderModel::factory()
				->join('MultiLang', "t2.model='Slider' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer');				
			if (isset($_GET['q']) && !empty($_GET['q']))
			{
				$q = str_replace(array('_', '%'), array('\_', '\%'), trim($_GET['q']));
				$sliderModel->where('t2.content LIKE', "%$q%");
			}

			if (isset($_GET['is_active']) && strlen($_GET['is_active']) > 0 && in_array($_GET['is_active'], array(1, 0)))
			{
				$sliderModel->where('t1.status', $_GET['is_active']);
			}

			$column = 'id';
			$direction = 'DESC';
			if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
			{
				$column = $_GET['column'];
				$direction = strtoupper($_GET['direction']);
			}

			$total = $sliderModel->findCount()->getData();
			$rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
			$pages = ceil($total / $rowCount);
			$page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
			$offset = ((int) $page - 1) * $rowCount;
			if ($page > $pages)
			{
				$page = $pages;
			}

			$data = $sliderModel
			->select('t1.*')
			->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
				
			AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
		}
		exit;
	}
	
	public function Index()
	{
		$this->checkLogin();
		$this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
		$this->appendJs('AdminSliders.js');
		$this->appendJs('index.php?controller=Admin&action=Messages', $this->baseUrl(), true);
	}
	
	public function SaveSlider()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			$sliderModel = SliderModel::factory();
			if (!in_array($_POST['column'], $sliderModel->getI18n()))
			{
				$sliderModel->set('id', $_GET['id'])->modify(array($_POST['column'] => $_POST['value']));
			} else {
				MultiLangModel::factory()->updateMultiLang(array($this->getLocaleId() => array($_POST['column'] => $_POST['value'])), $_GET['id'], 'Slider', 'data');
			}
		}
		exit;
	}
	
	public function Update()
	{
		$this->checkLogin();
	
		if (isset($_POST['slider_update']) && isset($_POST['id']) && (int) $_POST['id'] > 0)
		{
			$err = 'SLI06';
			$data = array();
			$postData = $_POST;				
			SliderModel::factory()->set('id', $postData['id'])->modify(array_merge($postData, $data));
			
			if($err == 'SLI06')
			{
				UtilComponent::redirect($this->baseUrl() . "index.php?controller=AdminSliders&action=Index&err=ADEP06");
			}else{
				UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=AdminSliders&action=Update&id=".$postData['id']."&err=$err");
			}
			
		} else {
			$arr = SliderModel::factory()->find($_GET['id'])->getData();
			if (empty($arr))
			{
				UtilComponent::redirect($this->baseUrl(). "index.php?controller=AdminSliders&action=Index&err=ADEP07");
			}
			$this->set('arr', $arr);
			$this->appendCss('gallery.css', Objects::getConstant('Gallery', 'PLUGIN_CSS_PATH'));
			$this->appendJs('ajaxupload.js', Objects::getConstant('Gallery', 'PLUGIN_JS_PATH'));
			$this->appendJs('jquery.gallery.js', Objects::getConstant('Gallery', 'PLUGIN_JS_PATH'));
			$this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
			$this->appendJs ( 'jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/' );
			$this->appendJs('additional-methods.js', THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('AdminSliders.js');
		}
	}
	
}
?>