<?php
namespace App\Controllers;

use App\Models\MultiLangModel;
use App\Models\OptionModel;
use App\Plugins\Locale\Models\LocaleModel;
use App\Controllers\Components\UtilComponent;
use App\Models\TaxModel;

class AdminOptionsController extends AdminController
{

	public function DeleteShipping() {
	    $this->setAjax(true);
	    if ($this->isXHR()) {
	        if (! isset($_POST['id']) || empty($_POST['id'])) {
	            AppController::jsonResponse(array(
	                'status' => 'ERR',
	                'code' => 101,
	                'text' => 'Missing or empty parameters.'
	            ));
	        }
	        if (1 == TaxModel::factory()->set('id', $_POST['id'])
	            ->erase()
	            ->getAffectedRows()) {
	                MultiLangModel::factory()->where('model', 'Tax')
	                ->where('foreign_id', $_POST['id'])
	                ->eraseAll();
	                AppController::jsonResponse(array(
	                    'status' => 'OK',
	                    'code' => 200,
	                    'text' => 'Shipping location has been deleted.'
	                ));
	            }
	            AppController::jsonResponse(array(
	                'status' => 'ERR',
	                'code' => 100,
	                'text' => 'Shipping location has not been deleted.'
	            ));
	    }
	    exit();
	}

	public function Index() {

		$this->checkLogin ();
		$tab_id = isset ( $_GET ['tab'] ) && ( int ) $_GET ['tab'] > 0 ? ( int ) $_GET ['tab'] : 1;
		$arr = OptionModel::factory ()->where ( 't1.foreign_id', $this->getForeignId () )->where ( 'tab_id', $tab_id )->orderBy ( 't1.order ASC' )->findAll ()->getData ();
		if (isset ( $_GET ['tab'] ) && in_array ( ( int ) $_GET ['tab'], [5,6])) {
			$locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
			$lp_arr = array ();
			foreach ( $locale_arr as $v ) {
				$lp_arr [$v ['id'] . "_"] = $v ['file'];
			}
			$this->set ( 'lp_arr', $locale_arr );
			$arr = array ();
			$arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $this->getForeignId (), 'Option' );
			$this->set ( 'arr', $arr );
			if (( int ) $this->option_arr ['o_multi_lang'] === 1) {
				$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
				$this->appendJs ( 'jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/' );
			}
		} elseif (isset($_GET['tab']) && in_array((int) $_GET['tab'], array(
		    4
		))) {
		    $tax_arr = TaxModel::factory()->findAll()->getData();
		    foreach ($tax_arr as $k => $v) {
		        $tax_arr[$k]['i18n'] = MultiLangModel::factory()->getMultiLang($v['id'], 'Tax');
		    }
		    $locale_arr = LocaleModel::factory()->select('t1.*, t2.file')
		    ->join('LocaleLanguage', 't2.iso=t1.language_iso', 'left outer')
		    ->where('t2.file IS NOT NULL')
		    ->orderBy('t1.sort ASC')
		    ->findAll()
		    ->getData();
		    $lp_arr = array();
		    foreach ($locale_arr as $v) {
		        $lp_arr[$v['id'] . "_"] = $v['file'];
		    }
		    $this->set('tax_arr', $tax_arr)
		    ->set('lp_arr', $locale_arr)
		    ->set('locale_str', AppController::jsonEncode($lp_arr))
		    ->appendJs('jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/')
		    ->appendJs('jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/')
		    ->appendCss('jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/');
		}
		$tmp = $this->getModel ( 'Option' )->reset ()->where ( 'foreign_id', $this->getForeignId () )->findAll ()->getData ();
		
		$o_arr = array ();
		foreach ( $tmp as $item ) {
			$o_arr [$item ['key']] = $item;
		}

