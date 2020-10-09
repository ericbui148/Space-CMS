<?php
namespace App\Plugins\Country\Controllers;

use App\Plugins\Country\Models\CountryModel;
use App\Models\MultiLangModel;
use App\Controllers\Components\UtilComponent;
use App\Plugins\Locale\Models\LocaleModel;
use App\Controllers\AppController;

class CountryController extends CountryAppController
{

	public function CheckAlpha() {
		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged ()) {
			$CountryModel = CountryModel::factory ();
			if (isset ( $_GET ['alpha_2'] ) && ! empty ( $_GET ['alpha_2'] )) {
				$CountryModel->where ( 't1.alpha_2', $_GET ['alpha_2'] );
			} elseif (isset ( $_GET ['alpha_3'] ) && ! empty ( $_GET ['alpha_3'] )) {
				$CountryModel->where ( 't1.alpha_3', $_GET ['alpha_3'] );
			}
			if (isset ( $_GET ['id'] ) && ( int ) $_GET ['id'] > 0) {
				$CountryModel->where ( 't1.id !=', $_GET ['id'] );
			}
			$cnt = $CountryModel->findCount ()->getData ();
			echo ( int ) $cnt === 0 ? 'true' : 'false';
		}
		exit ();
	}

	public function Create() {
		$this->checkLogin ();
		if (isset ( $_POST ['country_create'] )) {
			$id = CountryModel::factory ( $_POST )->insert ()->getInsertId ();
			if ($id !== false && ( int ) $id > 0) {
				$err = 'PCY03';
				if (isset ( $_POST ['i18n'] )) {
					MultiLangModel::factory ()->saveMultiLang ( $_POST ['i18n'], $id, 'Country' );
				}
			} else {
				$err = 'PCY04';
			}
			UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=Country&action=Index&err=$err" );
		} else {
			$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
			$lp_arr = array ();
			foreach ( $locale_arr as $item ) {
				$lp_arr [$item ['id'] . "_"] = $item ['file'];
			}
			$this->set ( 'lp_arr', $locale_arr );
			$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
			$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
			$this->appendJs ( 'jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/' );
			$this->appendJs ( 'jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/' );
			$this->appendCss ( 'jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/' );
			$this->appendJs ( 'Country.js', $this->getConst ( 'PLUGIN_JS_PATH' ) );
			$this->appendJs ( 'index.php?controller=Admin&action=Messages', $this->baseUrl(), true );
		}
	}

	public function DeleteCountry() {
		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged ()) {
			if (isset ( $_GET ['id'] ) && ( int ) $_GET ['id'] > 0 && CountryModel::factory ()->setAttributes ( array (
					'id' => $_GET ['id'] 
			) )->erase ()->getAffectedRows () == 1) {
				MultiLangModel::factory ()->where ( 'model', 'Country' )->where ( 'foreign_id', $_GET ['id'] )->eraseAll ();
				AppController::jsonResponse ( array (
						'status' => 'OK',
						'code' => 200,
						'text' => '' 
				) );
			}
			AppController::jsonResponse ( array (
					'status' => 'ERR',
					'code' => 100,
					'text' => '' 
			) );
		}
		exit ();
	}

	public function DeleteCountryBulk() {
		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged ()) {
			if (isset ( $_POST ['record'] ) && ! empty ( $_POST ['record'] )) {
				CountryModel::factory ()->whereIn ( 'id', $_POST ['record'] )->eraseAll ();
				MultiLangModel::factory ()->where ( 'model', 'Country' )->whereIn ( 'foreign_id', $_POST ['record'] )->eraseAll ();
				AppController::jsonResponse ( array (
						'status' => 'OK',
						'code' => 200,
						'text' => '' 
				) );
			}
			AppController::jsonResponse ( array (
					'status' => 'ERR',
					'code' => 100,
					'text' => '' 
			) );
		}
		exit ();
	}

	public function GetCountry() {
		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged ()) {
			$CountryModel = CountryModel::factory ()->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Country' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'name'", 'left' );
			if (isset ( $_GET ['q'] ) && ! empty ( $_GET ['q'] )) {
				$q = $CountryModel->escapeString ( $_GET ['q'] );
				$q = str_replace ( array (
						'%',
						'_' 
				), array (
						'\%',
						'\_' 
				), $q );
				$CountryModel->where ( sprintf ( "(t1.alpha_2 LIKE '%1\$s' OR t1.alpha_3 LIKE '%1\$s' OR t2.content LIKE '%1\$s')", "%$q%" ) );
			}
			if (isset ( $_GET ['status'] ) && in_array ( $_GET ['status'], array (
					'T',
					'F' 
			) )) {
				$CountryModel->where ( 't1.status', $_GET ['status'] );
			}
			$column = 'name';
			$direction = 'ASC';
			if (isset ( $_GET ['direction'] ) && isset ( $_GET ['column'] ) && in_array ( strtoupper ( $_GET ['direction'] ), array (
					'ASC',
					'DESC' 
			) )) {
				$column = $_GET ['column'];
				$direction = strtoupper ( $_GET ['direction'] );
			}
			$total = ( int ) $CountryModel->findCount ()->getData ();
			$rowCount = isset ( $_GET ['rowCount'] ) && ( int ) $_GET ['rowCount'] > 0 ? ( int ) $_GET ['rowCount'] : 10;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}
			$data = $CountryModel->select ( 't1.*, t2.content AS name' )->orderBy ( "$column $direction" )->limit ( $rowCount, $offset )->findAll ()->getData ();
			AppController::jsonResponse ( compact ( 'data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction' ) );
		}
		exit ();
	}

	public function Index() {
		$this->checkLogin ();
		$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
		$this->appendJs ( 'Country.js', $this->getConst ( 'PLUGIN_JS_PATH' ) );
		$this->appendJs ( 'index.php?controller=Admin&action=Messages', $this->baseUrl(), true );
	}

	public function SaveCountry() {
		$this->setAjax ( true );
		if ($this->isXHR () && $this->isLoged ()) {
			$CountryModel = CountryModel::factory ();
			if (! in_array ( $_POST ['column'], $CountryModel->getI18n () )) {
				$data = array ();
				if (in_array ( $_POST ['column'], array (
						'alpha_2',
						'alpha_3' 
				) ) && empty ( $_POST ['value'] )) {
					$data [$_POST ['column']] = ':NULL';
				} else {
					$data [$_POST ['column']] = $_POST ['value'];
				}
				$CountryModel->set ( 'id', $_GET ['id'] )->modify ( $data );
			} else {
				MultiLangModel::factory ()->updateMultiLang ( array (
						$this->getLocaleId () => array (
								$_POST ['column'] => $_POST ['value'] 
						) 
				), $_GET ['id'], 'Country' );
			}
		}
		exit ();
	}

	public function Update() {
		$this->checkLogin ();
		if (isset ( $_POST ['country_update'] )) {
			CountryModel::factory ()->where ( 'id', $_POST ['id'] )->limit ( 1 )->modifyAll ( $_POST );
			if (isset ( $_POST ['i18n'] )) {
				MultiLangModel::factory ()->updateMultiLang ( $_POST ['i18n'], $_POST ['id'], 'Country' );
			}
			UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=Country&action=Index&err=PCY01" );
		} else {
			$arr = CountryModel::factory ()->find ( $_GET ['id'] )->getData ();
			if (count ( $arr ) === 0) {
				UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=Country&action=Index&err=PCY08" );
			}
			$arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $arr ['id'], 'Country' );
			$this->set ( 'arr', $arr );
			$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
			$lp_arr = array ();
			foreach ( $locale_arr as $item ) {
				$lp_arr [$item ['id'] . "_"] = $item ['file'];
			}
			$this->set ( 'lp_arr', $locale_arr );
			$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
			$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
			$this->appendJs ( 'jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/' );
			$this->appendJs ( 'jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/' );
			$this->appendCss ( 'jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/' );
			$this->appendJs ( 'Country.js', $this->getConst ( 'PLUGIN_JS_PATH' ) );
			$this->appendJs ( 'index.php?controller=Admin&action=Messages', $this->baseUrl(), true );
		}
	}
}
?>