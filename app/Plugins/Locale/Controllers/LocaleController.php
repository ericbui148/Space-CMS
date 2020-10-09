<?php
namespace App\Plugins\Locale\Controllers;

use App\Models\OptionModel;
use App\Plugins\Locale\Models\LocaleLanguageModel;
use App\Models\FieldModel;
use App\Models\MultiLangModel;
use App\Plugins\Locale\Models\LocaleModel;
use App\Controllers\AppController;
use Core\Framework\Components\UploadComponent;
use App\Controllers\Components\UtilComponent;
use App\Models\AppModel;
use Core\Framework\Components\ToolkitComponent;

class LocaleController extends LocaleAppController {
	public $LocaleKey = 'LocaleKey';
	
	public function CreateTranslation()
	{
	    $this->checkLogin();
	    if (isset($_POST['locale_create_translation']))
	    {
	        $id = FieldModel::factory([
	            'key' => $_POST['key'],
	            'type' => 'backend',
	            'label' => $_POST['label'],
	            'source' => 'script',
	            'modified' => date('Y-m-d i:m:s')
	        ])->insert()->getInsertId();
	        if (!empty($id)) {
	            if (isset($_POST['i18n'])) {
	                MultiLangModel::factory()->saveMultiLang($_POST['i18n'], $id, 'Field', 'script');
	            }
	        }
	        UtilComponent::redirect($_SERVER['PHP_SELF'] . "?controller=Locale&action=Index");
	    } else {
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
	        $this->set('lp_arr', $locale_arr);
	        $this->set('locale_str', AppController::jsonEncode($lp_arr));
	        $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
	        $this->appendJs('jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/');
	        $this->appendJs('jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/');
	        $this->appendCss('jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/');
	        $this->appendJs ( 'Locale.js', $this->getConst ( 'PLUGIN_JS_PATH' ) );
	    }
	}
	
