<?php
namespace App\Controllers;

use Core\Framework\Components\ImageComponent;
use App\Models\MenuItemModel;
use App\Models\MultiLangModel;

class AdminMenuItemsController extends AdminController
{
	
	public function Create() {

		// Valid before create
		$dataValid = $_POST;
		$linkTypeValid = $this->getLinkTypeData($_POST['link_type'], $dataValid);
		foreach ( $dataValid['i18n'] as $key=>$val ) {
			if ( empty(trim($val['name'])) ) {
				unset($dataValid['i18n'][$key]);
			}
		}
		// if invalid
		$response = [];
		if ( empty(trim($linkTypeValid['link_data'])) || count($dataValid['i18n']) == 0 ) {
			$response['code'] = 250;
			$response['status'] = 'ERR';
			AppController::jsonResponse ( $response );
		}

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (isset ( $_POST ['menu_item_add'] )) {
				if (isset($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name']))
				{
					$image = new ImageComponent();
					$image
					->setAllowedExt(array('png', 'gif', 'jpg', 'jpeg', 'jpe', 'jfif', 'jif', 'jfi'))
					->setAllowedTypes(array('image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg'));
					if ($image->load($_FILES['avatar']))
					{
						$hash = md5(uniqid(rand(), true));
						$path = 'app/web/upload/menuitem/avatar';
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						$original = $path.'/'. $hash . '.' . $image->getExtension();
						if ($image->save($original))
						{
							$image->loadImage($original);
							$_POST['avatar'] = $original;
						}
					}
				}
				$dataPost = $_POST;
				$dataPost['uuid_code'] = uniqid();
				$dataPost['foreign_id'] = $dataPost['menu_id'];
				$linkTypeArr = $this->getLinkTypeData($_POST['link_type'], $dataPost);
				$id = MenuItemModel::factory ()->saveNode ( array_merge($dataPost, $linkTypeArr), $dataPost['parent_id']);
				if ($id !== false && ( int ) $id > 0) {
					if (isset ( $dataPost ['i18n'] )) {
						MultiLangModel::factory ()->saveMultiLang ( $dataPost ['i18n'], $id, 'MenuItem' );
					}
				}
				$response ['code'] = 200;
				AppController::jsonResponse ( $response );
			} 
		}
	}
	
	protected function getLinkTypeData($linkType, $postDta) {
		$result = array();
		$linkType = (int)$_POST['link_type'];
		switch ($linkType) {
			case MenuItemModel::LINK_TYPE_DEFAULT:
				$result['link_type'] = MenuItemModel::LINK_TYPE_DEFAULT;
				$result['link_data'] = $postDta['link'];
				break;
			case MenuItemModel::LINK_TYPE_SINGLE_ARTICLE:
				$result['link_type'] = MenuItemModel::LINK_TYPE_SINGLE_ARTICLE;
				$result['link_data'] = $postDta['article_id'];	
				break;
			case MenuItemModel::LINK_TYPE_ARTICE_CATEGORY:
				$result['link_type'] = MenuItemModel::LINK_TYPE_ARTICE_CATEGORY;
				$result['link_data'] = $postDta['category_id'];
				break;
			case MenuItemModel::LINK_TYPE_PAGE_CATEGORY :
				$result ['link_type'] = MenuItemModel::LINK_TYPE_PAGE_CATEGORY;
				$result['link_data'] = $postDta['category_id'];
				break;
			case MenuItemModel::LINK_TYPE_PAGE:
				$result['link_type'] = MenuItemModel::LINK_TYPE_PAGE;
				$result['link_data'] = $postDta['page_id'];				
				break;
			case MenuItemModel::LINK_TYPE_PRODUCT:
				$result['link_type'] = MenuItemModel::LINK_TYPE_PRODUCT;
				$result['link_data'] = $postDta['product_id'];				
				break;
			case MenuItemModel::LINK_TYPE_PRODUCT_CATEGORY:
				$result['link_type'] = MenuItemModel::LINK_TYPE_PRODUCT_CATEGORY;
				$result['link_data'] = $postDta['product_category_id'];				
				break;
			case MenuItemModel::LINK_TYPE_TAG:
				$result['link_type'] = MenuItemModel::LINK_TYPE_TAG;
				$result['link_data'] = $postDta['tag_id'];				
				break;
		}
		
		return $result;
	}

	public function DeleteMenuItem() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$response = array ();
			$menuItemModel = MenuItemModel::factory ();
			$menuItemModel->deleteNode ( $_GET ['id'] );
			$menuItemModel->rebuildTree ( 1, 1 );
			$response ['code'] = 200;
			AppController::jsonResponse ( $response );
		}
		exit ();
	}

	public function DeleteMenuItemBulk() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			if (isset ( $_POST ['record'] ) && count ( $_POST ['record'] ) > 0) {
				$menuItemModel = MenuItemModel::factory ();
				$menuItemModel->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
				foreach ( $_POST ['record'] as $id ) {
					$menuItemModel->deleteNode ( $id );
					$menuItemModel->rebuildTree ( 1, 1 );
				}
			}
		}
		exit ();
	}

	public function GetMenuItem() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$menuItemModel = MenuItemModel::factory ();
			$column = 'name';
			$direction = 'ASC';
			if (isset ( $_GET ['direction'] ) && isset ( $_GET ['column'] ) && in_array ( strtoupper ( $_GET ['direction'] ), array (
					'ASC',
					'DESC' 
			) )) {
				$column = $_GET ['column'];
				$direction = strtoupper ( $_GET ['direction'] );
			}
			$menuId = @$_GET['menu_id'];
			$data = $menuItemModel->getNode ( $this->getLocaleId (), 1, $menuId );
			$total = count ( $data );
			$rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 10;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}
			$data = array_slice ( $data, $offset, $rowCount );
			$stack = array ();
			foreach ( $data as $k => $MenuItem ) {
				//$data [$k] ['products'] = ( int ) @$c_arr [$MenuItem ['data'] ['id']];
				$data [$k] ['up'] = 0;
				$data [$k] ['down'] = 0;
				$data [$k] ['id'] = ( int ) $MenuItem ['data'] ['id'];
				if (! isset ( $stack [$MenuItem ['deep'] . "|" . $MenuItem ['data'] ['parent_id']] )) {
					$stack [$MenuItem ['deep'] . "|" . $MenuItem ['data'] ['parent_id']] = 0;
				}
				$stack [$MenuItem ['deep'] . "|" . $MenuItem ['data'] ['parent_id']] += 1;
				if ($stack [$MenuItem ['deep'] . "|" . $MenuItem ['data'] ['parent_id']] > 1) {
					$data [$k] ['up'] = 1;
				}
				if (isset ( $data [$k + 1] ) && $data [$k + 1] ['deep'] == $MenuItem ['deep'] || $stack [$MenuItem ['deep'] . "|" . $MenuItem ['data'] ['parent_id']] < $MenuItem ['siblings']) {
					$data [$k] ['down'] = 1;
				}
			}
			AppController::jsonResponse ( compact ( 'data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction' ) );
		}
		exit ();
	}

	public function Index() {
		$this->checkLogin ();
		if ($this->isAdmin ()) {
			$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
			$this->appendJs ( 'AdminMenuItems.js' );
			$this->appendJs ( 'index.php?controller=AdminStock&action=Messages', $this->baseUrl(), true );
		} else {
			$this->set ( 'status', 2 );
		}
	}

	public function SetOrder() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$menuItemModel = MenuItemModel::factory ();
			$node = $menuItemModel->find ( $_POST ['id'] )->getData ();
			if (count ( $node ) > 0) {
				$menuItemModel->reset ();
				switch ($_POST ['direction']) {
					case 'up' :
						$menuItemModel->where ( 't1.lft <', $node ['lft'] )->orderBy ( 't1.lft DESC' );
						break;
					case 'down' :
						$menuItemModel->where ( 't1.lft >', $node ['lft'] )->orderBy ( 't1.lft ASC' );
						break;
				}
				$neighbour = $menuItemModel->where ( 't1.id !=', $node ['id'] )->where ( 't1.parent_id', $node ['parent_id'] )->where('t1.foreign_id', $node ['foreign_id'])->limit ( 1 )->findAll ()->getData ();
				if (count ( $neighbour ) === 1) {
					$neighbour = $neighbour [0];
					$menuItemModel->reset ()->set ( 'id', $neighbour ['id'] )->modify ( array (
							'lft' => $node ['lft'],
							'rgt' => $node ['rgt'] 
					) );
					$menuItemModel->reset ()->set ( 'id', $node ['id'] )->modify ( array (
							'lft' => $neighbour ['lft'],
							'rgt' => $neighbour ['rgt'] 
					) );
					$menuItemModel->reset ()->rebuildTree ( 1, 1 );
				} else {
				}
			}
		}
		exit ();
	}

	public function Update() {

		$this->setAjax ( true );
		if ($this->isAdmin ()) {
			$menuItemModel = MenuItemModel::factory ();
			if (isset ( $_POST ['menu_item_edit'] )) {
				if($_POST['id'] == $_POST['parent_id']) {
				    $response = [];
					$response ['code'] = 250;
					$response['status'] = 'ERR';
					AppController::jsonResponse ( $response );					
				}
				if (isset($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name']))
				{
					$image = new ImageComponent();
					$image
					->setAllowedExt(array('png', 'gif', 'jpg', 'jpeg', 'jpe', 'jfif', 'jif', 'jfi'))
					->setAllowedTypes(array('image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg'));
					if ($image->load($_FILES['avatar']))
					{
						$hash = md5(uniqid(rand(), true));
						$path = 'app/web/upload/menuitem/avatar';
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						$original = $path.'/'. $hash . '.' . $image->getExtension();
						if ($image->save($original))
						{
							$image->loadImage($original);
							$_POST['avatar'] = $original;
						}
					}
				}
				$linkTypeArr = $this->getLinkTypeData($_POST['link_type'], $_POST);
				$menuItemModel->updateNode ( array_merge ( $_POST, $linkTypeArr ) );
				if (isset ( $_POST ['i18n'] )) {
					MultiLangModel::factory ()->updateMultiLang ( $_POST ['i18n'], $_POST ['id'], 'MenuItem' );
				}
				$response ['code'] = 200;
				AppController::jsonResponse ( $response );			}
		} else {
			$this->set ( 'status', 2 );
		}
	}
	
	public function DeleteAvatar()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{
			$menuItemModel = MenuItemModel::factory();
			$menuItemId = $_POST['menu_item_id'];
			$arr = $menuItemModel->find($menuItemId)->getData();
			if (!empty($arr) && !empty($arr['avatar']))
			{
				@clearstatcache();
				if (is_file($arr['avatar']))
				{
					@unlink($arr['avatar']);
				}
				$menuItemModel->set('id', $arr['id'])->modify(array('avatar' => ':NULL'));
			}
		}
		exit;
	}
}
?>