		$this->set ( 'arr', $arr )->set ( 'o_arr', $o_arr )->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' )->appendJs ( 'additional-methods.js', THIRD_PARTY_PATH . 'validate/' )->appendJs ( 'tinymce.min.js', THIRD_PARTY_PATH . 'tiny_mce_4.1.1/' )->appendJs ( 'AdminOptions.js' );

	}

	public function Update() {
		$this->checkLogin ();
		if (isset ( $_POST ['options_update'] )) {
		    if (isset($_POST['tab']) && in_array($_POST['tab'], array(
		        4
		    ))) {
		        $multiLangModel = MultiLangModel::factory();
		        $taxModel = TaxModel::factory();
		        foreach ($_POST['shipping'] as $k => $v) {
		            if (strpos($k, "new_") === 0) {
		                $insert_id = $taxModel->reset()
		                ->setAttributes(array(
		                    'shipping' => $_POST['shipping'][$k],
		                    'free' => $_POST['free'][$k],
		                    'tax' => $_POST['tax'][$k]
		                ))
		                ->insert()
		                ->getInsertId();
		                if ($insert_id !== false && (int) $insert_id > 0) {
		                    if (isset($_POST['i18n'])) {
		                        $tmp = $this->TurnI18n($_POST['i18n'], 'location', $k);
		                        $multiLangModel->reset()->saveMultiLang($tmp, $insert_id, 'Tax');
		                    }
		                }
		            } else {
		                $taxModel->reset()
		                ->set('id', $k)
		                ->modify(array(
		                    'shipping' => $_POST['shipping'][$k],
		                    'free' => $_POST['free'][$k],
		                    'tax' => $_POST['tax'][$k]
		                ));
		                if (isset($_POST['i18n'])) {
		                    $tmp = $this->TurnI18n($_POST['i18n'], 'location', $k);
		                    $multiLangModel->reset()->updateMultiLang($tmp, $k, 'Tax');
		                }
		            }
		        }
		    } elseif (isset ( $_POST ['tab'] ) && in_array ( $_POST ['tab'], array (
					5,
					6 
			) )) {
				if (isset ( $_POST ['i18n'] )) {
					MultiLangModel::factory ()->updateMultiLang ( $_POST ['i18n'], $this->getForeignId (), 'Option', 'data' );
				}
			} else {
				$optionModel = OptionModel::factory ();
				$optionModel->where ( 'foreign_id', $this->getForeignId () )->where ( 'type', 'bool' )->where ( 'tab_id', $_POST ['tab'] )->modifyAll ( array (
						'value' => '1|0::0' 
				) );
				foreach ( $_POST as $key => $value ) {
					if (preg_match ( '/value-(string|text|int|float|enum|bool|color)-(.*)/', $key ) === 1) {
						list ( , , $k ) = explode ( "-", $key );
						if (! empty ( $k )) {
							$optionModel->reset ()->where ( 'foreign_id', $this->getForeignId () )->where ( '`key`', $k )->limit ( 1 )->modifyAll ( array (
									'value' => $value 
							) );
						}
					}
				}
			}
			if (isset ( $_POST ['next_action'] )) {
				switch ($_POST ['next_action']) {
					case 'Index' :
					default :
						$err = 'AO01';
						break;
				}
			}
			UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=AdminOptions&action=" . @$_POST ['next_action'] . "&tab=" . @$_POST ['tab'] . "&err=$err" );
		}
	}

	public function Preview() {

		$this->checkLogin ();
		if ($this->isAdmin ()) {
			$this->appendJs ( 'AdminOptions.js' );
		} else {
			$this->set ( 'status', 2 );
		}
	}

	public function UpdateTheme() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			OptionModel::factory ()->where ( 'foreign_id', $this->getForeignId () )->where ( '`key`', 'o_theme' )->limit ( 1 )->modifyAll ( array (
					'value' => 'theme1|theme2|theme3|theme4|theme5|theme6|theme7|theme8|theme9|theme10::theme' . $_GET ['theme'] 
			) );
		}
	}

	private function TurnI18n($data, $key, $id, $index = NULL) {

		$arr = array ();
		foreach ( $data as $locale => $locale_arr ) {
			$arr [$locale] = array (
					$key => is_null ( $index ) ? (isset ( $locale_arr [$key] ) && isset ( $locale_arr [$key] [$id] ) ? $locale_arr [$key] [$id] : NULL) : (isset ( $locale_arr [$key] ) && isset ( $locale_arr [$key] [$id] ) && isset ( $locale_arr [$key] [$id] [$index] ) ? $locale_arr [$key] [$id] [$index] : NULL) 
			);
		}
		return $arr;
	}
}
?>