	private function UpdateFieldsIndex() {
		return OptionModel::factory ()->where ( '`key`', 'o_fields_index' )->limit ( 1 )->modifyAll ( array (
				'value' => md5 ( uniqid ( rand (), true ) ) 
		) )->getAffectedRows ();
	}
	public function Locales($force = false) {
		$this->checkLogin ();
		if (isset ( $this->option_arr ['o_multi_lang'] ) && ( int ) $this->option_arr ['o_multi_lang'] === 1) {
			$this->set ( 'language_arr', LocaleLanguageModel::factory ()->where ( 't1.file IS NOT NULL' )->orderBy ( 't1.title ASC' )->findAll ()->getData () );
			$this->appendJs ( 'jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/' );
			$this->appendJs ( 'Locale.js', $this->getConst ( 'PLUGIN_JS_PATH' ) );
			$this->appendJs ( 'index.php?controller=Admin&action=Messages', $this->baseUrl(), true );
		} else {
			$this->set ( 'status', 3 );
			return;
		}
	}
	public function SaveFields() {
		$this->checkLogin ();
		if (isset ( $_POST ['i18n'] ) && count ( $_POST ['i18n'] ) > 0) {
			$fieldModel = FieldModel::factory ();
			$MultiLangModel = MultiLangModel::factory ();
			$MultiLangModel->begin ();
			foreach ( $_POST ['i18n'] as $locale_id => $arr ) {
				foreach ( $arr as $foreign_id => $locale_arr ) {
					$data = array ();
					$data [$locale_id] = array ();
					foreach ( $locale_arr as $name => $content ) {
						$data [$locale_id] [$name] = $content;
					}
					$fids = $MultiLangModel->updateMultiLang ( $data, $foreign_id, 'Field' );
					if (! empty ( $fids )) {
						$fieldModel->reset ()->whereIn ( 'id', $fids )->limit ( count ( $fids ) )->modifyAll ( array (
								'modified' => ':NOW()' 
						) );
					}
				}
			}
			$MultiLangModel->commit ();
			$this->UpdateFieldsIndex ();
		}
		UtilComponent::redirect ( sprintf ( "%sindex.php?controller=Locale&action=%s&err=PAL01&tab=1&q=%s&locale=%u&page=%u", $this->baseUrl(), $_POST ['next_action'], urlencode ( $_POST ['q'] ), $_POST ['locale'], $_POST ['page'] ) );
		exit ();
	}
	private function CheckDefault() {
		if (0 == LocaleModel::factory ()->where ( 'is_default', 1 )->findCount ()->getData ()) {
			LocaleModel::factory ()->limit ( 1 )->modifyAll ( array (
					'is_default' => 1 
			) );
		}
	}
	public function DeleteLocale() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$response = array ();
			if (LocaleModel::factory ()->setAttributes ( array (
					'id' => $_GET ['id'] 
			) )->erase ()->getAffectedRows () == 1) {
				MultiLangModel::factory ()->where ( 'locale', $_GET ['id'] )->eraseAll ();
				$this->UpdateFieldsIndex ();
				$response ['code'] = 200;
				$this->CheckDefault ();
			} else {
				$response ['code'] = 100;
			}
			AppController::jsonResponse ( $response );
		}
		exit ();
	}
	public function GetLocale() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$LocaleModel = LocaleModel::factory ();
			$column = 't1.sort';
			$direction = 'ASC';
			if (isset ( $_GET ['direction'] ) && isset ( $_GET ['column'] ) && in_array ( strtoupper ( $_GET ['direction'] ), array (
					'ASC',
					'DESC' 
			) )) {
				$column = $_GET ['column'];
				$direction = strtoupper ( $_GET ['direction'] );
			}
			$total = $LocaleModel->findCount ()->getData ();
			$rowCount = 100;
			$pages = ceil ( $total / $rowCount );
			$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? intval ( $_GET ['page'] ) : 1;
			$offset = (( int ) $page - 1) * $rowCount;
			if ($page > $pages) {
				$page = $pages;
			}
			$data = $LocaleModel->select ( 't1.*, t2.title, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->orderBy ( "$column $direction" )->findAll ()->getData ();
			AppController::jsonResponse ( compact ( 'data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction' ) );
		}
		exit ();
	}
	public function ImportExport() {
		$this->checkLogin ();
		$LocaleModel = LocaleModel::factory ()->select ( 't1.*, t2.title' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso' );
		if (! isset ( $this->option_arr ['o_multi_lang'] ) || ( int ) $this->option_arr ['o_multi_lang'] === 0) {
			$LocaleModel->where ( 't1.is_default', 1 );
		}
		$this->set ( 'locale_arr', $LocaleModel->orderBy ( 't1.sort ASC' )->findAll ()->getData () );
	}
	public function ImportConfirm() {
		$this->checkLogin ();
		$LocaleModel = LocaleModel::factory ()->select ( 't1.*, t2.title' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso' );
		if (! isset ( $this->option_arr ['o_multi_lang'] ) || ( int ) $this->option_arr ['o_multi_lang'] === 0) {
			$LocaleModel->where ( 't1.is_default', 1 );
		}
		$locale_arr = $LocaleModel->orderBy ( 't1.sort ASC' )->findAll ()->getDataPair ( 'id' );
		$columns = count ( $locale_arr ) + 2;
		if (isset ( $_POST ['import'] )) {
			if (isset ( $_FILES ['file'], $_POST ['separator'] )) {
				$UploadComponent = new UploadComponent();
				$UploadComponent->setAllowedExt ( array (
						'csv',
						'txt' 
				) );
				$UploadComponent->setAllowedTypes ( array (
						'text/csv',
						'application/vnd.ms-excel',
						'application/octet-stream' 
				) );
				if ($UploadComponent->load ( $_FILES ['file'] )) {
					if (($handle = fopen ( $UploadComponent->getFile ( 'tmp_name' ), "rb" )) !== FALSE) {
						$separators = array (
								'comma' => ",",
								'semicolon' => ";",
								'tab' => "\t" 
						);
						$separator = $separators [$_POST ['separator']];
						$field_arr = FieldModel::factory ()->findAll ()->getDataPair ( 'id', 'key' );
						$i = 1;
						$prev_cnt = 0;
						$header = array ();
						while ( ($data = fgetcsv ( $handle, 0, $separator )) !== FALSE ) {
							if (! empty ( $data )) {
								$nl = preg_grep ( '/\r\n|\n/', $data );
								if (! empty ( $nl )) {
									$err = 'PAL14';
									break;
								}
								$cnt = count ( $data );
								if ($cnt <= 2) {
									$err = 'PAL15';
									break;
								}
								if ($prev_cnt > 0 && $cnt != $prev_cnt) {
									$err = 'PAL16';
									break;
								}
								if ($i > 1 && isset ( $id, $key ) && $id !== FALSE && $key !== FALSE) {
									if (! preg_match ( '/^\d+$/', $data [$id] ) || ! preg_match ( '/^[\w\-]+$/', $data [$key] )) {
										$err = 'PAL19';
										break;
									}
								} else {
									$header = $data;
									$id = array_search ( 'id', $data );
									$key = array_search ( 'key', $data );
									if ($id === FALSE || $key === FALSE) {
										$err = 'PAL18';
										break;
									}
								}
								$prev_cnt = $cnt;
								$i += 1;
							} else {
								$err = 'PAL17';
								break;
							}
						}
						fclose ( $handle );
					} else {
						$err = 'PAL13';
					}
				} else {
					$err = 'PAL12';
				}
			} else {
				$err = 'PAL11';
			}
			if (! isset ( $err )) {
				$locales = array ();
				foreach ( $header as $k => $col ) {
					if (in_array ( $k, array (
							$id,
							$key 
					) )) {
						continue;
					}
					list ( $locales [], ) = explode ( '::', $col );
				}
				$key = md5 ( uniqid ( rand (), true ) );
				$dest = UPLOAD_PATH . $key . ".csv";
				if ($UploadComponent->save ( $dest )) {
					$_SESSION [$key] = array (
							'name' => $dest,
							'separator' => $_POST ['separator'],
							'locales' => $locales 
					);
					$err = 'PAL20&key=' . $key;
				} else {
					$err = 'PAL20';
				}
			}
			UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=Locale&action=ImportConfirm&tab=1&err=" . $err );
		}
		$this->set ( 'locale_arr', $locale_arr );
	}
	public function Imports() {
		$this->setAjax ( true );
		$this->setLayout ( 'Empty' );
		$err = 'PAL02';
		if (isset ( $_POST ['import'] ) && $this->isLoged () && $this->isAdmin ()) {
			@set_time_limit ( 600 );
			if (isset ( $_POST ['key'], $_POST ['locale'], $_SESSION [$_POST ['key']], $_SESSION [$_POST ['key']] ['name'], $_SESSION [$_POST ['key']] ['separator'] ) && ! empty ( $_POST ['locale'] ) && ! empty ( $_POST ['key'] ) && ! empty ( $_SESSION [$_POST ['key']] ['name'] ) && ! empty ( $_SESSION [$_POST ['key']] ['separator'] )) {
				if (($handle = fopen ( $_SESSION [$_POST ['key']] ['name'], "rb" )) !== FALSE) {
					$multiLangModel = MultiLangModel::factory ();
					$multi_lang_arr = $multiLangModel->select ( 't1.locale, t1.id AS `mid`, t2.id, t2.key' )->join ( 'Field', 't2.id=t1.foreign_id', 'inner' )->where ( 't1.model', 'Field' )->where ( 't1.field', 'title' )->whereIn ( 't1.locale', $_POST ['locale'] )->where ( 't1.source !=', 'data' )->findAll ()->getData ();
					if (empty ( $multi_lang_arr )) {
						exit ();
					}
					$import_arr = array ();
					foreach ( $multi_lang_arr as $k => $item ) {
						if (! isset ( $import_arr [$item ['id']] )) {
							$import_arr [$item ['id']] = array (
									'key' => $item ['key'],
									'locales' => array () 
							);
						}
						$import_arr [$item ['id']] ['locales'] [$item ['locale']] = $item ['mid'];
					}
					if (empty ( $import_arr )) {
						exit ();
					}
					$separators = array (
							'comma' => ",",
							'semicolon' => ";",
							'tab' => "\t" 
					);
					$separator = $separators [$_SESSION [$_POST ['key']] ['separator']];
					$multiLangModel->debug ( true )->reset ()->begin ();
					$i = 1;
					while ( ($data = fgetcsv ( $handle, 0, $separator )) !== FALSE ) {
						if (! empty ( $data )) {
							if ($i > 1 && isset ( $id, $key, $locales ) && ! empty ( $locales ) && $id !== FALSE && $key !== FALSE && isset ( $import_arr [$data [$id]] )) {
								foreach ( $import_arr [$data [$id]] ['locales'] as $locale_id => $mid ) {
									if (($index = array_search ( $locale_id, $locales )) !== FALSE) {
										$multiLangModel->set ( 'id', $mid )->modify ( array (
												'content' => $data [$index] 
										) );
									}
								}
							} else {
								$id = array_search ( 'id', $data );
								$key = array_search ( 'key', $data );
								if ($id !== FALSE && $key !== FALSE) {
									$locales = array ();
									foreach ( $data as $k => $col ) {
										if (in_array ( $k, array (
												$id,
												$key 
										) )) {
											continue;
										}
										list ( $loc, ) = explode ( '::', $col );
										$locales [$k] = $loc;
									}
								}
							}
							$i += 1;
						}
					}
					fclose ( $handle );
					@unlink ( $_SESSION [$_POST ['key']] ['name'] );
					if ($i > 1) {
						$multiLangModel->commit ();
						$this->UpdateFieldsIndex ();
						$err = 'PAL03';
					} else {
						$err = 'PAL04';
					}
				} else {
					$err = 'PAL05';
				}
			}
		}
		UtilComponent::redirect ( $this->baseUrl() . "index.php?controller=Locale&action=ImportExport&tab=1&err=" . $err );
	}
	public function Exports() {
		$this->setAjax ( true );
		$this->setLayout ( 'Empty' );
		if (isset ( $_POST ['export'] ) && isset ( $_POST ['separator'] ) && $this->isLoged () && $this->isAdmin ()) {
			@set_time_limit ( 600 );
			$name = 'Locale-' . time ();
			$AppModel = AppModel::factory ();
			$fieldModel = FieldModel::factory ();
			$multiLangModel = MultiLangModel::factory ();
			$LocaleModel = LocaleModel::factory ()->select ( 't1.*, t2.title' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso' );
			if (! isset ( $this->option_arr ['o_multi_lang'] ) || ( int ) $this->option_arr ['o_multi_lang'] === 0) {
				$LocaleModel->where ( 't1.is_default', 1 );
			}
			$locale_arr = $LocaleModel->orderBy ( 't1.sort ASC' )->findAll ()->getDataPair ( 'id' );
			if (empty ( $locale_arr )) {
				exit ();
			}
			$multi_lang_arr = $multiLangModel->select ( 't1.locale, t1.content, t2.id, t2.key' )->join ( 'Field', 't2.id=t1.foreign_id', 'left outer' )->where ( 't1.model', 'Field' )->where ( 't1.field', 'title' )->whereIn ( 't1.locale', array_keys ( $locale_arr ) )->where ( 't1.source !=', 'data' )->findAll ()->getData ();
			if (empty ( $multi_lang_arr )) {
				exit ();
			}
			$export_arr = array ();
			foreach ( $multi_lang_arr as $k => $item ) {
				if (! isset ( $export_arr [$item ['id']] )) {
					$export_arr [$item ['id']] = array (
							'key' => $item ['key'],
							'locales' => array () 
					);
				}
				$export_arr [$item ['id']] ['locales'] [$item ['locale']] = $item ['content'];
			}
			$csv = array ();
			$separators = array (
					'comma' => ",",
					'semicolon' => ";",
					'tab' => "\t" 
			);
			$separator = $separators [$_POST ['separator']];
			$header = array (
					'id',
					'key' 
			);
			foreach ( $locale_arr as $id => $data ) {
				$header [] = $id . '::' . $data ['title'];
			}
			$csv [] = join ( $separator, $header );
			foreach ( $export_arr as $id => $data ) {
				$cells = array ();
				$cells [] = '"' . ( int ) $id . '"';
				$cells [] = '"' . str_replace ( array (
						"\r\n",
						"\n",
						"\t",
						'"' 
				), array (
						'\n',
						'\n',
						'\t',
						'""' 
				), $data ['key'] ) . '"';
				foreach ( $locale_arr as $locale_id => $item ) {
					if (isset ( $data ['locales'] [$locale_id] )) {
						$cells [] = '"' . str_replace ( array (
								"\r\n",
								"\n",
								"\t",
								'"' 
						), array (
								'\n',
								'\n',
								'\t',
								'""' 
						), $data ['locales'] [$locale_id] ) . '"';
					} else {
						$cells [] = '""';
					}
				}
				$csv [] = "\n";
				$csv [] = join ( $separator, $cells );
			}
			$content = join ( "", $csv );
			ToolkitComponent::download ( $content, $name . '.csv' );
		}
		exit ();
	}
	public function Index() {
		$this->checkLogin ();
		if (isset ( $_POST ['lang_show_id'] )) {
			if (isset ( $_POST ['show_id'] )) {
				$_SESSION ['lang_show_id'] = 1;
			} else {
				$_SESSION ['lang_show_id'] = 0;
			}
			$this->UpdateFieldsIndex ();
		}
		$this->set ( 'field_arr', FieldModel::factory ()->findAll ()->getDataPair ( 'id', 'label' ) );
		$LocaleModel = LocaleModel::factory ()->select ( 't1.*, t2.title, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' );
		if (! isset ( $this->option_arr ['o_multi_lang'] ) || ( int ) $this->option_arr ['o_multi_lang'] === 0) {
			$LocaleModel->where ( 't1.is_default', 1 );
		}
		$locale_arr = $LocaleModel->orderBy ( "is_default DESC, t1.id ASC" )->findAll ()->getData ();
		$lp_arr = array ();
		foreach ( $locale_arr as $item ) {
			$lp_arr [$item ['id'] . "_"] = $item ['file'];
		}
		$this->set ( 'lp_arr', $locale_arr );
		$this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
		$fieldModel = FieldModel::factory ()->join ( 'MultiLang', "t2.model='Field' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getLocaleId () . "'", 'left' );
		if (isset ( $_GET ['q'] ) && ! empty ( $_GET ['q'] )) {
			$q = $fieldModel->escapeStr ( trim ( $_GET ['q'] ) );
			$q = str_replace ( array (
					'%',
					'_' 
			), array (
					'\%',
					'\_' 
			), $q );
			$id_arr = array ();
			$_arr = explode ( ":", $q );
			if (count ( $_arr ) >= 3) {
				$last_index = count ( $_arr ) - 1;
				unset ( $_arr [0] );
				unset ( $_arr [$last_index] );
				foreach ( $_arr as $id ) {
					if (( int ) $id > 0) {
						$id_arr [] = $id;
					}
				}
			}
			if (! empty ( $id_arr )) {
				$fieldModel->whereIn ( "t1.id", $id_arr );
			} else {
				$fieldModel->where ( "(t1.label LIKE '%$q%' OR t2.content LIKE '%$q%')" );
			}
			if (get_magic_quotes_gpc ()) {
				$_GET ['q'] = stripslashes ( $_GET ['q'] );
			}
		}
		$multiLangModel = MultiLangModel::factory ()->where ( 'model', 'Field' )->where ( 'field', 'title' );
		$column = 'id';
		$direction = 'ASC';
		if (isset ( $_GET ['direction'] ) && isset ( $_GET ['column'] ) && in_array ( strtoupper ( $_GET ['direction'] ), array (
				'ASC',
				'DESC' 
		) )) {
			$column = $_GET ['column'];
			$direction = strtoupper ( $_GET ['direction'] );
		}
		$total = $fieldModel->findCount ()->getData ();
		$row_count = isset ( $_GET ['row_count'] ) && ( int ) $_GET ['row_count'] > 0 ? ( int ) $_GET ['row_count'] : 15;
		$pages = ceil ( $total / $row_count );
		$page = isset ( $_GET ['page'] ) && ( int ) $_GET ['page'] > 0 ? ( int ) $_GET ['page'] : 1;
		if ($page > $pages) {
			$page = $pages > 0 ? $pages : 1;
			$_GET ['page'] = $page;
		}
		$offset = (( int ) $page - 1) * $row_count;
		$_arr = $fieldModel->select ( "t1.*" )->orderBy ( "$column $direction" )->limit ( $row_count, $offset )->findAll ()->getData ();
		foreach ( $_arr as $_k => $_v ) {
			$multiLangModel->reset ()->select ( 't1.*, t2.is_default' )->join ( 'Locale', 't2.id=t1.locale', 'left' )->where ( 'model', 'Field' )->where ( 'field', 'title' )->where ( 'foreign_id', $_v ['id'] );
			if (! isset ( $this->option_arr ['o_multi_lang'] ) || ( int ) $this->option_arr ['o_multi_lang'] === 0) {
				$multiLangModel->where ( 't1.locale', $locale_arr [0] ['id'] );
			}
			$tmp = $multiLangModel->orderBy ( "t2.is_default DESC, t2.id ASC" )->findAll ()->getData ();
			$_arr [$_k] ['i18n'] = array ();
			foreach ( $locale_arr as $item ) {
			    $_arr [$_k] ['i18n'] [$item['id']] = [
			        'foreign_id' => $_arr[$_k]['id'],
			        'model' => 'Field',
			        'locale' => $item['id'],
			        'field' => 'title',
			        'content' => '',
			        'source' => 'script'
			    ];
			}
			foreach ( $tmp as $item ) {
				$_arr [$_k] ['i18n'] [$item ['locale']] = $item;
			}
		}
		$this->set ( 'arr', $_arr );
		$this->set ( 'paginator', compact ( 'pages' ) );
		$this->appendJs ( 'jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/' );
		$this->appendJs ( 'jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/' );
		$this->appendCss ( 'jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/' );
		$this->appendJs ( 'Locale.js', $this->getConst ( 'PLUGIN_JS_PATH' ) );
		$this->appendCss ( 'plugin_locale.css', $this->getConst ( 'PLUGIN_CSS_PATH' ) );
		$this->appendJs ( 'index.php?controller=Admin&action=Messages', $this->baseUrl(), true );
	}
	public function SaveLocale() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$response = array ();
			if (isset ( $_GET ['id'] ) && ( int ) $_GET ['id'] > 0) {
				LocaleModel::factory ()->where ( 'id', $_GET ['id'] )->limit ( 1 )->modifyAll ( array (
						$_POST ['column'] => $_POST ['value'] 
				) );
				$response ['code'] = 201;
			} else {
				$LocaleModel = LocaleModel::factory ();
				$arr = $LocaleModel->select ( 't1.sort' )->orderBy ( 't1.sort DESC' )->limit ( 1 )->findAll ()->getData ();
				$sort = 1;
				if (count ( $arr ) === 1) {
					$sort = ( int ) $arr [0] ['sort'] + 1;
				}
				$lang = LocaleLanguageModel::factory ()->where ( sprintf ( "t1.iso NOT IN (SELECT `language_iso` FROM `%s`)", $LocaleModel->getTable () ) )->where ( 't1.file IS NOT NULL' )->orderBy ( 't1.title ASC' )->limit ( 1 )->findAll ()->getDataPair ( null, 'iso' );
				$insert_id = LocaleModel::factory ( array (
						'sort' => $sort,
						'is_default' => '0',
						'language_iso' => @$lang [0] 
				) )->insert ()->getInsertId ();
				if ($insert_id !== false && ( int ) $insert_id > 0) {
					$response ['code'] = 200;
					$response ['id'] = $insert_id;
					$locale_id = NULL;
					$arr = $LocaleModel->reset ()->findAll ()->getData ();
					foreach ( $arr as $locale ) {
						if ($locale ['language_iso'] == 'en') {
							$locale_id = $locale ['id'];
							break;
						}
					}
					if (is_null ( $locale_id ) && count ( $arr ) > 0) {
						$locale_id = $arr [0] ['id'];
					}
					if (! is_null ( $locale_id )) {
						$sql = sprintf ( "INSERT INTO `%1\$s` (`foreign_id`, `model`, `locale`, `field`, `content`)  SELECT t1.foreign_id, t1.model, :insert_id, t1.field, t1.content  FROM `%1\$s` AS t1  WHERE t1.locale = :locale", MultiLangModel::factory ()->getTable () );
						MultiLangModel::factory ()->prepare ( $sql )->exec ( array (
								'insert_id' => $insert_id,
								'locale' => ( int ) $locale_id 
						) );
						$this->UpdateFieldsIndex ();
					}
				} else {
					$response ['code'] = 100;
				}
			}
			AppController::jsonResponse ( $response );
		}
		exit ();
	}

	public function SaveDefault() {

		$this->setAjax ( true );
		if ($this->isXHR ()) {
			LocaleModel::factory ()->where ( 1, 1 )->modifyAll ( array (
					'is_default' => '0' 
			) )->reset ()->set ( 'id', $_POST ['id'] )->modify ( array (
					'is_default' => 1 
			) );
			$this->setLocaleId ( $_POST ['id'] );
		}
		exit ();
	}
	public function SortLocale() {
		$this->setAjax ( true );
		if ($this->isXHR ()) {
			$LocaleModel = new LocaleModel ();
			$arr = $LocaleModel->whereIn ( 'id', $_POST ['sort'] )->orderBy ( "t1.sort ASC" )->findAll ()->getDataPair ( 'id', 'sort' );
			$fliped = array_flip ( $_POST ['sort'] );
			$combined = array_combine ( array_keys ( $fliped ), $arr );
			$LocaleModel->begin ();
			foreach ( $combined as $id => $sort ) {
				$LocaleModel->setAttributes ( compact ( 'id' ) )->modify ( compact ( 'sort' ) );
			}
			$LocaleModel->commit ();
		}
		exit ();
	}
	public function Clean() {
		$this->checkLogin ();
		if (isset ( $_POST ['clean_step'] )) {
			if ($_POST ['clean_step'] == 1) {
				$multiLangModel = MultiLangModel::factory ();
				$arr = MultiLangModel::factory ()->select ( 't1.id' )->join ( 'Field', 't2.id=t1.foreign_id', 'left' )->where ( 't1.model', 'Field' )->where ( 't2.id IS NULL' )->findAll ()->getDataPair ( null, 'id' );
				if (! empty ( $arr )) {
					$multiLangModel->reset ()->whereIn ( 'id', $arr )->eraseAll ();
					$this->UpdateFieldsIndex ();
				}
			}
			if ($_POST ['clean_step'] == 2) {
				if (isset ( $_POST ['field_id'] ) && ! empty ( $_POST ['field_id'] )) {
					FieldModel::factory ()->whereIn ( 'id', $_POST ['field_id'] )->eraseAll ();
					MultiLangModel::factory ()->where ( 'model', 'Field' )->whereIn ( 'foreign_id', $_POST ['field_id'] )->eraseAll ();
					$this->UpdateFieldsIndex ();
				}
			}
			UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Locale&action=Clean" );
		}
		$step1_arr = MultiLangModel::factory ()->select ( 't1.id' )->join ( 'Field', 't2.id=t1.foreign_id', 'left' )->where ( 't1.model', 'Field' )->where ( 't2.id IS NULL' )->findAll ()->getDataPair ( null, 'id' );
		$this->set ( 'step1_arr', $step1_arr );
		$keys = $start = $data = array ();
		ToolkitComponent::readDir ( $data, APP_PATH );
		foreach ( $data as $file ) {
			$ext = ToolkitComponent::getFileExtension ( $file );
			if ($ext !== 'php') {
				continue;
			}
			$string = file_get_contents ( $file );
			if ($string !== FALSE) {
				preg_match_all ( '/__\(\s*\'(\w+)\'\s*(?:,\s*(true|false))?\)/i', $string, $matches );
				if (! empty ( $matches [1] )) {
					foreach ( $matches [1] as $k => $m ) {
						if (! empty ( $matches [2] [$k] ) && strtolower ( $matches [2] [$k] ) == 'true') {
							$start [] = $m;
						} else {
							$keys [] = $m;
						}
					}
				}
			}
		}
		$keys = array_unique ( $keys );
		$keys = array_values ( $keys );
		$start = array_unique ( $start );
		$start = array_values ( $start );
		if (! empty ( $keys ) || ! empty ( $start )) {
			$field_arr = FieldModel::factory ()->whereNotIn ( 't1.key', $keys )->whereNotIn ( "SUBSTRING_INDEX(t1.key, '_ARRAY_', 1)", $start )->orderBy ( "FIELD(t1.type, 'backend', 'frontend', 'arrays'), t1.key ASC", false )->findAll ()->getData ();
			$this->set ( 'field_arr', $field_arr );
		}
		$this->appendJs ( 'Locale.js', $this->getConst ( 'PLUGIN_JS_PATH' ) );
	}
}
?>