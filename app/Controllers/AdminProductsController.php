<?php
namespace App\Controllers;

use App\Plugins\Locale\Models\LocaleModel;
use App\Models\ProductModel;
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\MultibyteComponent;
use App\Models\ProductSimilarModel;
use App\Models\AttributeModel;
use App\Models\MultiLangModel;
use App\Plugins\Gallery\Models\GalleryModel;
use App\Models\StockModel;
use App\Models\StockAttributeModel;
use Core\Framework\Objects;
use App\Models\ProductCategoryModel;
use App\Models\CategoryModel;
use App\Models\ExtraModel;
use App\Models\ExtraItemModel;
use Core\Framework\Components\UploadComponent;
use App\Models\OrderStockModel;
use Core\Framework\Components\CSVComponent;
use App\Models\HistoryModel;
use App\Models\RouterModel;
use App\Models\ProductTagModel;
use App\Models\TagModel;

class AdminProductsController extends AdminController
{
    const FRONTEND_PRODUCT_CONTROLLER = 'ShopCart';
    
    private function LoadLocales()
    {
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
        return $this;
    }

    public function OpenDigital()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
                $arr = ProductModel::factory()->find($_GET['id'])->getData();
                if (empty($arr)) {
                    exit();
                }
                if ((int) $arr['is_digital'] !== 1) {
                    exit();
                }
                if (empty($arr['digital_file']) || ! is_file($arr['digital_file'])) {
                    exit();
                }
                $handle = @fopen($arr['digital_file'], "rb");
                if ($handle) {
                    $buffer = "";
                    while (! feof($handle)) {
                        $buffer .= fgets($handle, 4096);
                    }
                    fclose($handle);
                }
                $this->setAjax(true);
                $this->setLayout('ActionEmpty');
                $ext = UtilComponent::getFileExtension($arr['digital_file']);
                $mime_type = UtilComponent::getMimeType($ext);
                $charset = MultibyteComponent::detect_encoding($buffer);
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Cache-Control: private', FALSE);
                header('Content-Type: ' . $mime_type . '; charset=' . $charset);
                header('Content-Disposition: inline; filename="' . basename($arr['digital_name']) . '"');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . filesize($arr['digital_file']));
                echo $buffer;
                exit();
            } else {
                exit();
            }
        } else {
            $this->set('status', 2);
        }
        exit();
    }

    public function AttrGroupDelete()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['id']) && (int) $_POST['id'] > 0) {
                $AttributeModel = AttributeModel::factory();
                $ids = $AttributeModel->where('t1.parent_id', $_POST['id'])
                    ->findAll()
                    ->getDataPair(null, 'id');
                if ($AttributeModel->reset()
                    ->set('id', $_POST['id'])
                    ->erase()
                    ->getAffectedRows() == 1) {
                    $MultiLangModel = MultiLangModel::factory();
                    $MultiLangModel->where('model', 'Attribute')
                        ->where('foreign_id', $_POST['id'])
                        ->eraseAll();
                    if (! empty($ids)) {
                        $AttributeModel->reset()
                            ->whereIn('id', $ids)
                            ->eraseAll();
                        $MultiLangModel->reset()
                            ->where('model', 'Attribute')
                            ->whereIn('foreign_id', $ids)
                            ->eraseAll();
                    }
                    AppController::jsonResponse(array(
                        'status' => 'OK',
                        'code' => 200,
                        'text' => ''
                    ));
                }
            }
            AppController::jsonResponse(array(
                'status' => 'ERR',
                'code' => 100,
                'text' => ''
            ));
        }
        exit();
    }

    public function AttrDelete()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['id']) && (int) $_POST['id'] > 0) {
                if (AttributeModel::factory()->set('id', $_POST['id'])
                    ->erase()
                    ->getAffectedRows() == 1) {
                    MultiLangModel::factory()->where('model', 'Attribute')
                        ->where('foreign_id', $_POST['id'])
                        ->eraseAll();
                    AppController::jsonResponse(array(
                        'status' => 'OK',
                        'code' => 200,
                        'text' => ''
                    ));
                }
            }
            AppController::jsonResponse(array(
                'status' => 'ERR',
                'code' => 100,
                'text' => ''
            ));
        }
        exit();
    }

    public function AttrCopy()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['from_product_id']) && (int) $_POST['from_product_id'] > 0) {
                if (isset($_POST['product_id']) && (int) $_POST['product_id'] > 0) {
                    $ak = 'product_id';
                    $av = $_POST['product_id'];
                } elseif (isset($_POST['hash']) && ! empty($_POST['hash'])) {
                    $ak = 'hash';
                    $av = $_POST['hash'];
                }
                $attributeModel = AttributeModel::factory();
                $multiLangModel = MultiLangModel::factory();
                $attr = $attributeModel->where('t1.product_id', $_POST['from_product_id'])
                    ->orderBy('t1.`order_group`, `order_item` ASC')
                    ->findAll()
                    ->getDataPair('id', 'parent_id');
                $arr = array();
                foreach ($attr as $id => $parent_id) {
                    if (empty($parent_id)) {
                        $arr[$id] = array();
                    } else {
                        $arr[$parent_id][] = $id;
                    }
                }
                $multi = $multiLangModel->where('t1.model', 'Attribute')
                    ->whereIn('t1.foreign_id', array_keys($attr))
                    ->where("t1.field='name'")
                    ->findAll()
                    ->getData();
                $stack = array();
                foreach ($multi as $item) {
                    if (! isset($stack[$item['foreign_id']])) {
                        $stack[$item['foreign_id']] = array();
                    }
                    $stack[$item['foreign_id']][] = $item;
                }
                $last_order = $attributeModel->getLastOrder($_POST['product_id']);
                foreach ($arr as $parent_id => $items) {
                    $insert_id = $attributeModel->reset()
                        ->setAttributes(array(
                        $ak => $av,
                        'order_group' => $last_order
                    ))
                        ->insert()
                        ->getInsertId();
                    if ($insert_id !== false && (int) $insert_id > 0) {
                        if (isset($stack[$parent_id])) {
                            foreach ($stack[$parent_id] as $locale) {
                                $multiLangModel->reset()
                                    ->setAttributes(array(
                                    'model' => $locale['model'],
                                    'foreign_id' => $insert_id,
                                    'field' => $locale['field'],
                                    'locale' => $locale['locale'],
                                    'content' => $locale['content'],
                                    'source' => 'data'
                                ))
                                    ->insert();
                            }
                        }
                        $item_order = 0;
                        foreach ($items as $id) {
                            $attr_id = $attributeModel->reset()
                                ->setAttributes(array(
                                $ak => $av,
                                'parent_id' => $insert_id,
                                'order_group' => $last_order,
                                'order_item' => $item_order
                            ))
                                ->insert()
                                ->getInsertId();
                            if ($attr_id !== false && (int) $attr_id > 0) {
                                if (isset($stack[$id])) {
                                    foreach ($stack[$id] as $locale) {
                                        $multiLangModel->reset()
                                            ->setAttributes(array(
                                            'model' => $locale['model'],
                                            'foreign_id' => $attr_id,
                                            'field' => $locale['field'],
                                            'locale' => $locale['locale'],
                                            'content' => $locale['content'],
                                            'source' => 'data'
                                        ))
                                            ->insert();
                                    }
                                }
                                $item_order ++;
                            }
                        }
                    }
                    $last_order ++;
                }
                AppController::jsonResponse(array(
                    'status' => 'OK',
                    'code' => 200,
                    'text' => ''
                ));
            }
        }
        exit();
    }

    private function AttrHandle($product_id)
    {
        if (isset($_POST['attr']) && ! empty($_POST['attr'])) {
            $attributeModel = AttributeModel::factory();
            $MultiLangModel = MultiLangModel::factory();
            $keys = array_keys($_POST['i18n']);
            $fkey = $keys[0];
            $group_arr = ! empty($_POST['orderAttributes']) ? explode("|", $_POST['orderAttributes']) : array();
            $order_group_arr = array();
            foreach ($group_arr as $k => $v) {
                $order_group_arr[$v] = $k;
            }
            foreach ($_POST['attr'] as $group_id => $whatever) {
                if (strpos($group_id, 'x_') === 0) {
                    $attr_group_id = $attributeModel->reset()
                        ->setAttributes(array(
                        'product_id' => $product_id,
                        'order_group' => $order_group_arr['attrBox_' . $group_id]
                    ))
                        ->insert()
                        ->getInsertId();
                    if ($attr_group_id !== false && (int) $attr_group_id > 0) {
                        $tmp = $this->TurnI18n($_POST['i18n'], 'attr_group', $group_id, NULL, 'name');
                        $MultiLangModel->saveMultiLang($tmp, $attr_group_id, 'Attribute');
                        $item_arr = ! empty($_POST['orderItems_' . $group_id]) ? explode("|", $_POST['orderItems_' . $group_id]) : array();
                        $order_item_arr = array();
                        foreach ($item_arr as $k => $v) {
                            $order_item_arr[$v] = $k;
                        }
                        if (isset($_POST['i18n'][$fkey]['attr_item'][$group_id]) && count(($_POST['i18n'][$fkey]['attr_item'][$group_id])) > 0) {
                            foreach ($_POST['i18n'][$fkey]['attr_item'][$group_id] as $index => $value) {
                                $attr_item_id = $attributeModel->reset()
                                    ->setAttributes(array(
                                    'product_id' => $product_id,
                                    'parent_id' => $attr_group_id,
                                    'order_group' => $order_group_arr['attrBox_' . $group_id],
                                    'order_item' => $order_item_arr['attrBoxRowItems_' . $index]
                                ))
                                    ->insert()
                                    ->getInsertId();
                                if ($attr_item_id !== false && (int) $attr_item_id > 0) {
                                    $tmp = $this->TurnI18n($_POST['i18n'], 'attr_item', $group_id, $index, 'name');
                                    $MultiLangModel->saveMultiLang($tmp, $attr_item_id, 'Attribute');
                                }
                            }
                        }
                    }
                } else {
                    $tmp = $this->TurnI18n($_POST['i18n'], 'attr_group', $group_id, NULL, 'name');
                    $MultiLangModel->updateMultiLang($tmp, $group_id, 'Attribute');
                    $attributeModel->reset()
                        ->set('id', $group_id)
                        ->modify(array(
                        'order_group' => $order_group_arr['attrBox_' . $group_id]
                    ));
                    $item_arr = ! empty($_POST['orderItems_' . $group_id]) ? explode("|", $_POST['orderItems_' . $group_id]) : array();
                    $order_item_arr = array();
                    foreach ($item_arr as $k => $v) {
                        $order_item_arr[$v] = $k;
                    }
                    if (isset($_POST['i18n'][$fkey]['attr_item'][$group_id]) && is_array($_POST['i18n'][$fkey]['attr_item'][$group_id])) {
                        foreach ($_POST['i18n'][$fkey]['attr_item'][$group_id] as $index => $value) {
                            if (strpos($index, 'y_') === 0) {
                                $attr_item_id = $attributeModel->reset()
                                    ->setAttributes(array(
                                    'product_id' => $product_id,
                                    'parent_id' => $group_id,
                                    'order_group' => $order_group_arr['attrBox_' . $group_id],
                                    'order_item' => $order_item_arr['attrBoxRowItems_' . $index]
                                ))
                                    ->insert()
                                    ->getInsertId();
                                if ($attr_item_id !== false && (int) $attr_item_id > 0) {
                                    $tmp = $this->TurnI18n($_POST['i18n'], 'attr_item', $group_id, $index, 'name');
                                    $MultiLangModel->saveMultiLang($tmp, $attr_item_id, 'Attribute');
                                }
                            } else {
                                $tmp = $this->TurnI18n($_POST['i18n'], 'attr_item', $group_id, $index, 'name');
                                $attributeModel->reset()
                                    ->set('id', $index)
                                    ->modify(array(
                                    'order_group' => $order_group_arr['attrBox_' . $group_id],
                                    'order_item' => $order_item_arr['attrBoxRowItems_' . $index]
                                ));
                                $MultiLangModel->updateMultiLang($tmp, $index, 'Attribute');
                            }
                        }
                    }
                }
            }
            foreach ($_POST['i18n'] as $locale_id => $whatever) {
                if (isset($_POST['i18n'][$locale_id]['attr_group'])) {
                    unset($_POST['i18n'][$locale_id]['attr_group']);
                }
                if (isset($_POST['i18n'][$locale_id]['attr_item'])) {
                    unset($_POST['i18n'][$locale_id]['attr_item']);
                }
            }
        }
    }

    public function CheckSku()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (! isset($_GET['sku']) || empty($_GET['sku'])) {
                echo 'false';
                exit();
            }
            $ProductModel = ProductModel::factory()->where('t1.sku', $_GET['sku']);
            if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
                $ProductModel->where('t1.id !=', $_GET['id']);
            }
            echo $ProductModel->findCount()->getData() == 0 ? 'true' : 'false';
        }
        exit();
    }

    public function Create()
    {
        $this->checkLogin();
        $this->title = "Sản phẩm - Tạo sản phẩm";
        if ($this->isAdmin()) {
            if (isset($_POST['product_create'])) {
                $MultiLangModel = MultiLangModel::factory();
                $data = array();
                $data['is_featured'] = isset($_POST['is_featured']) ? 1 : array(
                    0
                );
                $product_id = ProductModel::factory()->setAttributes(array_merge($_POST, $data))
                    ->insert()
                    ->getInsertId();
                if ($product_id !== false && (int) $product_id > 0) {
                    $locale_arr = LocaleModel::factory()->select('t1.*')
                    ->orderBy('t1.sort ASC')
                    ->findAll()
                    ->getData();
                    $defaultLocale = LocaleModel::factory()->where('is_default', 1)->limit(1)->findAll()->first();
                    $defaultLocaleId = $defaultLocale['id'];
                    if (isset($_POST['i18n'])) {
                        $MultiLangModel->saveMultiLang($_POST['i18n'], $product_id, 'Product');
                    }
                    if (isset($_POST['category_id']) && count($_POST['category_id']) > 0) {
                        $productCategoryModel = ProductCategoryModel::factory();
                        $productCategoryModel->begin();
                        foreach ($_POST['category_id'] as $category_id) {
                            $productCategoryModel->reset()
                                ->set('product_id', $product_id)
                                ->set('category_id', $category_id)
                                ->insert();
                        }
                        $productCategoryModel->commit();
                    }

                    $uuidNumber = $this->generateRamdomNumber(6);
                    foreach ($locale_arr as $locale) {
                        $category_arr = CategoryModel::factory ()->getNode ( $locale['id'], 1);
                        $pc_arr = ProductCategoryModel::factory()->where('product_id', $product_id)->findAll()->getDataPair(NULL, 'category_id');
                        $productName = $_POST['i18n'][$locale['id']]['name'];
                        if (empty($productName)) {
                            $productName = $_POST['i18n'][$defaultLocaleId]['name'];
                        }
                        $friendlyUrl = $this->createFriendlyUrl($category_arr, $pc_arr, $productName);
                        $friendlyUrl = $locale['language_iso'].'/'.$friendlyUrl.'-'.$uuidNumber;
                        $this->createOrUpdateDynamicRouter(
                            $friendlyUrl,
                            RouterModel::TYPE_PRODUCT,
                            self::FRONTEND_PRODUCT_CONTROLLER,
                            "Product",
                            "id",
                            $product_id,
                            $locale['id']);
                    }
                    $err = 'AP01';
                    UtilComponent::redirect(sprintf("%s?controller=AdminProducts&action=Update&id=%u&tab=0&err=%s", $_SERVER['PHP_SELF'], $product_id, $err));
                } else {
                    $err = 'AP02';
                }
                UtilComponent::redirect(sprintf("%s?controller=AdminProducts&action=Index&err=%s", $_SERVER['PHP_SELF'], $err));
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
                $this->set('category_arr', CategoryModel::factory()->getNode($this->getLocaleId(), 1));
                $this->appendJs ( 'chosen.jquery.js', THIRD_PARTY_PATH . 'harvest/chosen/' );
                $this->appendCss ( 'chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
                $this->appendJs('jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/');
                $this->appendJs('jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/');
                $this->appendCss('jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/');
                $this->appendJs('tinymce.min.js', THIRD_PARTY_PATH . 'tiny_mce/');
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('AdminProducts.js');
            }
        } else {
            $this->set('status', 2);
        }
    }

    private function DeleteProductAttr($product_id)
    {
        if (empty($product_id)) {
            return false;
        }
        $attributeModel = AttributeModel::factory();
        if (is_array($product_id)) {
            $attributeModel->whereIn('product_id', $product_id);
        } else {
            $attributeModel->where('product_id', $product_id);
        }
        $attr_ids = $attributeModel->findAll()->getDataPair(null, 'id');
        if (! empty($attr_ids)) {
            $attributeModel->eraseAll();
            $MultiLangModel = MultiLangModel::factory();
            $MultiLangModel->reset()
                ->where('model', 'Attribute')
                ->whereIn('foreign_id', $attr_ids)
                ->eraseAll();
        }
    }

    private function DeleteStockAttr($product_id)
    {
        if (empty($product_id)) {
            return false;
        }
        $stockAttributeModel = StockAttributeModel::factory();
        if (is_array($product_id)) {
            $stockAttributeModel->whereIn('product_id', $product_id);
        } else {
            $stockAttributeModel->where('product_id', $product_id);
        }
        $stockAttributeModel->eraseAll();
    }

    public function Deactivate()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged() && isset($_GET['id']) && (int) $_GET['id'] > 0) {
            ProductModel::factory()->where('id', $_GET['id'])
                ->limit(1)
                ->modifyAll(array(
                'status' => 2
            ));
            AppController::jsonResponse(array(
                'status' => 'OK',
                'code' => 200,
                'text' => ''
            ));
        }
        exit();
    }
    
    public function DeleteProduct()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged() && isset($_GET['id']) && (int) $_GET['id'] > 0) {
            if (ProductModel::factory()->set('id', $_GET['id'])
                ->erase()
                ->getAffectedRows() == 1) {
                $extraModel = ExtraModel::factory();
                $MultiLangModel = MultiLangModel::factory();
                $MultiLangModel->where('model', 'Product')
                    ->where('foreign_id', $_GET['id'])
                    ->eraseAll();
                StockModel::factory()->where('product_id', $_GET['id'])->eraseAll();
                $this->DeleteStockAttr($_GET['id']);
                ProductSimilarModel::factory()->where('product_id', $_GET['id'])
                    ->orWhere('similar_id', $_GET['id'])
                    ->eraseAll();
                $extra_arr = $extraModel->where('product_id', $_GET['id'])
                    ->findAll()
                    ->getDataPair(null, 'id');
                if (! empty($extra_arr)) {
                    $extraItemModel = ExtraItemModel::factory();
                    $extraModel->eraseAll();
                    $MultiLangModel->reset()
                        ->where('model', 'Extra')
                        ->whereIn('foreign_id', $extra_arr)
                        ->eraseAll();
                    $extra_item_arr = $extraItemModel->whereIn('extra_id', $extra_arr)
                        ->findAll()
                        ->getDataPair(NULL, 'id');
                    if (! empty($extra_item_arr)) {
                        $extraItemModel->reset()
                            ->whereIn('extra_id', $extra_arr)
                            ->eraseAll();
                        $MultiLangModel->reset()
                            ->where('model', 'ExtraItem')
                            ->whereIn('foreign_id', $extra_item_arr)
                            ->eraseAll();
                    }
                }
                $this->DeleteProductAttr($_GET['id']);
                ProductCategoryModel::factory()->where('product_id', $_GET['id'])->eraseAll();
                $galleryModel = GalleryModel::factory();
                $image_arr = $galleryModel->where('foreign_id', $_GET['id'])
                    ->findAll()
                    ->getData();
                if (! empty($image_arr)) {
                    $galleryModel->eraseAll();
                    foreach ($image_arr as $image) {
                        @clearstatcache();
                        if (! empty($image['small_path']) && is_file($image['small_path'])) {
                            @unlink($image['small_path']);
                        }
                        @clearstatcache();
                        if (! empty($image['medium_path']) && is_file($image['medium_path'])) {
                            @unlink($image['medium_path']);
                        }
                        @clearstatcache();
                        if (! empty($image['large_path']) && is_file($image['large_path'])) {
                            @unlink($image['large_path']);
                        }
                        @clearstatcache();
                        if (! empty($image['source_path']) && is_file($image['source_path'])) {
                            @unlink($image['source_path']);
                        }
                    }
                }
                $this->deleteRouter(RouterModel::TYPE_PRODUCT, self::FRONTEND_PRODUCT_CONTROLLER, 'Product', $_GET['id']);
                AppController::jsonResponse(array(
                    'status' => 'OK',
                    'code' => 200,
                    'text' => ''
                ));
            } else {
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 100,
                    'text' => ''
                ));
            }
        }
        exit();
    }

    public function DeleteProductBulk()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['record']) && count($_POST['record']) > 0) {
                $extraModel = ExtraModel::factory();
                $multiLangModel = MultiLangModel::factory();
                ProductModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
                $multiLangModel->where('model', 'Product')
                    ->whereIn('foreign_id', $_POST['record'])
                    ->eraseAll();
                StockModel::factory()->whereIn('product_id', $_POST['record'])->eraseAll();
                $this->DeleteStockAttr($_POST['record']);
                ProductSimilarModel::factory()->whereIn('product_id', $_POST['record'])
                    ->orWhereIn('similar_id', $_POST['record'])
                    ->eraseAll();
                $extra_arr = $extraModel->whereIn('product_id', $_POST['record'])
                    ->findAll()
                    ->getDataPair(null, 'id');
                if (! empty($extra_arr)) {
                    $extraItemModel = ExtraItemModel::factory();
                    $extraModel->eraseAll();
                    $multiLangModel->reset()
                        ->where('model', 'Extra')
                        ->whereIn('foreign_id', $extra_arr)
                        ->eraseAll();
                    $extra_item_arr = $extraItemModel->whereIn('extra_id', $extra_arr)
                        ->findAll()
                        ->getDataPair(NULL, 'id');
                    if (! empty($extra_item_arr)) {
                        $extraItemModel->reset()
                            ->whereIn('extra_id', $extra_arr)
                            ->eraseAll();
                        $multiLangModel->reset()
                            ->where('model', 'ExtraItem')
                            ->whereIn('foreign_id', $extra_item_arr)
                            ->eraseAll();
                    }
                }
                $this->DeleteProductAttr($_POST['record']);
                ProductCategoryModel::factory()->whereIn('product_id', $_POST['record'])->eraseAll();
                $galleryModel = GalleryModel::factory();
                $image_arr = $galleryModel->whereIn('foreign_id', $_POST['record'])
                    ->findAll()
                    ->getData();
                if (! empty($image_arr)) {
                    $galleryModel->eraseAll();
                    foreach ($image_arr as $image) {
                        @clearstatcache();
                        if (! empty($image['small_path']) && is_file($image['small_path'])) {
                            @unlink($image['small_path']);
                        }
                        @clearstatcache();
                        if (! empty($image['medium_path']) && is_file($image['medium_path'])) {
                            @unlink($image['medium_path']);
                        }
                        @clearstatcache();
                        if (! empty($image['large_path']) && is_file($image['large_path'])) {
                            @unlink($image['large_path']);
                        }
                        @clearstatcache();
                        if (! empty($image['source_path']) && is_file($image['source_path'])) {
                            @unlink($image['source_path']);
                        }
                    }
                }
                foreach ($_POST['record'] as $id) {
                    $this->deleteRouter(RouterModel::TYPE_PRODUCT, self::FRONTEND_PRODUCT_CONTROLLER, 'product', $id);
                }
            }
        }
        exit();
    }

    public function DeleteSimilar()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged() && isset($_GET['id']) && (int) $_GET['id'] > 0) {
            if (ProductSimilarModel::factory()->set('id', $_GET['id'])
                ->erase()
                ->getAffectedRows() == 1) {
                AppController::jsonResponse(array(
                    'status' => 'OK',
                    'code' => 200,
                    'text' => 'Similar product has been deleted.'
                ));
            }
            AppController::jsonResponse(array(
                'status' => 'ERR',
                'code' => 100,
                'text' => 'Similar product has not been deleted.'
            ));
        }
        exit();
    }

    public function DeleteSimilarBulk()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['record']) && count($_POST['record']) > 0) {
                if (ProductSimilarModel::factory()->whereIn('id', $_POST['record'])
                    ->eraseAll()
                    ->getAffectedRows() > 0) {
                    AppController::jsonResponse(array(
                        'status' => 'OK',
                        'code' => 200,
                        'text' => 'Similar products has been deleted.'
                    ));
                }
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 100,
                    'text' => 'Similar products has been deleted.'
                ));
            }
        }
        exit();
    }

    public function GetProduct()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $productModel = ProductModel::factory()->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getLocaleId() . "' AND t2.field='name'", 'left outer')
                ->join('MultiLang', "t3.model='Product' AND t3.foreign_id=t1.id AND t3.locale='" . $this->getLocaleId() . "' AND t3.field='short_desc'", 'left outer')
                ->join('MultiLang', "t4.model='Product' AND t4.foreign_id=t1.id AND t4.locale='" . $this->getLocaleId() . "' AND t4.field='full_desc'", 'left outer');
            if (isset($_GET['q']) && ! empty($_GET['q'])) {
                $q = str_replace(array(
                    '%',
                    '_'
                ), array(
                    '\%',
                    '\_'
                ), trim($_GET['q']));
                $productModel->where('t2.content LIKE', "%$q%");
                $productModel->orWhere('t3.content LIKE', "%$q%");
                $productModel->orWhere('t4.content LIKE', "%$q%");
                $productModel->orWhere('t1.sku LIKE', "%$q%");
            }
            if (isset($_GET['status']) && ! empty($_GET['status']) && in_array($_GET['status'], array(
                1,
                2
            ))) {
                $productModel->where('t1.status', $_GET['status']);
            }
            if (isset($_GET['name']) && ! empty($_GET['name'])) {
                $q = str_replace(array(
                    '%',
                    '_'
                ), array(
                    '\%',
                    '\_'
                ), $_GET['name']);
                $productModel->where('t2.content LIKE', "%$q%");
            }
            if (isset($_GET['sku']) && ! empty($_GET['sku'])) {
                $q = str_replace(array(
                    '%',
                    '_'
                ), array(
                    '\%',
                    '\_'
                ), $_GET['sku']);
                $productModel->where('t1.sku LIKE', "%$q%");
            }
            if (isset($_GET['category_id']) && (int) $_GET['category_id'] > 0) {
                $productModel->where(sprintf("t1.id IN (SELECT `product_id` FROM `%s` WHERE `category_id` = '%u')", ProductCategoryModel::factory()->getTable(), (int) $_GET['category_id']));
            }
            if (isset($_GET['is_digital'])) {
                $productModel->where('t1.is_digital', 1);
            }
            if (isset($_GET['is_featured'])) {
                $productModel->where('t1.is_featured', 1);
            }
            if (isset($_GET['is_out']) && $_GET['is_out'] != '') {
                $productModel->where("(t1.id NOT IN(SELECT TS.product_id FROM `" . StockModel::factory()->getTable() . "` AS TS GROUP BY TS.product_id HAVING SUM(TS.qty) > 0))");
            }
            if (isset($_GET['is_active_out']) && $_GET['is_active_out'] != '') {
                $productModel->where("(t1.status = 1 AND t1.id NOT IN(SELECT TS.product_id FROM `" . StockModel::factory()->getTable() . "` AS TS GROUP BY TS.product_id HAVING SUM(TS.qty) > 0))");
            }
            $column = 'name';
            $direction = 'ASC';
            if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                'ASC',
                'DESC'
            ))) {
                $column = $_GET['column'];
                $direction = strtoupper($_GET['direction']);
            }
            $total = $productModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $data = $productModel->select(sprintf("t1.*, t2.content AS name,  (SELECT `small_path` FROM `%1\$s`  WHERE `foreign_id` = `t1`.`id`  ORDER BY `sort` ASC  LIMIT 1) AS `pic`,  (SELECT COALESCE(SUM(`qty`), 0) FROM `%2\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `total_stock`,  (SELECT MIN(`price`) FROM `%2\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `min_price`,  (SELECT COUNT(`id`) FROM `%2\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `cnt_stock`,  (SELECT COUNT(DISTINCT `order_id`) FROM `%3\$s` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `cnt_orders`  ", GalleryModel::factory()->getTable(), StockModel::factory()->getTable(), OrderStockModel::factory()->getTable()))
                ->orderBy("$column $direction")
                ->limit($rowCount, $offset)
                ->findAll()
                ->getData();
            foreach ($data as $k => $v) {
                $data[$k]['min_price_format'] = UtilComponent::formatCurrencySign(number_format($v['min_price'], 2), $this->option_arr['o_currency']);
                if ($v['cnt_stock'] > 1) {
                    $data[$k]['min_price_format'] = __('front_price_from', true) . " " . $data[$k]['min_price_format'];
                }
            }
            AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
        }
        exit();
    }

    public function GetStock()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $StockModel = StockModel::factory()->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.product_id AND t2.locale='" . $this->getLocaleId() . "' AND t2.field='name'", 'left outer')
                ->join('Product', 't3.id=t1.product_id', 'left outer');
            if (isset($_GET['q']) && ! empty($_GET['q'])) {
                $q = trim($_GET['q']);
                $q = str_replace(array(
                    '%',
                    '_'
                ), array(
                    '\%',
                    '\_'
                ), $q);
                $StockModel->where('t2.content LIKE', "%$q%");
                $StockModel->orWhere('t3.sku LIKE', "%$q%");
            }
            $column = 'name';
            $direction = 'ASC';
            if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                'ASC',
                'DESC'
            ))) {
                $column = $_GET['column'];
                $direction = strtoupper($_GET['direction']);
            }
            $total = $StockModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $data = $StockModel->select(sprintf("t1.*, t2.content AS name,  (SELECT `small_path` FROM `%1\$s`  WHERE `id` = `t1`.`image_id`  LIMIT 1) AS `pic`,  (SELECT GROUP_CONCAT(CONCAT_WS('~:~', `tm2`.`content`, `tm1`.`content`) SEPARATOR '~|~')  FROM `%2\$s` AS `tsa`  LEFT JOIN `%3\$s` AS `tm1` ON `tm1`.`model` = 'Attribute' AND `tm1`.`foreign_id` = `tsa`.`attribute_id` AND `tm1`.`field` = 'name' AND `tm1`.`locale` = '%4\$u'  LEFT JOIN `%3\$s` AS `tm2` ON `tm2`.`model` = 'Attribute' AND `tm2`.`foreign_id` = `tsa`.`attribute_parent_id` AND `tm2`.`field` = 'name' AND `tm2`.`locale` = '%4\$u'  WHERE `tsa`.`product_id` = `t1`.`product_id`  AND `tsa`.`stock_id` = `t1`.`id`  LIMIT 1) AS `stock_attr`  ", GalleryModel::factory()->getTable(), StockAttributeModel::factory()->getTable(), MultiLangModel::factory()->getTable(), $this->getLocaleId()))
                ->orderBy("$column $direction")
                ->limit($rowCount, $offset)
                ->findAll()
                ->toArray('stock_attr', '~|~')
                ->getData();
            foreach ($data as $k => $v) {
                $data[$k]['price_formated'] = UtilComponent::formatCurrencySign(number_format($v['price'], 2), $this->option_arr['o_currency']);
            }
            AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
        }
        exit();
    }

    public function DeleteExtra()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $resp = array(
                'code' => 100
            );
            if (ExtraModel::factory()->set('id', $_POST['id'])
                ->erase()
                ->getAffectedRows() == 1) {
                $MultiLangModel = MultiLangModel::factory();
                $ExtraItemModel = ExtraItemModel::factory();
                $MultiLangModel->reset()
                    ->where('model', 'Extra')
                    ->where('foreign_id', $_POST['id'])
                    ->eraseAll();
                $extra_item = $ExtraItemModel->where('extra_id', $_POST['id'])
                    ->findAll()
                    ->getDataPair(NULL, 'id');
                if (! empty($extra_item)) {
                    $MultiLangModel->reset()
                        ->where('model', 'ExtraItem')
                        ->whereIn('foreign_id', $extra_item)
                        ->eraseAll();
                    $ExtraItemModel->reset()
                        ->where('extra_id', $_POST['id'])
                        ->eraseAll();
                }
                $resp['code'] = 200;
            }
            AppController::jsonResponse($resp);
        }
        exit();
    }

    public function DeleteStock()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $resp = array(
                'code' => 100
            );
            if (StockModel::factory()->set('id', $_POST['id'])
                ->erase()
                ->getAffectedRows() == 1) {
                StockAttributeModel::factory()->where('stock_id', $_POST['id'])->eraseAll();
                $resp['code'] = 200;
            }
            AppController::jsonResponse($resp);
        }
    }
    
    public function DeleteDigital()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $ProductModel = ProductModel::factory();
            $arr = $ProductModel->find($_POST['id'])->getData();
            if (count($arr) > 0) {
                if ($ProductModel->reset()
                    ->set('id', $arr['id'])
                    ->modify(array(
                    'digital_file' => ':NULL',
                    'digital_name' => ':NULL'
                ))
                    ->getAffectedRows() == 1) {
                    @unlink($arr['digital_file']);
                }
            }
        }
    }

    public function ExportProduct()
    {
        $this->checkLogin();
        if (isset($_POST['record']) && is_array($_POST['record'])) {
            $arr = ProductModel::factory()->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getLocaleId() . "' AND t2.field='name'", 'left outer')
                ->select("t1.*, t2.content as product_name, (SELECT COALESCE(SUM(`qty`), 0) FROM `" . StockModel::factory()->getTable() . "` WHERE `product_id` = `t1`.`id` LIMIT 1) AS `in_stock`")
                ->whereIn('t1.id', $_POST['record'])
                ->findAll()
                ->getData();
            $csv = new CSVComponent();
            $csv->setHeader(true)
                ->setName("Products-" . time() . ".csv")
                ->process($arr)
                ->download();
        }
        exit();
    }


    public function ExtraCopy()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['from_product_id']) && (int) $_POST['from_product_id'] > 0 && isset($_POST['product_id']) && (int) $_POST['product_id'] > 0) {
                $ExtraModel = ExtraModel::factory();
                $ExtraItemModel = ExtraItemModel::factory();
                $MultiLangModel = MultiLangModel::factory();
                $extras = $ExtraModel->where('t1.product_id', $_POST['from_product_id'])
                    ->findAll()
                    ->getData();
                $extras_items = $ExtraItemModel->where(sprintf("t1.extra_id IN (SELECT `id` FROM `%s` WHERE `product_id` = '%u')", $ExtraModel->getTable(), $_POST['from_product_id']))
                    ->findAll()
                    ->getData();
                foreach ($extras as $k => $extra) {
                    $extras[$k]['items'] = array();
                    if ($extra['type'] == 'multi') {
                        foreach ($extras_items as $key => $item) {
                            if ($item['extra_id'] == $extra['id']) {
                                $extras[$k]['items'][] = $item;
                            }
                        }
                    }
                }
                $query = sprintf("INSERT INTO `%1\$s` (`foreign_id`, `model`, `locale`, `field`, `content`, `source`)  SELECT :foreign_id, `model`, `locale`, `field`, `content`, `source`  FROM `%1\$s`  WHERE `foreign_id` = :fid  AND `model` = :model", $MultiLangModel->getTable());
                foreach ($extras as $extra) {
                    $extra_id = $ExtraModel->reset()
                        ->setAttributes(array(
                        'product_id' => $_POST['product_id'],
                        'type' => $extra['type'],
                        'price' => $extra['price'],
                        'is_mandatory' => $extra['is_mandatory']
                    ))
                        ->insert()
                        ->getInsertId();
                    if ($extra_id !== FALSE && (int) $extra_id > 0) {
                        $MultiLangModel->reset()
                            ->prepare($query)
                            ->exec(array(
                            'foreign_id' => $extra_id,
                            'fid' => $extra['id'],
                            'model' => 'Extra'
                        ));
                        if ($extra['type'] == 'multi' && isset($extra['items']) && ! empty($extra['items'])) {
                            foreach ($extra['items'] as $item) {
                                $extra_item_id = $ExtraItemModel->reset()
                                    ->setAttributes(array(
                                    'extra_id' => $extra_id,
                                    'price' => $item['price']
                                ))
                                    ->insert()
                                    ->getInsertId();
                                if ($extra_item_id !== FALSE && (int) $extra_item_id > 0) {
                                    $MultiLangModel->reset()
                                        ->prepare($query)
                                        ->exec(array(
                                        'foreign_id' => $extra_item_id,
                                        'fid' => $item['id'],
                                        'model' => 'ExtraItem'
                                    ));
                                }
                            }
                        }
                    }
                }
                AppController::jsonResponse(array(
                    'status' => 'OK',
                    'code' => 200,
                    'text' => 'Extras has been copied'
                ));
            }
        }
        exit();
    }

    public function GetAttributes()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_GET['product_id']) && (int) $_GET['product_id'] > 0) {
                $wk = 't1.product_id';
                $wv = $_GET['product_id'];
            } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                $wk = 't1.hash';
                $wv = $_GET['hash'];
            }
            $attr_arr = array();
            $a_arr = AttributeModel::factory()->select('t1.id, t1.product_id, t1.parent_id, t1.hash, t2.content AS name')
                ->join('MultiLang', "t2.model='Attribute' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
                ->where($wk, $wv)
                ->orderBy('t1.`order_group` ASC, `order_item` ASC')
                ->findAll()
                ->getData();
            $MultiLangModel = MultiLangModel::factory();
            foreach ($a_arr as $attr) {
                $attr['i18n'] = $MultiLangModel->reset()->getMultiLang($attr['id'], 'Attribute');
                if ((int) $attr['parent_id'] === 0) {
                    $attr_arr[$attr['id']] = $attr;
                } else {
                    if (! isset($attr_arr[$attr['parent_id']]['child'])) {
                        $attr_arr[$attr['parent_id']]['child'] = array();
                    }
                    $attr_arr[$attr['parent_id']]['child'][] = $attr;
                }
            }
            $this->set('attr_arr', array_values($attr_arr));
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
        }
    }

    public function GetExtras()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (! isset($_GET['product_id']) || (int) $_GET['product_id'] <= 0) {
                return;
            }
            $extra_arr = ExtraModel::factory()->where('t1.product_id', $_GET['product_id'])
                ->findAll()
                ->getData();
            $ExtraItemModel = ExtraItemModel::factory();
            $MultiLangModel = MultiLangModel::factory();
            foreach ($extra_arr as $k => $extra) {
                $extra_arr[$k]['i18n'] = $MultiLangModel->reset()->getMultiLang($extra['id'], 'Extra');
                $extra_arr[$k]['extra_items'] = $ExtraItemModel->reset()
                    ->where('t1.extra_id', $extra['id'])
                    ->orderBy('t1.price ASC')
                    ->findAll()
                    ->getData();
                foreach ($extra_arr[$k]['extra_items'] as $key => $val) {
                    $extra_arr[$k]['extra_items'][$key]['i18n'] = $MultiLangModel->reset()->getMultiLang($val['id'], 'ExtraItem');
                }
            }
            $this->set('extra_arr', $extra_arr);
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
        }
    }

    public function GetHistory()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $this->set('history_arr', HistoryModel::factory()->select('t1.*, t2.id AS sid, t3.content AS name')
                ->join('Stock', 't2.id=t1.record_id', 'left outer')
                ->join('MultiLang', "t3.model='Product' AND t3.foreign_id=t2.product_id AND t3.locale='" . $this->getLocaleId() . "' AND t3.field='name'", 'left outer')
                ->where('t1.table_name', StockModel::factory()->getTable())
                ->where('t2.product_id', $_GET['id'])
                ->orderBy('t1.created ASC')
                ->findAll()
                ->getData());
        }
    }

    public function GetProducts()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $ProductModel = ProductModel::factory();
            if (isset($_GET['product_id']) && (int) $_GET['product_id'] > 0) {
                $ProductModel->where('t1.id !=', $_GET['product_id']);
            }
            if (isset($_GET['copy'])) {
                switch ($_GET['copy']) {
                    case 'Attr':
                        $ProductModel->where(sprintf("t1.id IN (SELECT `product_id` FROM `%s` WHERE 1)", AttributeModel::factory()->getTable()));
                        break;
                    case 'Extra':
                        $ProductModel->where(sprintf("t1.id IN (SELECT `product_id` FROM `%s` WHERE 1)", ExtraModel::factory()->getTable()));
                        break;
                }
            }
            $this->set('arr', $ProductModel->select('t1.*, t2.content AS name')
                ->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getLocaleId() . "' AND t2.field='name'", 'left outer')
                ->orderBy('name ASC')
                ->findAll()
                ->getData());
        }
    }

    public function GetSimilar()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
                $ProductSimilarModel = ProductSimilarModel::factory()->join('Product', 't2.id=t1.similar_id', 'inner')
                    ->join('MultiLang', "t3.model='Product' AND t3.foreign_id=t2.id AND t3.locale='" . $this->getLocaleId() . "' AND t3.field='name'", 'left outer')
                    ->where('t1.product_id', $_GET['id']);
                $column = 'name';
                $direction = 'ASC';
                if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                    'ASC',
                    'DESC'
                ))) {
                    $column = $_GET['column'];
                    $direction = strtoupper($_GET['direction']);
                }
                $total = $ProductSimilarModel->findCount()->getData();
                $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
                $pages = ceil($total / $rowCount);
                $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
                $offset = ((int) $page - 1) * $rowCount;
                if ($page > $pages) {
                    $page = $pages;
                }
                $data = $ProductSimilarModel->select('t1.id, t2.sku, t2.status, t3.content AS name')
                    ->orderBy("$column $direction")
                    ->findAll()
                    ->getData();
                AppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
            }
        }
        exit();
    }
    
    public function SearchProducts()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $ProductModel = ProductModel::factory();
            if (isset($_GET['term'])) {
                $q = $ProductModel->escapeStr($_GET['term']);
                $q = str_replace(array(
                    '%',
                    '_'
                ), array(
                    '\%',
                    '\_'
                ), $q);
                $ProductModel->where("(t1.id LIKE '%$q%' OR t1.sku LIKE '%$q%' OR t1.id IN (SELECT `foreign_id` FROM `" . MultiLangModel::factory()->getTable() . "`  WHERE `field` = 'name'  AND `model` = 'Product'  AND `content` LIKE '%$q%'))");
            }
            $arr = $ProductModel->select('t1.*, t2.content AS name')
                ->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
                ->where('t1.id !=', $_GET['id'])
                ->where(sprintf("t1.id NOT IN (SELECT `similar_id` FROM `%s` WHERE `product_id` = '%u')", ProductSimilarModel::factory()->getTable(), $_GET['id']))
                ->orderBy('`name` ASC')
                ->findAll()
                ->getData();
            $_arr = array();
            foreach ($arr as $v) {
                $_arr[] = array(
                    'label' => $v['name'],
                    'value' => $v['id']
                );
            }
            AppController::jsonResponse($_arr);
        }
        exit();
    }

    public function Stock()
    {
        $this->checkLogin();
        if ($this->isAdmin()) {
            $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
            $this->appendJs('AdminProducts.js');
            $this->appendJs('index.php?controller=Admin&action=Messages', BASE_URL, true);
        } else {
            $this->set('status', 2);
        }
    }

    public function PrintStock()
    {
        $this->setLayout('ActionEmpty');
        $StockModel = StockModel::factory();
        if (isset($_POST['record']) && ! empty($_POST['record'])) {
            $StockModel->whereIn('t1.id', $_POST['record']);
        }
        $arr = $StockModel->select(sprintf("t1.*, t2.content AS name,  (SELECT `small_path` FROM `%1\$s`  WHERE `id` = `t1`.`image_id`  LIMIT 1) AS `pic`,  (SELECT GROUP_CONCAT(CONCAT_WS('~:~', `tm2`.`content`, `tm1`.`content`) SEPARATOR '~|~')  FROM `%2\$s` AS `tsa`  LEFT JOIN `%3\$s` AS `tm1` ON `tm1`.`model` = 'Attribute' AND `tm1`.`foreign_id` = `tsa`.`attribute_id` AND `tm1`.`field` = 'name' AND `tm1`.`locale` = '%4\$u'  LEFT JOIN `%3\$s` AS `tm2` ON `tm2`.`model` = 'Attribute' AND `tm2`.`foreign_id` = `tsa`.`attribute_parent_id` AND `tm2`.`field` = 'name' AND `tm2`.`locale` = '%4\$u'  WHERE `tsa`.`product_id` = `t1`.`product_id`  AND `tsa`.`stock_id` = `t1`.`id`  LIMIT 1) AS `stock_attr`  ", GalleryModel::factory()->getTable(), StockAttributeModel::factory()->getTable(), MultiLangModel::factory()->getTable(), $this->getLocaleId()))
            ->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.product_id AND t2.locale='" . $this->getLocaleId() . "' AND t2.field='name'", 'left outer')
            ->join('Product', 't3.id=t1.product_id', 'left outer')
            ->orderBy("`name` ASC")
            ->findAll()
            ->toArray('stock_attr', '~|~')
            ->getData();
        $this->set('arr', $arr);
        $this->resetCss()
            ->appendCss('reset.css')
            ->appendCss('print.css')
            ->appendCss('table.css', FRAMEWORK_LIBS_PATH . '/css/');
    }

    public function AddSimilar()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['product_id']) && isset($_POST['similar_id']) && (int) $_POST['product_id'] > 0 && (int) $_POST['similar_id'] > 0) {
                $insert_id = ProductSimilarModel::factory($_POST)->insert()->getInsertId();
                if ($insert_id !== FALSE && (int) $insert_id > 0) {
                    AppController::jsonResponse(array(
                        'status' => 'OK',
                        'code' => 200,
                        'text' => 'Similar product has been added.'
                    ));
                }
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 100,
                    'text' => 'Similar product has not been added.'
                ));
            } else {
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 101,
                    'text' => 'Missing parameters.'
                ));
            }
        }
        exit();
    }

    public function Index()
    {
        $this->checkLogin();
        $this->title = "Sản phẩm - Danh sách sản phẩm";
        if ($this->isAdmin()) {
            $this->set('category_arr', CategoryModel::factory()->getNode($this->getLocaleId(), 1));
            $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
            $this->appendJs('AdminProducts.js');
            $this->appendJs('index.php?controller=Admin&action=Messages', BASE_URL, true);
        } else {
            $this->set('status', 2);
        }
    }

    public function SaveProduct()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $ProductModel = ProductModel::factory();
            if (! in_array($_POST['column'], $ProductModel->getI18n())) {
                if ($_POST['column'] != "sku" || $ProductModel->where('t1.sku', $_POST['value'])
                    ->findCount()
                    ->getData() == 0) {
                    $ProductModel->reset()
                        ->where('id', $_GET['id'])
                        ->limit(1)
                        ->modifyAll(array(
                        $_POST['column'] => $_POST['value']
                    ));
                }
            } else {
                MultiLangModel::factory()->updateMultiLang(array(
                    $this->getLocaleId() => array(
                        $_POST['column'] => $_POST['value']
                    )
                ), $_GET['id'], 'Product');
            }
        }
        exit();
    }

    public function SaveStock()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $StockModel = StockModel::factory();
            if (! in_array($_POST['column'], $StockModel->getI18n())) {
                if ($_POST['column'] == 'qty') {
                    $before = $StockModel->reset()
                        ->find($_GET['id'])
                        ->getData();
                }
                $affected_rows = $StockModel->reset()
                    ->set('id', $_GET['id'])
                    ->modify(array(
                    $_POST['column'] => $_POST['value']
                ))
                    ->getAffectedRows();
                if ($_POST['column'] == 'qty' && $affected_rows == 1) {
                    $after = $StockModel->reset()
                        ->find($_GET['id'])
                        ->getData();
                    AppController::addToHistory($_GET['id'], $this->getUserId(), $StockModel->getTable(), $before, $after);
                }
            } else {
                MultiLangModel::factory()->updateMultiLang(array(
                    $this->getLocaleId() => array(
                        $_POST['column'] => $_POST['value']
                    )
                ), $_GET['id'], 'Stock');
            }
        }
        exit();
    }


    public function Update()
    {
        $this->checkLogin();
        $this->title = "Sản phẩm - Cập nhật sản phẩm";
        if ($this->isAdmin()) {
            $ExtraModel = ExtraModel::factory();
            $extraItemModel = ExtraItemModel::factory();
            $AttributeModel = AttributeModel::factory();
            $StockModel = StockModel::factory();
            $StockAttributeModel = StockAttributeModel::factory();
            $ProductCategoryModel = ProductCategoryModel::factory();
            $multiLangModel = MultiLangModel::factory();
            $ProductModel = ProductModel::factory();
            $post_max_size = UtilComponent::getPostMaxSize();
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > $post_max_size) {
                UtilComponent::redirect(BASE_URL . "index.php?controller=AdminProducts&action=Index&err=AP09");
            }
            if (isset($_POST['product_update'])) {
                $err = 'AP05';
                if (! isset($_POST['id']) || empty($_POST['id'])) {
                    UtilComponent::redirect(BASE_URL . 'index.php?controller=AdminProducts&action=Index&err=');
                }
                $product_arr = $ProductModel->find($_POST['id'])->getData();
                if (empty($product_arr)) {
                    UtilComponent::redirect(BASE_URL . 'index.php?controller=AdminProducts&action=Index&err=');
                }
                $sa_arr = $StockAttributeModel->select("t1.stock_id, GROUP_CONCAT(CONCAT_WS(':', t1.attribute_parent_id, t1.attribute_id) ORDER BY t1.attribute_parent_id ASC, t1.attribute_id ASC SEPARATOR '|') AS `str`")
                    ->join('Stock', 't2.id=t1.stock_id AND t2.product_id=t1.product_id', 'inner')
                    ->where('t2.product_id', $_POST['id'])
                    ->groupBy('t1.stock_id')
                    ->findAll()
                    ->getDataPair('stock_id', 'str');
                $i_arr = $u_arr = array();
                if (isset($_POST['stock_qty'])) {
                    foreach ($_POST['stock_qty'] as $k => $whatever) {
                        if (strpos($k, 'x_') === 0) {
                            if ((float) $_POST['stock_qty'][$k] > 0 && (float) $_POST['stock_price'][$k] > 0 && (int) $_POST['stock_image_id'][$k] > 0) {
                                $tmp = array();
                                if (isset($_POST['stock_attribute']) && isset($_POST['stock_attribute'][$k])) {
                                    foreach ($_POST['stock_attribute'][$k] as $attr_parent_id => $attr_id) {
                                        $tmp[] = $attr_parent_id . ":" . $attr_id;
                                    }
                                }
                                asort($tmp);
                                $i_arr[$k] = join("|", $tmp);
                            }
                        } else {
                            if (isset($_POST['stock_attribute']) && isset($_POST['stock_attribute'][$k])) {
                                $tmp = array();
                                foreach ($_POST['stock_attribute'][$k] as $attr_parent_id => $attr_id) {
                                    $tmp[] = $attr_parent_id . ":" . $attr_id;
                                }
                                asort($tmp);
                                $u_arr[$k] = join("|", $tmp);
                            }
                        }
                    }
                    foreach ($_POST['stock_qty'] as $k => $whatever) {
                        if (strpos($k, 'x_') === 0) {
                            if ((float) $_POST['stock_qty'][$k] > 0 && (float) $_POST['stock_price'][$k] >= 0 && (int) $_POST['stock_image_id'][$k] > 0 && ! in_array($i_arr[$k], $sa_arr)) {
                                $stock_id = $StockModel->reset()
                                    ->set('product_id', $_POST['id'])
                                    ->set('image_id', $_POST['stock_image_id'][$k])
                                    ->set('qty', $_POST['stock_qty'][$k])
                                    ->set('price', $_POST['stock_price'][$k])
                                    ->insert()
                                    ->getInsertId();
                                if ($stock_id !== false && (int) $stock_id > 0) {
                                    if (isset($_POST['stock_attribute']) && isset($_POST['stock_attribute'][$k])) {
                                        $StockAttributeModel->begin();
                                        foreach ($_POST['stock_attribute'][$k] as $attr_parent_id => $attr_id) {
                                            $StockAttributeModel->reset()
                                                ->set('stock_id', $stock_id)
                                                ->set('product_id', $_POST['id'])
                                                ->set('attribute_parent_id', $attr_parent_id)
                                                ->set('attribute_id', $attr_id)
                                                ->insert();
                                        }
                                        $StockAttributeModel->commit();
                                    }
                                }
                            }
                        } else {
                            $before = $StockModel->reset()
                                ->find($k)
                                ->getData();
                            if ($StockModel->reset()
                                ->set('id', $k)
                                ->modify(array(
                                'image_id' => $_POST['stock_image_id'][$k],
                                'qty' => $_POST['stock_qty'][$k],
                                'price' => $_POST['stock_price'][$k]
                            ))
                                ->getAffectedRows() == 1) {
                                $after = $StockModel->reset()
                                    ->find($k)
                                    ->getData();
                                AppController::addToHistory($k, $this->getUserId(), $StockModel->getTable(), $before, $after);
                            }
                            if (isset($u_arr[$k]) && ! in_array($u_arr[$k], $sa_arr) && (! isset($sa_arr[$k]) || (isset($sa_arr[$k]) && $sa_arr[$k] != $u_arr[$k]))) {
                                $StockAttributeModel->reset()
                                    ->where('stock_id', $k)
                                    ->eraseAll();
                                if (isset($_POST['stock_attribute']) && isset($_POST['stock_attribute'][$k])) {
                                    $StockAttributeModel->begin();
                                    foreach ($_POST['stock_attribute'][$k] as $attr_parent_id => $attr_id) {
                                        $StockAttributeModel->reset()
                                            ->set('stock_id', $k)
                                            ->set('product_id', $_POST['id'])
                                            ->set('attribute_parent_id', $attr_parent_id)
                                            ->set('attribute_id', $attr_id)
                                            ->insert();
                                    }
                                    $StockAttributeModel->commit();
                                }
                            }
                        }
                    }
                }
                $this->AttrHandle($_POST['id']);
                if (isset($_POST['is_digital'])) {
                    $this->DeleteProductAttr($product_arr['id']);
                    $this->DeleteStockAttr($product_arr['id']);
                    $statement = sprintf("DELETE FROM `%1\$s`  WHERE `product_id` = :product_id  AND `id` NOT IN (  SELECT `id`  FROM (  SELECT `id`  FROM `%1\$s`  WHERE `product_id` = :product_id  ORDER BY `id` ASC  LIMIT 1  ) `foo`);", $StockModel->getTable());
                    $StockModel->prepare($statement)->exec(array(
                        'product_id' => $product_arr['id']
                    ));
                }
                $extra_items = array();
                $extra_arr = $ExtraModel->where('t1.product_id', $_POST['id'])
                    ->findAll()
                    ->getDataPair('id', 'type');
                foreach ($extra_arr as $id => $type) {
                    if ($type == 'multi') {
                        $extra_items[$id] = $extraItemModel->reset()
                            ->where('t1.extra_id', $id)
                            ->findAll()
                            ->getDataPair('id', 'id');
                    }
                }
                if (isset($_POST['extra_type'])) {
                    foreach ($_POST['extra_type'] as $k => $type) {
                        if (strpos($k, 'x_') === 0) {
                            $data = array();
                            $data['product_id'] = $_POST['id'];
                            $data['type'] = $type;
                            switch ($type) {
                                case 'single':
                                    $data['price'] = $_POST['extra_price'][$k];
                                    break;
                                case 'multi':
                                    break;
                            }
                            $data['is_mandatory'] = isset($_POST['extra_is_mandatory'][$k]) ? 1 : 0;
                            $extra_id = $ExtraModel->reset()
                                ->setAttributes($data)
                                ->insert()
                                ->getInsertId();
                            if ($extra_id !== false && (int) $extra_id > 0) {
                                switch ($type) {
                                    case 'single':
                                        $tmp = $this->TurnI18n($_POST['i18n'], 'extra_name', $k);
                                        $multiLangModel->saveMultiLang($tmp, $extra_id, 'Extra');
                                        break;
                                    case 'multi':
                                        $tmp = $this->TurnI18n($_POST['i18n'], 'extra_title', $k);
                                        $multiLangModel->saveMultiLang($tmp, $extra_id, 'Extra');
                                        $edata = array();
                                        $edata['extra_id'] = $extra_id;
                                        foreach ($_POST['extra_price'][$k] as $index => $price) {
                                            $edata['price'] = $price;
                                            $ei_id = $extraItemModel->reset()
                                                ->setAttributes($edata)
                                                ->insert()
                                                ->getInsertId();
                                            if ($ei_id !== false && (int) $ei_id > 0) {
                                                $tmp = $this->TurnI18n($_POST['i18n'], 'extra_name', $k, $index);
                                                $multiLangModel->saveMultiLang($tmp, $ei_id, 'ExtraItem');
                                            }
                                        }
                                        break;
                                }
                            }
                        } else {
                            $ExtraModel->reset()
                                ->set('id', $k)
                                ->modify(array(
                                'type' => $type,
                                'price' => $type == 'single' ? $_POST['extra_price'][$k] : ':NULL',
                                'is_mandatory' => isset($_POST['extra_is_mandatory'][$k]) ? 1 : 0
                            ));
                            switch ($type) {
                                case 'multi':
                                    $tmp = $this->TurnI18n($_POST['i18n'], 'extra_title', $k);
                                    if ($extra_arr[$k] == 'single') {
                                        $multiLangModel->reset()
                                            ->where('model', 'Extra')
                                            ->where('foreign_id', $k)
                                            ->eraseAll();
                                        $multiLangModel->saveMultiLang($tmp, $k, 'Extra');
                                    } else {
                                        $multiLangModel->updateMultiLang($tmp, $k, 'Extra');
                                    }
                                    $diff = array_diff($extra_items[$k], array_keys($_POST['extra_price'][$k]));
                                    if (count($diff) > 0) {
                                        $extraItemModel->reset()
                                            ->whereIn('id', $diff)
                                            ->eraseAll();
                                        $multiLangModel->reset()
                                            ->where('model', 'ExtraItem')
                                            ->whereIn('foreign_id', $diff)
                                            ->eraseAll();
                                    }
                                    foreach ($_POST['extra_price'][$k] as $index => $price) {
                                        if (strpos($index, 'y_') === 0) {
                                            $ei_id = $extraItemModel->reset()
                                                ->setAttributes(array(
                                                'extra_id' => $k,
                                                'price' => $price
                                            ))
                                                ->insert()
                                                ->getInsertId();
                                            if ($ei_id !== false && (int) $ei_id > 0) {
                                                $tmp = $this->TurnI18n($_POST['i18n'], 'extra_name', $k, $index);
                                                $multiLangModel->saveMultiLang($tmp, $ei_id, 'ExtraItem');
                                            }
                                        } else {
                                            $extraItemModel->reset()
                                                ->set('id', $index)
                                                ->modify(array(
                                                'price' => $price
                                            ));
                                            $tmp = $this->TurnI18n($_POST['i18n'], 'extra_name', $k, $index);
                                            $multiLangModel->updateMultiLang($tmp, $index, 'ExtraItem');
                                        }
                                    }
                                    break;
                                case 'single':
                                    if ($extra_arr[$k] == 'multi') {
                                        $ei_ids = $extraItemModel->reset()
                                            ->where('extra_id', $k)
                                            ->findAll()
                                            ->getDataPair(NULL, 'id');
                                        if (! empty($ei_ids)) {
                                            $extraItemModel->eraseAll();
                                            $multiLangModel->reset()
                                                ->where('model', 'ExtraItem')
                                                ->whereIn('foreign_id', $ei_ids)
                                                ->eraseAll();
                                        }
                                        $multiLangModel->reset()
                                            ->where('model', 'Extra')
                                            ->where('foreign_id', $k)
                                            ->where('field', 'extra_title')
                                            ->eraseAll();
                                    }
                                    $tmp = $this->TurnI18n($_POST['i18n'], 'extra_name', $k);
                                    $multiLangModel->updateMultiLang($tmp, $k, 'Extra');
                                    break;
                            }
                        }
                    }
                }
                $ProductCategoryModel->where('product_id', $_POST['id'])->eraseAll();
                if (isset($_POST['category_id']) && count($_POST['category_id']) > 0) {
                    $ProductCategoryModel->begin();
                    foreach ($_POST['category_id'] as $category_id) {
                        $ProductCategoryModel->reset()
                            ->set('product_id', $_POST['id'])
                            ->set('category_id', $category_id)
                            ->insert();
                    }
                    $ProductCategoryModel->commit();
                }
                $data = array();
                $data['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
                $data['is_digital'] = isset($_POST['is_digital']) ? 1 : 0;
                if (isset($_POST['is_digital'])) {
                    if (isset($_POST['digital_choose'])) {
                        switch ($_POST['digital_choose']) {
                            case 1:
                                if (isset($_FILES['digital_file'])) {
                                    if ($_FILES['digital_file']['error'] == 0) {
                                        $upload = new UploadComponent();
                                        if ($upload->load($_FILES['digital_file'])) {
                                            $name = $upload->getFile('name');
                                            $file = UPLOAD_PATH . 'digital/' . md5(uniqid(rand(), true)) . "." . $upload->getExtension();
                                            if ($upload->save($file)) {
                                                $data['digital_file'] = $file;
                                                $data['digital_name'] = $name;
                                            }
                                        }
                                    } else if ($_FILES['digital_file']['error'] != 4) {
                                        $err = 'AP10';
                                        $data['is_digital'] = 0;
                                    }
                                }
                                break;
                            case 2:
                                if (file_exists($_POST['digital_file'])) {
                                    $data['digital_file'] = $_POST['digital_file'];
                                    $data['digital_name'] = basename($_POST['digital_file']);
                                } else {
                                    $err = 'AP11';
                                    $data['is_digital'] = 0;
                                }
                                break;
                        }
                    }
                    if ($err == 'AP05') {
                        $data['digital_expire'] = sprintf("%s:%s:00", $_POST['hour'], $_POST['minute']);
                    } else {
                        $data['digital_file'] = ':NULL';
                        $data['digital_name'] = ':NULL';
                        $data['digital_expire'] = ':NULL';
                    }
                } else {
                    $data['digital_file'] = ':NULL';
                    $data['digital_name'] = ':NULL';
                    $data['digital_expire'] = ':NULL';
                }
                $ProductModel->reset()
                    ->set('id', $_POST['id'])
                    ->modify(array_merge($_POST, $data));
                if (isset($_POST['i18n'])) {
                    foreach ($_POST['i18n'] as $locale_id => $locale_arr) {
                        unset($_POST['i18n'][$locale_id]['extra_title']);
                        unset($_POST['i18n'][$locale_id]['extra_name']);
                    }
                    $multiLangModel->updateMultiLang($_POST['i18n'], $_POST['id'], 'Product');
                    $locale_arr = LocaleModel::factory()->select('t1.*')
                    ->orderBy('t1.sort ASC')
                    ->findAll()
                    ->getData();
                    $uuidNumber = $this->generateRamdomNumber(6);
                    foreach ($locale_arr as $locale) {
                        $category_arr = CategoryModel::factory ()->getNode ( $locale['id'], 1);
                        $pc_arr = ProductCategoryModel::factory()->where('product_id', $_POST['id'])->findAll()->getDataPair(null, 'category_id');
                        
                        if (empty($_POST['i18n'][$locale['id']]['url'])) {
                            $friendlyUrl = $this->createFriendlyUrl($category_arr, $pc_arr, $_POST['i18n'][$locale['id']['name']]);
                            $friendlyUrl = $locale['language_iso'].'/'.$friendlyUrl.'-'.$uuidNumber;
                        } else {
                            $friendlyUrl = $_POST['i18n'][$locale['id']]['url'];
                        }
                        
                        $this->createOrUpdateDynamicRouter(
                            $friendlyUrl,
                            RouterModel::TYPE_PRODUCT,
                            self::FRONTEND_PRODUCT_CONTROLLER,
                            "product",
                            "id",
                            $_POST['id'],
                            $locale['id']);
                    }
                }
                $productTagModel = ProductTagModel::factory ();
                if (isset ( $_POST ['tag_id'] ) && count ( $_POST ['tag_id'] ) > 0) {
                    ProductTagModel::factory ()->set ( 'product_id', $_POST['id'] )->eraseAll();
                    $productTagModel->begin ();
                    foreach ( $_POST ['tag_id'] as $tagId ) {
                        $productTagModel->reset ()->set ( 'product_id', $_POST['id'] )->set ( 'tag_id', $tagId )->insert ();
                    }
                    $productTagModel->commit ();
                }
                UtilComponent::redirect(sprintf("%s?controller=AdminProducts&action=Update&id=%u&tab=%u&err=%s", $_SERVER['PHP_SELF'], @$_POST['id'], @$_POST['tab'], $err));
            } else {
                $arr = $ProductModel->find($_GET['id'])->getData();
                if (count($arr) === 0) {
                    UtilComponent::redirect(sprintf("%s?controller=AdminProducts&action=Index&err=%s", $_SERVER['PHP_SELF'], 'AP08'));
                }
                $multiLangModel = MultiLangModel::factory();
                $arr['i18n'] = $multiLangModel->getMultiLang($arr['id'], 'Product');
                $routers = RouterModel::factory()
                ->where('t1.type', RouterModel::TYPE_PRODUCT)
                ->where('t1.controller', self::FRONTEND_PRODUCT_CONTROLLER)
                ->where('t1.action', "Product")
                ->where('t1.foreign_id', $_GET['id'])
                ->findAll()
                ->getData();
                if (!empty($routers)) {
                    foreach ($routers as $router) {
                        $arr['i18n'][$router['locale_id']]['url'] = $router['url'];
                    }
                }
                $this->set('arr', $arr);
                $this->set('category_arr', CategoryModel::factory()->getNode($this->getLocaleId(), 1));
                $this->set('pc_arr', ProductCategoryModel::factory()->where('t1.product_id', $arr['id'])
                    ->orderBy('t1.category_id ASC')
                    ->findAll()
                    ->getDataPair('category_id', 'category_id'));
                $extra_arr = ExtraModel::factory()->where('t1.product_id', $arr['id'])
                    ->findAll()
                    ->getData();
                $extraItemModel = ExtraItemModel::factory();
                foreach ($extra_arr as $k => $extra) {
                    $extra_arr[$k]['i18n'] = $multiLangModel->reset()->getMultiLang($extra['id'], 'Extra');
                    $extra_arr[$k]['extra_items'] = $extraItemModel->reset()
                        ->where('t1.extra_id', $extra['id'])
                        ->orderBy('t1.price ASC')
                        ->findAll()
                        ->getData();
                    foreach ($extra_arr[$k]['extra_items'] as $key => $val) {
                        $extra_arr[$k]['extra_items'][$key]['i18n'] = $multiLangModel->reset()->getMultiLang($val['id'], 'ExtraItem');
                    }
                }
                $this->set('extra_arr', $extra_arr);
                $attr_arr = array();
                $a_arr = AttributeModel::factory()->select('t1.id, t1.product_id, t1.parent_id, t1.hash, t2.content AS name')
                    ->join('MultiLang', "t2.model='Attribute' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId() . "'", 'left outer')
                    ->where('t1.product_id', $arr['id'])
                    ->orderBy('t1.`order_group`, `order_item` ASC')
                    ->findAll()
                    ->getData();
                foreach ($a_arr as $attr) {
                    $attr['i18n'] = $multiLangModel->reset()->getMultiLang($attr['id'], 'Attribute');
                    if ((int) $attr['parent_id'] === 0) {
                        $attr_arr[$attr['id']] = $attr;
                    } else {
                        if (! isset($attr_arr[$attr['parent_id']]['child'])) {
                            $attr_arr[$attr['parent_id']]['child'] = array();
                        }
                        $attr_arr[$attr['parent_id']]['child'][] = $attr;
                    }
                }
                $this->set('attr_arr', array_values($attr_arr));
                $stock_arr = StockModel::factory()->select('t1.*, t2.small_path')
                    ->join("Gallery", 't2.id=t1.image_id', 'left outer')
                    ->where('t1.product_id', $arr['id'])
                    ->findAll()
                    ->getData();
                $StockAttributeModel = StockAttributeModel::factory();
                foreach ($stock_arr as $k => $stock) {
                    $stock_arr[$k]['attrs'] = $StockAttributeModel->reset()
                        ->where('t1.stock_id', $stock['id'])
                        ->orderBy('t1.attribute_id ASC')
                        ->findAll()
                        ->getDataPair('attribute_parent_id', 'attribute_id');
                }
                $this->set('stock_arr', $stock_arr);
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
                $gallery_arr = GalleryModel::factory()->where('t1.foreign_id', $_GET['id'])
                    ->orderBy('ISNULL(t1.sort), t1.sort ASC, t1.id ASC')
                    ->findAll()
                    ->getData();
                $this->set('gallery_arr', $gallery_arr);
                $this->set('tag_arr', TagModel::factory()->select("t1.*, t2.content as name")->join ( 'MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Tag' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'name'", 'left' )->findAll()->getData());
                $this->set ( 'mc_tag_arr', ProductTagModel::factory ()->where ( 't1.product_id', $arr ['id'] )->orderBy ( 't1.tag_id ASC' )->findAll ()->getDataPair ( 'tag_id', 'tag_id' ) );
                
                $this->appendCss('gallery.css', Objects::getConstant('Gallery', 'PLUGIN_CSS_PATH'));
                $this->appendJs('ajaxupload.js', Objects::getConstant('Gallery', 'PLUGIN_JS_PATH'));
                $this->appendJs('jquery.gallery.js', Objects::getConstant('Gallery', 'PLUGIN_JS_PATH'));
                $this->appendJs ( 'chosen.jquery.js', THIRD_PARTY_PATH . 'harvest/chosen/' );
                $this->appendCss ( 'chosen.css', THIRD_PARTY_PATH . 'harvest/chosen/');
                $this->appendJs('jquery.datagrid.js', FRAMEWORK_LIBS_PATH . '/js/');
                $this->appendJs('index.php?controller=Admin&action=Messages', BASE_URL, true);
                $this->appendJs('tinymce.min.js', THIRD_PARTY_PATH . 'tiny_mce/');
                $this->appendJs('jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('jquery.multilang.js', FRAMEWORK_LIBS_PATH . '/js/');
                $this->appendJs('jquery.tipsy.js', THIRD_PARTY_PATH . 'tipsy/');
                $this->appendCss('jquery.tipsy.css', THIRD_PARTY_PATH . 'tipsy/');
                $this->appendJs('AdminProducts.js');
            }
        } else {
            $this->set('status', 2);
        }
    }


    private function TurnI18n($data, $key, $id, $index = NULL, $new_key = NULL)
    {
        $arr = array();
        $arr_index = is_null($new_key) ? $key : $new_key;
        foreach ($data as $locale => $locale_arr) {
            $arr[$locale] = array(
                $arr_index => is_null($index) ? (isset($locale_arr[$key]) && isset($locale_arr[$key][$id]) ? $locale_arr[$key][$id] : NULL) : (isset($locale_arr[$key]) && isset($locale_arr[$key][$id]) && isset($locale_arr[$key][$id][$index]) ? $locale_arr[$key][$id][$index] : NULL)
            );
        }
        return $arr;
    }


    public function LoadImages()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $arr = array();
            if (isset($_GET['product_id']) && (int) $_GET['product_id'] > 0) {
                $arr = GalleryModel::factory()->where('t1.foreign_id', $_GET['product_id'])
                    ->orderBy('ISNULL(t1.sort), t1.sort ASC, t1.id ASC')
                    ->findAll()
                    ->getData();
            } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                $arr = GalleryModel::factory()->where('t1.hash', $_GET['hash'])
                    ->orderBy('ISNULL(t1.sort), t1.sort ASC, t1.id ASC')
                    ->findAll()
                    ->getData();
            }
            $this->set('arr', $arr);
        }
    }
}
?>