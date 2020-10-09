<?php
namespace App\Plugins\Gallery\Controllers;

use App\Plugins\Gallery\Models\GalleryModel;
use Core\Framework\Components\ImageComponent;
use App\Controllers\AppController;
use App\Controllers\Components\UtilComponent;
use App\Models\MultiLangModel;
use App\Plugins\Locale\Models\LocaleModel;

class GalleryController extends GalleryAppController
{
    
    private $imageSizes = array(
        'small' => array(
            90,
            90
        ),
        'medium' => array(
            800,
            800
        )
    );
    
    private $imageFiles = array(
        'small_path',
        'medium_path',
        'large_path',
        'source_path'
    );
    
    private $imageCrop = true;
    
    private $imageFillColor = array(
        255,
        255,
        255
    );
    
    public function __construct()
    {
        parent::__construct();
        if (defined("GALLERY_SMALL") && strpos(GALLERY_SMALL, ",") !== FALSE) {
            $this->imageSizes['small'] = explode(",", preg_replace('/\s+/', '', GALLERY_SMALL));
        }
        if (defined("GALLERY_MEDIUM") && strpos(GALLERY_MEDIUM, ",") !== FALSE) {
            $this->imageSizes['medium'] = explode(",", preg_replace('/\s+/', '', GALLERY_MEDIUM));
        }
        if (defined("GALLERY_FILL_COLOR") && strpos(GALLERY_FILL_COLOR, ",") !== FALSE) {
            $this->imageFillColor = explode(",", preg_replace('/\s+/', '', GALLERY_FILL_COLOR));
        }
        if (defined("GALLERY_CROP")) {
            $this->imageCrop = (bool) GALLERY_CROP;
        }
    }
    
    
    private function DeleteImage($arr)
    {
        if (! is_array($arr)) {
            $this->log('Given data is not an array');
            return FALSE;
        }
        foreach ($this->imageFiles as $file) {
            @clearstatcache();
            if (! empty($arr[$file]) && is_file($arr[$file])) {
                @unlink($arr[$file]);
            } else {
                $this->log(sprintf("%s is empty or not a file", $arr[$file]));
            }
        }
    }
    
    
    private function BuildFromSource(&$Image, $item, $watermark = NULL, $watermarkPosition = "cc", $uploadPath = null)
    {
        $data = array();
        if (empty($item['source_path'])) {
            $this->log('source_path is empty');
            return FALSE;
        }
        foreach ($this->imageSizes as $key => $d) {
            if (isset($item[$key . '_path']) && ! empty($item[$key . '_path'])) {
                $dst = $item[$key . '_path'];
            } else {
                $dst = str_replace($uploadPath . 'source/', $uploadPath . $key . '/', $item['source_path']);
            }
            $Image->loadImage($item['source_path']);
            if ($this->imageCrop) {
                $Image->setFillColor($this->imageFillColor)->resizeSmart($d[0], $d[1]);
            } else {
                $Image->resizeToWidth($d[0]);
            }
            if (! empty($watermark) && $key != 'small') {
                $Image->setWatermark($watermark, $watermarkPosition);
            }
            $Image->saveImage($dst);
            $data[$key . '_path'] = $dst;
            $data[$key . '_size'] = filesize($dst);
            $size = getimagesize($dst);
            $data[$key . '_width'] = $size[0];
            $data[$key . '_height'] = $size[1];
        }
        $dst = str_replace($uploadPath . 'source/', $uploadPath . 'large/', $item['source_path']);
        $Image->loadImage($item['source_path']);
        if (! empty($watermark)) {
            $Image->setWatermark($watermark, $watermarkPosition);
        }
        $Image->saveImage($dst);
        $data['large_path'] = $dst;
        $data['large_size'] = filesize($dst);
        $size = getimagesize($dst);
        $data['large_width'] = $size[0];
        $data['large_height'] = $size[1];
        return $data;
    }
    
    
    
    
    public function CompressGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if ((isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) ^ (isset($_GET['hash']) && ! empty($_GET['hash']))) {
                $GalleryModel = GalleryModel::factory();
                if (isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) {
                    $GalleryModel->where('foreign_id', $_GET['foreign_id']);
                } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                    $GalleryModel->where('hash', $_GET['hash']);
                }
                $arr = $GalleryModel->findAll()->getData();
                if (count($arr) > 0) {
                    $_POST['large_path_compression'] = $_POST['small_path_compression'];
                    $_POST['medium_path_compression'] = $_POST['small_path_compression'];
                    $Image = new ImageComponent();
                    if ($Image->getErrorCode() !== 200) {
                        foreach ($arr as $item) {
                            $data = array();
                            foreach ($this->imageFiles as $file) {
                                if (! empty($item[$file])) {
                                    $compression = isset($_POST[$file . '_compression']) ? (int) $_POST[$file . '_compression'] : 60;
                                    $Image->loadImage($item[$file])->saveImage($item[$file], NULL, $compression);
                                    @clearstatcache();
                                    $data[str_replace('_path', '_size', $file)] = filesize($item[$file]);
                                }
                            }
                            if (count($data) > 0) {
                                $GalleryModel->reset()
                                ->set('id', $item['id'])
                                ->modify($data);
                            }
                        }
                    }
                } else {
                    $this->log('No image records found in DB');
                }
            } else {
                $this->log("\$_GET['foreign_id'] is not set or has incorrect value");
            }
        }
        exit();
    }
    
    public function CompressProductGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if ((isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) ^ (isset($_GET['hash']) && ! empty($_GET['hash']))) {
                $GalleryModel = GalleryModel::factory();
                if (isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) {
                    $GalleryModel->where('foreign_id', $_GET['foreign_id']);
                } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                    $GalleryModel->where('hash', $_GET['hash']);
                }
                $arr = $GalleryModel->findAll()->getData();
                if (count($arr) > 0) {
                    $_POST['large_path_compression'] = $_POST['small_path_compression'];
                    $_POST['medium_path_compression'] = $_POST['small_path_compression'];
                    $Image = new ImageComponent();
                    if ($Image->getErrorCode() !== 200) {
                        foreach ($arr as $item) {
                            $data = array();
                            foreach ($this->imageFiles as $file) {
                                if (! empty($item[$file])) {
                                    $compression = isset($_POST[$file . '_compression']) ? (int) $_POST[$file . '_compression'] : 60;
                                    $Image->loadImage($item[$file])->saveImage($item[$file], NULL, $compression);
                                    @clearstatcache();
                                    $data[str_replace('_path', '_size', $file)] = filesize($item[$file]);
                                }
                            }
                            if (count($data) > 0) {
                                $GalleryModel->reset()
                                ->set('id', $item['id'])
                                ->modify($data);
                            }
                        }
                    }
                } else {
                    $this->log('No image records found in DB');
                }
            } else {
                $this->log("\$_GET['foreign_id'] is not set or has incorrect value");
            }
        }
        exit();
    }
    
    public function CompressSlider()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if ((isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) ^ (isset($_GET['hash']) && ! empty($_GET['hash']))) {
                $GalleryModel = GalleryModel::factory();
                if (isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) {
                    $GalleryModel->where('foreign_id', $_GET['foreign_id']);
                } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                    $GalleryModel->where('hash', $_GET['hash']);
                }
                $arr = $GalleryModel->findAll()->getData();
                if (count($arr) > 0) {
                    $_POST['large_path_compression'] = $_POST['small_path_compression'];
                    $_POST['medium_path_compression'] = $_POST['small_path_compression'];
                    $Image = new ImageComponent();
                    if ($Image->getErrorCode() !== 200) {
                        foreach ($arr as $item) {
                            $data = array();
                            foreach ($this->imageFiles as $file) {
                                if (! empty($item[$file])) {
                                    $compression = isset($_POST[$file . '_compression']) ? (int) $_POST[$file . '_compression'] : 60;
                                    $Image->loadImage($item[$file])->saveImage($item[$file], NULL, $compression);
                                    @clearstatcache();
                                    $data[str_replace('_path', '_size', $file)] = filesize($item[$file]);
                                }
                            }
                            if (count($data) > 0) {
                                $GalleryModel->reset()
                                ->set('id', $item['id'])
                                ->modify($data);
                            }
                        }
                    }
                } else {
                    $this->log('No image records found in DB');
                }
            } else {
                $this->log("\$_GET['foreign_id'] is not set or has incorrect value");
            }
        }
        exit();
    }
    
    
    public function CropGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['id']) && (int) $_POST['id'] > 0) {
                $GalleryModel = GalleryModel::factory();
                $arr = $GalleryModel->find($_POST['id'])->getData();
                if (count($arr) > 0) {
                    $Image = new ImageComponent();
                    if ($Image->getErrorCode() !== 200) {
                        $Image->loadImage($arr[$_POST['src']]);
                        if ($_POST['dst'] == 'large_path') {
                            $Image->crop($_POST['x'], $_POST['y'], $_POST['w'], $_POST['h'], $_POST['w'], $_POST['h']);
                        } else {
                            $Image->crop($_POST['x'], $_POST['y'], $this->imageSizes[str_replace('_path', '', $_POST['dst'])][0], $this->imageSizes[str_replace('_path', '', $_POST['dst'])][1], $_POST['w'], $_POST['h']);
                        }
                        $Image->saveImage($arr[$_POST['dst']]);
                    } else {
                        $this->log('GD is not loaded');
                    }
                    $key = str_replace('_path', '', $_POST['dst']);
                    $data = array();
                    $data[$key . '_size'] = filesize($arr[$_POST['dst']]);
                    $size = @getimagesize($arr[$_POST['dst']]);
                    if ($size !== false) {
                        $data[$key . '_width'] = $size[0];
                        $data[$key . '_height'] = $size[1];
                    }
                    $GalleryModel->reset()
                    ->where('id', $arr['id'])
                    ->limit(1)
                    ->modifyAll($data);
                } else {
                    $this->log('Image record not found in DB');
                }
            } else {
                $this->log("\$_POST['id'] is not set or has incorrect value");
            }
        }
        exit();
    }
    
    public function CropSlider()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['id']) && (int) $_POST['id'] > 0) {
                $GalleryModel = GalleryModel::factory();
                $arr = $GalleryModel->find($_POST['id'])->getData();
                if (count($arr) > 0) {
                    $Image = new ImageComponent();
                    if ($Image->getErrorCode() !== 200) {
                        $Image->loadImage($arr[$_POST['src']]);
                        if ($_POST['dst'] == 'large_path') {
                            $Image->crop($_POST['x'], $_POST['y'], $_POST['w'], $_POST['h'], $_POST['w'], $_POST['h']);
                        } else {
                            $Image->crop($_POST['x'], $_POST['y'], $this->imageSizes[str_replace('_path', '', $_POST['dst'])][0], $this->imageSizes[str_replace('_path', '', $_POST['dst'])][1], $_POST['w'], $_POST['h']);
                        }
                        $Image->saveImage($arr[$_POST['dst']]);
                    } else {
                        $this->log('GD is not loaded');
                    }
                    $key = str_replace('_path', '', $_POST['dst']);
                    $data = array();
                    $data[$key . '_size'] = filesize($arr[$_POST['dst']]);
                    $size = @getimagesize($arr[$_POST['dst']]);
                    if ($size !== false) {
                        $data[$key . '_width'] = $size[0];
                        $data[$key . '_height'] = $size[1];
                    }
                    $GalleryModel->reset()
                    ->where('id', $arr['id'])
                    ->limit(1)
                    ->modifyAll($data);
                } else {
                    $this->log('Image record not found in DB');
                }
            } else {
                $this->log("\$_POST['id'] is not set or has incorrect value");
            }
        }
        exit();
    }
    
    
    public function EmptyGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if ((isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) ^ (isset($_GET['hash']) && ! empty($_GET['hash']))) {
                $GalleryModel = GalleryModel::factory();
                if (isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) {
                    $GalleryModel->where('foreign_id', $_GET['foreign_id']);
                } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                    $GalleryModel->where('hash', $_GET['hash']);
                }
                $arr = $GalleryModel->findAll()->getData();
                foreach ($arr as $item) {
                    $this->DeleteImage($item);
                }
                $GalleryModel->eraseAll();
                $resp = array(
                    'code' => 200
                );
            } else {
                $resp = array(
                    'code' => 100
                );
                $this->log("\$_GET['foreign_id'] is not set or has incorrect value");
            }
            AppController::jsonResponse($resp);
        }
        exit();
    }
    
    
    public function EmptySlider()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if ((isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) ^ (isset($_GET['hash']) && ! empty($_GET['hash']))) {
                $GalleryModel = GalleryModel::factory();
                if (isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) {
                    $GalleryModel->where('foreign_id', $_GET['foreign_id']);
                } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                    $GalleryModel->where('hash', $_GET['hash']);
                }
                $arr = $GalleryModel->findAll()->getData();
                foreach ($arr as $item) {
                    $this->DeleteImage($item);
                }
                $GalleryModel->eraseAll();
                $resp = array(
                    'code' => 200
                );
            } else {
                $resp = array(
                    'code' => 100
                );
                $this->log("\$_GET['foreign_id'] is not set or has incorrect value");
            }
            AppController::jsonResponse($resp);
        }
        exit();
    }
    
    
    public function DeleteGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['id']) && (int) $_POST['id'] > 0) {
                $GalleryModel = GalleryModel::factory();
                $arr = $GalleryModel->find($_POST['id'])->getData();
                if (count($arr) > 0) {
                    $this->DeleteImage($arr);
                    $GalleryModel->erase();
                    $resp = array(
                        'code' => 200
                    );
                } else {
                    $this->log("Image record not found in DB");
                    $resp = array(
                        'code' => 101
                    );
                }
            } else {
                $this->log("\$_POST['id'] is not set or has incorrect value");
                $resp = array(
                    'code' => 100
                );
            }
            AppController::jsonResponse($resp);
        }
        exit();
    }
    
    public function DeleteProductGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['id']) && (int) $_POST['id'] > 0) {
                $GalleryModel = GalleryModel::factory();
                $arr = $GalleryModel->find($_POST['id'])->getData();
                if (count($arr) > 0) {
                    $this->DeleteImage($arr);
                    $GalleryModel->erase();
                    $resp = array(
                        'code' => 200
                    );
                } else {
                    $this->log("Image record not found in DB");
                    $resp = array(
                        'code' => 101
                    );
                }
            } else {
                $this->log("\$_POST['id'] is not set or has incorrect value");
                $resp = array(
                    'code' => 100
                );
            }
            AppController::jsonResponse($resp);
        }
        exit();
    }
    
    
    public function DeleteSlider()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['id']) && (int) $_POST['id'] > 0) {
                $GalleryModel = GalleryModel::factory();
                $arr = $GalleryModel->find($_POST['id'])->getData();
                if (count($arr) > 0) {
                    $this->DeleteImage($arr);
                    $GalleryModel->erase();
                    $resp = array(
                        'code' => 200
                    );
                } else {
                    $this->log("Image record not found in DB");
                    $resp = array(
                        'code' => 101
                    );
                }
            } else {
                $this->log("\$_POST['id'] is not set or has incorrect value");
                $resp = array(
                    'code' => 100
                );
            }
            AppController::jsonResponse($resp);
        }
        exit();
    }
    
    
    public function GetGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $GalleryModel = GalleryModel::factory();
            if (isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) {
                $GalleryModel->where('t1.foreign_id', $_GET['foreign_id']);
            } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                $GalleryModel->where('t1.hash', $_GET['hash']);
            } else {
                $GalleryModel->where('t1.id < 0');
            }
            $column = 'sort';
            $direction = 'ASC';
            if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                'ASC',
                'DESC'
            ))) {
                $column = $_GET['column'];
                $direction = strtoupper($_GET['direction']);
            }
            $error = NULL;
            if (isset($_GET['error'])) {
                $error = $_GET['error'];
            }
            $total = $GalleryModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 100;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $data = $GalleryModel->orderBy("$column $direction")
            ->limit($rowCount, $offset)
            ->findAll()
            ->getData();
            $originals_size = $thumbs_size = 0;
            foreach ($data as $item) {
                $originals_size += (int) $item['source_size'];
                $thumbs_size += (int) $item['small_size'];
                $thumbs_size += (int) $item['medium_size'];
                $thumbs_size += (int) $item['large_size'];
            }
            AppController::jsonResponse(compact('data', 'originals_size', 'thumbs_size', 'total', 'pages', 'page', 'rowCount', 'column', 'direction', 'error'));
        }
        exit();
    }
    
    public function GetProductGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $GalleryModel = GalleryModel::factory();
            if (isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) {
                $GalleryModel->where('t1.foreign_id', $_GET['foreign_id']);
            } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                $GalleryModel->where('t1.hash', $_GET['hash']);
            } else {
                $GalleryModel->where('t1.id < 0');
            }
            $column = 'sort';
            $direction = 'ASC';
            if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                'ASC',
                'DESC'
            ))) {
                $column = $_GET['column'];
                $direction = strtoupper($_GET['direction']);
            }
            $error = NULL;
            if (isset($_GET['error'])) {
                $error = $_GET['error'];
            }
            $total = $GalleryModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 100;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $data = $GalleryModel->orderBy("$column $direction")
            ->limit($rowCount, $offset)
            ->findAll()
            ->getData();
            $originals_size = $thumbs_size = 0;
            foreach ($data as $item) {
                $originals_size += (int) $item['source_size'];
                $thumbs_size += (int) $item['small_size'];
                $thumbs_size += (int) $item['medium_size'];
                $thumbs_size += (int) $item['large_size'];
            }
            AppController::jsonResponse(compact('data', 'originals_size', 'thumbs_size', 'total', 'pages', 'page', 'rowCount', 'column', 'direction', 'error'));
        }
        exit();
    }
    
    public function GetSliderGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $GalleryModel = GalleryModel::factory();
            if (isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) {
                $GalleryModel->where('t1.foreign_id', $_GET['foreign_id']);
            } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                $GalleryModel->where('t1.hash', $_GET['hash']);
            } else {
                $GalleryModel->where('t1.id < 0');
            }
            $column = 'sort';
            $direction = 'ASC';
            if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array(
                'ASC',
                'DESC'
            ))) {
                $column = $_GET['column'];
                $direction = strtoupper($_GET['direction']);
            }
            $error = NULL;
            if (isset($_GET['error'])) {
                $error = $_GET['error'];
            }
            $total = $GalleryModel->findCount()->getData();
            $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 100;
            $pages = ceil($total / $rowCount);
            $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
            $offset = ((int) $page - 1) * $rowCount;
            if ($page > $pages) {
                $page = $pages;
            }
            $data = $GalleryModel->orderBy("$column $direction")
            ->limit($rowCount, $offset)
            ->findAll()
            ->getData();
            $originals_size = $thumbs_size = 0;
            foreach ($data as $item) {
                $originals_size += (int) $item['source_size'];
                $thumbs_size += (int) $item['small_size'];
                $thumbs_size += (int) $item['medium_size'];
                $thumbs_size += (int) $item['large_size'];
            }
            AppController::jsonResponse(compact('data', 'originals_size', 'thumbs_size', 'total', 'pages', 'page', 'rowCount', 'column', 'direction', 'error'));
        }
        exit();
    }
    
    
    public function Index()
    {
        $this->checkLogin();
    }
    
    private function Rebuild($foreign_id = NULL, $hash = NULL)
    {
        if ((isset($foreign_id) && (int) $foreign_id > 0) ^ (isset($hash) && ! empty($hash))) {
            $Image = new ImageComponent();
            if ($Image->getErrorCode() !== 200) {
                $GalleryModel = GalleryModel::factory();
                if (isset($foreign_id) && (int) $foreign_id > 0) {
                    $GalleryModel->where('foreign_id', $foreign_id);
                } elseif (isset($hash) && ! empty($hash)) {
                    $GalleryModel->where('hash', $hash);
                }
                $arr = $GalleryModel->findAll()->getData();
                foreach ($arr as $item) {
                    $data = array();
                    $data = $this->BuildFromSource($Image, $item);
                    $GalleryModel->reset()
                    ->set('id', $item['id'])
                    ->modify($data);
                }
            } else {
                $this->log('GD extension is not loaded');
            }
        } else {
            $this->log("\$_GET['foreign_id'] is not set or has incorrect value");
        }
    }
    
    
    public function RebuildUrl()
    {
        $this->checkLogin();
        if ($this->isLoged()) {
            $this->Rebuild(@$_GET['foreign_id'], @$_GET['hash']);
        }
        exit();
    }
    
    
    public function RebuildSliderUrl()
    {
        $this->checkLogin();
        if ($this->isLoged()) {
            $this->Rebuild(@$_GET['foreign_id'], @$_GET['hash']);
        }
        exit();
    }
    
    
    public function RebuildGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $this->Rebuild(@$_GET['foreign_id'], @$_GET['hash']);
        }
        exit();
    }
    
    public function RebuildProductGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $this->Rebuild(@$_GET['foreign_id'], @$_GET['hash']);
        }
        exit();
    }
    
    public function RebuildSlider()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $this->Rebuild(@$_GET['foreign_id'], @$_GET['hash']);
        }
        exit();
    }
    
    
    public function ResizeGallery()
    {
        $this->checkLogin();
        $arr = GalleryModel::factory()->find($_GET['id'])->getData();
        if (count($arr) === 0) {
            UtilComponent::redirect(sprintf("%sindex.php?controller=Gallery&action=Index&err=AG01", BASE_URL));
        }
        $this->set('arr', $arr);
        $this->set('imageSizes', $this->imageSizes);
        $this->appendJs('jquery.Jcrop.min.js', $this->getConst('PLUGIN_LIBS_PATH') . 'jcrop/js/');
        $this->appendCss('jquery.Jcrop.min.css', $this->getConst('PLUGIN_LIBS_PATH') . 'jcrop/css/');
        $this->appendJs('Gallery.js', $this->getConst('PLUGIN_JS_PATH'));
    }
    
    public function ResizeSlider()
    {
        $this->checkLogin();
        $arr = GalleryModel::factory()->find($_GET['id'])->getData();
        if (count($arr) === 0) {
            UtilComponent::redirect(sprintf("%sindex.php?controller=Gallery&action=Index&err=AG01", BASE_URL));
        }
        $this->set('arr', $arr);
        $this->set('imageSizes', $this->imageSizes);
        $this->appendJs('jquery.Jcrop.min.js', $this->getConst('PLUGIN_LIBS_PATH') . 'jcrop/js/');
        $this->appendCss('jquery.Jcrop.min.css', $this->getConst('PLUGIN_LIBS_PATH') . 'jcrop/css/');
        $this->appendJs('Gallery.js', $this->getConst('PLUGIN_JS_PATH'));
    }
    
    public function ResizeProductGallery()
    {
        $this->checkLogin();
        $arr = GalleryModel::factory()->find($_GET['id'])->getData();
        if (count($arr) === 0) {
            UtilComponent::redirect(sprintf("%sindex.php?controller=Gallery&action=Index&err=AG01", BASE_URL));
        }
        $this->set('arr', $arr);
        $this->set('imageSizes', $this->imageSizes);
        $this->appendJs('jquery.Jcrop.min.js', $this->getConst('PLUGIN_LIBS_PATH') . 'jcrop/js/');
        $this->appendCss('jquery.Jcrop.min.css', $this->getConst('PLUGIN_LIBS_PATH') . 'jcrop/css/');
        $this->appendJs('Gallery.js', $this->getConst('PLUGIN_JS_PATH'));
    }
    
    
    public function RotateGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['id']) && (int) $_POST['id'] > 0) {
                $GalleryModel = GalleryModel::factory();
                $arr = $GalleryModel->find($_POST['id'])->getData();
                if (count($arr) > 0) {
                    $Image = new ImageComponent();
                    if ($Image->getErrorCode() !== 200) {
                        $data = array();
                        if (! empty($arr['small_path'])) {
                            $Image->loadImage($arr['small_path'])
                            ->rotate()
                            ->saveImage($arr['small_path']);
                            $data['small_size'] = filesize($arr['small_path']);
                            $size = getimagesize($arr['small_path']);
                            $data['small_width'] = $size[0];
                            $data['small_height'] = $size[1];
                        }
                        if (! empty($arr['medium_path'])) {
                            $Image->loadImage($arr['medium_path'])
                            ->rotate()
                            ->saveImage($arr['medium_path']);
                            $data['medium_size'] = filesize($arr['medium_path']);
                            $size = getimagesize($arr['medium_path']);
                            $data['medium_width'] = $size[0];
                            $data['medium_height'] = $size[1];
                        }
                        if (! empty($arr['large_path'])) {
                            $Image->loadImage($arr['large_path'])
                            ->rotate()
                            ->saveImage($arr['large_path']);
                            $data['large_size'] = filesize($arr['large_path']);
                            $size = getimagesize($arr['large_path']);
                            $data['large_width'] = $size[0];
                            $data['large_height'] = $size[1];
                        }
                        if (! empty($data)) {
                            $GalleryModel->modify($data);
                        }
                    } else {
                        $this->log('GD extesion is not loaded');
                    }
                } else {
                    $this->log("Image record not found in DB");
                }
            } else {
                $this->log("\$_POST['id'] is not set or has incorrect value");
            }
        }
        exit();
    }
    
    public function RotateProductGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['id']) && (int) $_POST['id'] > 0) {
                $GalleryModel = GalleryModel::factory();
                $arr = $GalleryModel->find($_POST['id'])->getData();
                if (count($arr) > 0) {
                    $Image = new ImageComponent();
                    if ($Image->getErrorCode() !== 200) {
                        $data = array();
                        if (! empty($arr['small_path'])) {
                            $Image->loadImage($arr['small_path'])
                            ->rotate()
                            ->saveImage($arr['small_path']);
                            $data['small_size'] = filesize($arr['small_path']);
                            $size = getimagesize($arr['small_path']);
                            $data['small_width'] = $size[0];
                            $data['small_height'] = $size[1];
                        }
                        if (! empty($arr['medium_path'])) {
                            $Image->loadImage($arr['medium_path'])
                            ->rotate()
                            ->saveImage($arr['medium_path']);
                            $data['medium_size'] = filesize($arr['medium_path']);
                            $size = getimagesize($arr['medium_path']);
                            $data['medium_width'] = $size[0];
                            $data['medium_height'] = $size[1];
                        }
                        if (! empty($arr['large_path'])) {
                            $Image->loadImage($arr['large_path'])
                            ->rotate()
                            ->saveImage($arr['large_path']);
                            $data['large_size'] = filesize($arr['large_path']);
                            $size = getimagesize($arr['large_path']);
                            $data['large_width'] = $size[0];
                            $data['large_height'] = $size[1];
                        }
                        if (! empty($data)) {
                            $GalleryModel->modify($data);
                        }
                    } else {
                        $this->log('GD extesion is not loaded');
                    }
                } else {
                    $this->log("Image record not found in DB");
                }
            } else {
                $this->log("\$_POST['id'] is not set or has incorrect value");
            }
        }
        exit();
    }
    
    public function RotateSlider()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['id']) && (int) $_POST['id'] > 0) {
                $GalleryModel = GalleryModel::factory();
                $arr = $GalleryModel->find($_POST['id'])->getData();
                if (count($arr) > 0) {
                    $Image = new ImageComponent();
                    if ($Image->getErrorCode() !== 200) {
                        $data = array();
                        if (! empty($arr['small_path'])) {
                            $Image->loadImage($arr['small_path'])
                            ->rotate()
                            ->saveImage($arr['small_path']);
                            $data['small_size'] = filesize($arr['small_path']);
                            $size = getimagesize($arr['small_path']);
                            $data['small_width'] = $size[0];
                            $data['small_height'] = $size[1];
                        }
                        if (! empty($arr['medium_path'])) {
                            $Image->loadImage($arr['medium_path'])
                            ->rotate()
                            ->saveImage($arr['medium_path']);
                            $data['medium_size'] = filesize($arr['medium_path']);
                            $size = getimagesize($arr['medium_path']);
                            $data['medium_width'] = $size[0];
                            $data['medium_height'] = $size[1];
                        }
                        if (! empty($arr['large_path'])) {
                            $Image->loadImage($arr['large_path'])
                            ->rotate()
                            ->saveImage($arr['large_path']);
                            $data['large_size'] = filesize($arr['large_path']);
                            $size = getimagesize($arr['large_path']);
                            $data['large_width'] = $size[0];
                            $data['large_height'] = $size[1];
                        }
                        if (! empty($data)) {
                            $GalleryModel->modify($data);
                        }
                    } else {
                        $this->log('GD extesion is not loaded');
                    }
                } else {
                    $this->log("Image record not found in DB");
                }
            } else {
                $this->log("\$_POST['id'] is not set or has incorrect value");
            }
        }
        exit();
    }
    
    public function SortGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['sort']) && is_array($_POST['sort'])) {
                $GalleryModel = new GalleryModel();
                $arr = $GalleryModel->whereIn('id', $_POST['sort'])
                ->orderBy("t1.sort ASC")
                ->findAll()
                ->getDataPair('id', 'sort');
                $fliped = array_flip($_POST['sort']);
                $combined = array_combine(array_keys($fliped), $arr);
                $GalleryModel->begin();
                foreach ($combined as $id => $sort) {
                    $GalleryModel->setAttributes(compact('id'))->modify(compact('sort'));
                }
                $GalleryModel->commit();
            } else {
                $this->log("\$_POST['sort'] is not set or incorrect value");
            }
        }
        exit();
    }
    
    public function SortProductGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['sort']) && is_array($_POST['sort'])) {
                $GalleryModel = new GalleryModel();
                $arr = $GalleryModel->whereIn('id', $_POST['sort'])
                ->orderBy("t1.sort ASC")
                ->findAll()
                ->getDataPair('id', 'sort');
                $fliped = array_flip($_POST['sort']);
                $combined = array_combine(array_keys($fliped), $arr);
                $GalleryModel->begin();
                foreach ($combined as $id => $sort) {
                    $GalleryModel->setAttributes(compact('id'))->modify(compact('sort'));
                }
                $GalleryModel->commit();
            } else {
                $this->log("\$_POST['sort'] is not set or incorrect value");
            }
        }
        exit();
    }
    
    public function SortSlider()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if (isset($_POST['sort']) && is_array($_POST['sort'])) {
                $GalleryModel = new GalleryModel();
                $arr = $GalleryModel->whereIn('id', $_POST['sort'])
                ->orderBy("t1.sort ASC")
                ->findAll()
                ->getDataPair('id', 'sort');
                $fliped = array_flip($_POST['sort']);
                $combined = array_combine(array_keys($fliped), $arr);
                $GalleryModel->begin();
                foreach ($combined as $id => $sort) {
                    $GalleryModel->setAttributes(compact('id'))->modify(compact('sort'));
                }
                $GalleryModel->commit();
            } else {
                $this->log("\$_POST['sort'] is not set or incorrect value");
            }
        }
        exit();
    }
    
    public function UpdateGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $GalleryModel = GalleryModel::factory();
            if (isset($_POST['id']) && (int) $_POST['id'] > 0) {
                $arr = $GalleryModel->find($_POST['id'])->getData();
                if (count($arr) > 0) {
                    $Image = new ImageComponent();
                    if ($Image->getErrorCode() !== 200) {
                        $Image->setFontSize(18)->setFont(WEB_PATH . 'obj/arialbd.ttf');
                        $_POST['large_path_compression'] = $_POST['small_path_compression'];
                        $_POST['medium_path_compression'] = $_POST['small_path_compression'];
                        $data = array();
                        foreach ($this->imageFiles as $file) {
                            @clearstatcache();
                            if (! empty($arr[$file]) && is_file($arr[$file])) {
                                if (isset($_POST['watermark']) && ! empty($_POST['watermark']) && $arr['watermark'] != $_POST['watermark']) {
                                    if ($file != 'source_path') {
                                        if (! empty($arr['watermark'])) {
                                            if (! empty($arr[$file])) {
                                                $dst = $arr[$file];
                                            } else {
                                                $dst = str_replace(PRODUCT_UPLOAD_PATH . 'source/', PRODUCT_UPLOAD_PATH . str_replace('_path', '', $file) . '/', $arr['source_path']);
                                            }
                                            $Image->loadImage($arr['source_path']);
                                            if ($file != 'large_path') {
                                                if ($this->imageCrop) {
                                                    $Image->setFillColor($this->imageFillColor)->resizeSmart($this->imageSizes[str_replace('_path', '', $file)][0], $this->imageSizes[str_replace('_path', '', $file)][1]);
                                                } else {
                                                    $Image->resizeToWidth($this->imageSizes[str_replace('_path', '', $file)][0]);
                                                }
                                            }
                                            if ($file != 'small_path') {
                                                $Image->setWatermark($_POST['watermark'], $_POST['position']);
                                            }
                                            $Image->saveImage($dst);
                                        } else {
                                            if ($file != 'small_path') {
                                                $Image->loadImage($arr[$file])
                                                ->setWatermark($_POST['watermark'], $_POST['position'])
                                                ->saveImage($arr[$file]);
                                            }
                                        }
                                    }
                                }
                                if (! empty($arr[$file])) {
                                    $compression = isset($_POST[$file . '_compression']) ? (int) $_POST[$file . '_compression'] : 60;
                                    $Image->loadImage($arr[$file])->saveImage($arr[$file], NULL, $compression);
                                    @clearstatcache();
                                    $data[str_replace('_path', '_size', $file)] = filesize($arr[$file]);
                                }
                            }
                        }
                        if (empty($_POST['watermark']) && ! empty($arr['watermark'])) {
                            foreach ($this->imageSizes as $key => $d) {
                                if (! empty($arr[$key . '_path'])) {
                                    $dst = $arr[$key . '_path'];
                                } else {
                                    $dst = str_replace(PRODUCT_UPLOAD_PATH . 'source/', PRODUCT_UPLOAD_PATH . $key . '/', $arr['source_path']);
                                }
                                $Image->loadImage($arr['source_path']);
                                if ($this->imageCrop) {
                                    $Image->setFillColor($this->imageFillColor)->resizeSmart($d[0], $d[1]);
                                } else {
                                    $Image->resizeToWidth($d[0]);
                                }
                                $Image->saveImage($dst);
                                $data[$key . '_path'] = $dst;
                            }
                            $dst = str_replace(PRODUCT_UPLOAD_PATH . 'source/', PRODUCT_UPLOAD_PATH . 'large/', $arr['source_path']);
                            $Image->loadImage($arr['source_path'])->saveImage($dst);
                            $data['large_path'] = $dst;
                        }
                    } else {
                        $this->log('GD extension is not loaded');
                    }
                    $GalleryModel->modify(array_merge($_POST, $data));
                }
            } else {
                $arr = $GalleryModel->find($_GET['id'])->getData();
                AppController::jsonResponse($arr);
            }
        }
        exit();
    }
    
    public function UpdateProductGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $GalleryModel = GalleryModel::factory();
            if (isset($_POST['id']) && (int) $_POST['id'] > 0) {
                $arr = $GalleryModel->find($_POST['id'])->getData();
                if (count($arr) > 0) {
                    $Image = new ImageComponent();
                    if ($Image->getErrorCode() !== 200) {
                        $Image->setFontSize(18)->setFont(WEB_PATH . 'obj/arialbd.ttf');
                        $_POST['large_path_compression'] = $_POST['small_path_compression'];
                        $_POST['medium_path_compression'] = $_POST['small_path_compression'];
                        $data = array();
                        foreach ($this->imageFiles as $file) {
                            @clearstatcache();
                            if (! empty($arr[$file]) && is_file($arr[$file])) {
                                if (isset($_POST['watermark']) && ! empty($_POST['watermark']) && $arr['watermark'] != $_POST['watermark']) {
                                    if ($file != 'source_path') {
                                        if (! empty($arr['watermark'])) {
                                            if (! empty($arr[$file])) {
                                                $dst = $arr[$file];
                                            } else {
                                                $dst = str_replace(PRODUCT_UPLOAD_PATH . 'source/', PRODUCT_UPLOAD_PATH . str_replace('_path', '', $file) . '/', $arr['source_path']);
                                            }
                                            $Image->loadImage($arr['source_path']);
                                            if ($file != 'large_path') {
                                                if ($this->imageCrop) {
                                                    $Image->setFillColor($this->imageFillColor)->resizeSmart($this->imageSizes[str_replace('_path', '', $file)][0], $this->imageSizes[str_replace('_path', '', $file)][1]);
                                                } else {
                                                    $Image->resizeToWidth($this->imageSizes[str_replace('_path', '', $file)][0]);
                                                }
                                            }
                                            if ($file != 'small_path') {
                                                $Image->setWatermark($_POST['watermark'], $_POST['position']);
                                            }
                                            $Image->saveImage($dst);
                                        } else {
                                            if ($file != 'small_path') {
                                                $Image->loadImage($arr[$file])
                                                ->setWatermark($_POST['watermark'], $_POST['position'])
                                                ->saveImage($arr[$file]);
                                            }
                                        }
                                    }
                                }
                                if (! empty($arr[$file])) {
                                    $compression = isset($_POST[$file . '_compression']) ? (int) $_POST[$file . '_compression'] : 60;
                                    $Image->loadImage($arr[$file])->saveImage($arr[$file], NULL, $compression);
                                    @clearstatcache();
                                    $data[str_replace('_path', '_size', $file)] = filesize($arr[$file]);
                                }
                            }
                        }
                        if (empty($_POST['watermark']) && ! empty($arr['watermark'])) {
                            foreach ($this->imageSizes as $key => $d) {
                                if (! empty($arr[$key . '_path'])) {
                                    $dst = $arr[$key . '_path'];
                                } else {
                                    $dst = str_replace(PRODUCT_UPLOAD_PATH . 'source/', PRODUCT_UPLOAD_PATH . $key . '/', $arr['source_path']);
                                }
                                $Image->loadImage($arr['source_path']);
                                if ($this->imageCrop) {
                                    $Image->setFillColor($this->imageFillColor)->resizeSmart($d[0], $d[1]);
                                } else {
                                    $Image->resizeToWidth($d[0]);
                                }
                                $Image->saveImage($dst);
                                $data[$key . '_path'] = $dst;
                            }
                            $dst = str_replace(PRODUCT_UPLOAD_PATH . 'source/', PRODUCT_UPLOAD_PATH . 'large/', $arr['source_path']);
                            $Image->loadImage($arr['source_path'])->saveImage($dst);
                            $data['large_path'] = $dst;
                        }
                    } else {
                        $this->log('GD extension is not loaded');
                    }
                    $GalleryModel->modify(array_merge($_POST, $data));
                }
            } else {
                $arr = $GalleryModel->find($_GET['id'])->getData();
                AppController::jsonResponse($arr);
            }
        }
    }
    
    public function UpdateSlider()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            $GalleryModel = GalleryModel::factory();
            if (isset($_POST['id']) && (int) $_POST['id'] > 0) {
                $arr = $GalleryModel->find($_POST['id'])->getData();
                if (count($arr) > 0) {
                    $Image = new ImageComponent();
                    if ($Image->getErrorCode() !== 200) {
                        $Image->setFontSize(18)->setFont(WEB_PATH . 'obj/arialbd.ttf');
                        $_POST['large_path_compression'] = $_POST['small_path_compression'];
                        $_POST['medium_path_compression'] = $_POST['small_path_compression'];
                        $data = array();
                        foreach ($this->imageFiles as $file) {
                            @clearstatcache();
                            if (! empty($arr[$file]) && is_file($arr[$file])) {
                                if (isset($_POST['watermark']) && ! empty($_POST['watermark']) && $arr['watermark'] != $_POST['watermark']) {
                                    if ($file != 'source_path') {
                                        if (! empty($arr['watermark'])) {
                                            if (! empty($arr[$file])) {
                                                $dst = $arr[$file];
                                            } else {
                                                $dst = str_replace(SLIDER_UPLOAD_PATH . 'source/', SLIDER_UPLOAD_PATH . str_replace('_path', '', $file) . '/', $arr['source_path']);
                                            }
                                            $Image->loadImage($arr['source_path']);
                                            if ($file != 'large_path') {
                                                if ($this->imageCrop) {
                                                    $Image->setFillColor($this->imageFillColor)->resizeSmart($this->imageSizes[str_replace('_path', '', $file)][0], $this->imageSizes[str_replace('_path', '', $file)][1]);
                                                } else {
                                                    $Image->resizeToWidth($this->imageSizes[str_replace('_path', '', $file)][0]);
                                                }
                                            }
                                            if ($file != 'small_path') {
                                                $Image->setWatermark($_POST['watermark'], $_POST['position']);
                                            }
                                            $Image->saveImage($dst);
                                        } else {
                                            if ($file != 'small_path') {
                                                $Image->loadImage($arr[$file])
                                                ->setWatermark($_POST['watermark'], $_POST['position'])
                                                ->saveImage($arr[$file]);
                                            }
                                        }
                                    }
                                }
                                if (! empty($arr[$file])) {
                                    $compression = isset($_POST[$file . '_compression']) ? (int) $_POST[$file . '_compression'] : 60;
                                    $Image->loadImage($arr[$file])->saveImage($arr[$file], NULL, $compression);
                                    @clearstatcache();
                                    $data[str_replace('_path', '_size', $file)] = filesize($arr[$file]);
                                }
                            }
                        }
                        if (empty($_POST['watermark']) && ! empty($arr['watermark'])) {
                            foreach ($this->imageSizes as $key => $d) {
                                if (! empty($arr[$key . '_path'])) {
                                    $dst = $arr[$key . '_path'];
                                } else {
                                    $dst = str_replace(SLIDER_UPLOAD_PATH . 'source/', SLIDER_UPLOAD_PATH . $key . '/', $arr['source_path']);
                                }
                                $Image->loadImage($arr['source_path']);
                                if ($this->imageCrop) {
                                    $Image->setFillColor($this->imageFillColor)->resizeSmart($d[0], $d[1]);
                                } else {
                                    $Image->resizeToWidth($d[0]);
                                }
                                $Image->saveImage($dst);
                                $data[$key . '_path'] = $dst;
                            }
                            $dst = str_replace(SLIDER_UPLOAD_PATH . 'source/', SLIDER_UPLOAD_PATH . 'large/', $arr['source_path']);
                            $Image->loadImage($arr['source_path'])->saveImage($dst);
                            $data['large_path'] = $dst;
                        }
                    } else {
                        $this->log('GD extension is not loaded');
                    }
                    $GalleryModel->modify(array_merge($_POST, $data));
                    if (isset ( $_POST ['i18n'] )) {
                        MultiLangModel::factory ()->updateMultiLang ( $_POST ['i18n'], $_POST ['id'], 'Gallery', 'data' );
                    }
                    AppController::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => ''));
                }
            } else {
                $arr = $GalleryModel->find($_GET['id'])->getData();
                $arr ['i18n'] = MultiLangModel::factory ()->getMultiLang ( $arr ['id'], 'Gallery' );
                $this->set('arr', $arr);
                $locale_arr = LocaleModel::factory ()->select ( 't1.*, t2.file' )->join ( 'LocaleLanguage', 't2.iso=t1.language_iso', 'left' )->where ( 't2.file IS NOT NULL' )->orderBy ( 't1.sort ASC' )->findAll ()->getData ();
                $lp_arr = array ();
                foreach ( $locale_arr as $item ) {
                    $lp_arr [$item ['id'] . "_"] = $item ['file'];
                }
                $this->set ( 'lp_arr', $locale_arr );
                $this->set ( 'locale_str', AppController::jsonEncode ( $lp_arr ) );
            }
        }
    }
    
    public function UploadSlider()
    {
        $this->checkLogin();
        $this->setAjax(true);
        ini_set('post_max_size', '50M');
        ini_set('upload_max_filesize', '50M');
        $resp = array();
        $post_max_size = ini_get('post_max_size');
        switch (substr($post_max_size, - 1)) {
            case 'G':
                $post_max_size = (int) $post_max_size * 1024 * 1024 * 1024;
                break;
            case 'M':
                $post_max_size = (int) $post_max_size * 1024 * 1024;
                break;
            case 'K':
                $post_max_size = (int) $post_max_size * 1024;
                break;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > $post_max_size) {
            $error = 'Posted data is too large. ' . $_SERVER['CONTENT_LENGTH'] . ' bytes exceeds the maximum size of ' . $post_max_size . ' bytes.';
            $this->log("The \$_SERVER['CONTENT_LENGTH'] exceeds the post_max_size directive in php.ini.");
            $this->set('error', $error);
        } else {
            if (isset($_FILES['image'])) {
                $Image = new ImageComponent();
                if ($Image->getErrorCode() !== 200) {
                    $Image->setAllowedTypes(array(
                        'image/png',
                        'image/gif',
                        'image/jpg',
                        'image/jpeg',
                        'image/peg'
                    ));
                    if ($Image->load($_FILES['image'])) {
                        $resp = $Image->isConvertPossible();
                        if ($resp['status'] === true) {
                            $hash = md5(uniqid(rand(), true));
                            if (!file_exists(SLIDER_UPLOAD_PATH)) {
                                mkdir(SLIDER_UPLOAD_PATH, 0777);
                            }
                            $source_path = SLIDER_UPLOAD_PATH . 'source/' . @$_GET['foreign_id'] . '_' . $hash . '.' . $Image->getExtension();
                            if ($Image->save($source_path)) {
                                $GalleryModel = GalleryModel::factory();
                                $data = array();
                                if (isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) {
                                    $GalleryModel->where('t1.foreign_id', $_GET['foreign_id']);
                                    $data['foreign_id'] = $_GET['foreign_id'];
                                } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                                    $GalleryModel->where('t1.hash', $_GET['hash']);
                                    $data['hash'] = $_GET['hash'];
                                }
                                $arr = $GalleryModel->orderBy('t1.sort DESC')
                                ->limit(1)
                                ->findAll()
                                ->getData();
                                $sort = 1;
                                if (count($arr) === 1) {
                                    $sort = (int) $arr[0]['sort'] + 1;
                                }
                                $data['mime_type'] = $_FILES['image']['type'];
                                $data['source_path'] = $source_path;
                                $data['source_size'] = $_FILES['image']['size'];
                                $data['name'] = $_FILES['image']['name'];
                                $data['sort'] = $sort;
                                $data = array_merge($data, $this->BuildFromSource($Image, $data, null, null, SLIDER_UPLOAD_PATH));
                                $size = $Image->getImageSize();
                                $data['source_width'] = $size[0];
                                $data['source_height'] = $size[1];
                                $data['source_type'] = GalleryModel::SOURCE_TYPE_SLIDER;
                                $GalleryModel->reset()
                                ->setAttributes($data)
                                ->insert();
                                
                            } else {
                                $this->log('Image has not been saved');
                            }
                        } else {
                            $this->set('error', sprintf('Allowed memory size of %u bytes exhausted (tried to allocate %u bytes)', $resp['memory_limit'], $resp['memory_needed']));
                            $this->log($this->get('error'));
                        }
                    } else {
                        $this->set('error', $Image->getError());
                        $this->log($this->get('error'));
                    }
                } else {
                    $this->log('GD extension is not loaded');
                }
            } else {
                $this->log("\$_FILES['image'] is not set");
                $this->set('error', 'Image is not set');
            }
        }
        if ($this->get('error') !== FALSE) {
            $resp['error'] = $this->get('error');
        }
        header("Content-Type: text/html; charset=utf-8");
        echo AppController::jsonEncode($resp);
        exit();
    }
    
    public function UploadGallery()
    {
        $this->checkLogin();
        $this->setAjax(true);
        ini_set('post_max_size', '50M');
        ini_set('upload_max_filesize', '50M');
        $resp = array();
        $post_max_size = ini_get('post_max_size');
        switch (substr($post_max_size, - 1)) {
            case 'G':
                $post_max_size = (int) $post_max_size * 1024 * 1024 * 1024;
                break;
            case 'M':
                $post_max_size = (int) $post_max_size * 1024 * 1024;
                break;
            case 'K':
                $post_max_size = (int) $post_max_size * 1024;
                break;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > $post_max_size) {
            $error = 'Posted data is too large. ' . $_SERVER['CONTENT_LENGTH'] . ' bytes exceeds the maximum size of ' . $post_max_size . ' bytes.';
            $this->log("The \$_SERVER['CONTENT_LENGTH'] exceeds the post_max_size directive in php.ini.");
            $this->set('error', $error);
        } else {
            if (isset($_FILES['image'])) {
                $Image = new ImageComponent();
                if ($Image->getErrorCode() !== 200) {
                    $Image->setAllowedTypes(array(
                        'image/png',
                        'image/gif',
                        'image/jpg',
                        'image/jpeg',
                        'image/peg'
                    ));
                    if ($Image->load($_FILES['image'])) {
                        $resp = $Image->isConvertPossible();
                        if ($resp['status'] === true) {
                            $hash = md5(uniqid(rand(), true));
                            if (!file_exists(GALLERY_UPLOAD_PATH . 'sliders/')) {
                                mkdir(GALLERY_UPLOAD_PATH. "sliders/", 0777);
                            }
                            $source_path = GALLERY_UPLOAD_PATH . 'source/' . @$_GET['foreign_id'] . '_' . $hash . '.' . $Image->getExtension();
                            if ($Image->save($source_path)) {
                                $GalleryModel = GalleryModel::factory();
                                $data = array();
                                if (isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) {
                                    $GalleryModel->where('t1.foreign_id', $_GET['foreign_id']);
                                    $data['foreign_id'] = $_GET['foreign_id'];
                                } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                                    $GalleryModel->where('t1.hash', $_GET['hash']);
                                    $data['hash'] = $_GET['hash'];
                                }
                                $arr = $GalleryModel->orderBy('t1.sort DESC')
                                ->limit(1)
                                ->findAll()
                                ->getData();
                                $sort = 1;
                                if (count($arr) === 1) {
                                    $sort = (int) $arr[0]['sort'] + 1;
                                }
                                $data['mime_type'] = $_FILES['image']['type'];
                                $data['source_path'] = $source_path;
                                $data['source_size'] = $_FILES['image']['size'];
                                $data['name'] = $_FILES['image']['name'];
                                $data['sort'] = $sort;
                                $data = array_merge($data, $this->BuildFromSource($Image, $data, null, null, GALLERY_UPLOAD_PATH));
                                $size = $Image->getImageSize();
                                $data['source_width'] = $size[0];
                                $data['source_height'] = $size[1];
                                
                                $GalleryModel->reset()
                                ->setAttributes($data)
                                ->insert();
                                
                            } else {
                                $this->log('Image has not been saved');
                            }
                        } else {
                            $this->set('error', sprintf('Allowed memory size of %u bytes exhausted (tried to allocate %u bytes)', $resp['memory_limit'], $resp['memory_needed']));
                            $this->log($this->get('error'));
                        }
                    } else {
                        $this->set('error', $Image->getError());
                        $this->log($this->get('error'));
                    }
                } else {
                    $this->log('GD extension is not loaded');
                }
            } else {
                $this->log("\$_FILES['image'] is not set");
                $this->set('error', 'Image is not set');
            }
        }
        if ($this->get('error') !== FALSE) {
            $resp['error'] = $this->get('error');
        }
        header("Content-Type: text/html; charset=utf-8");
        echo AppController::jsonEncode($resp);
        exit();
    }
    
    public function UploadProductGallery()
    {
        $this->checkLogin();
        $this->setAjax(true);
        ini_set('post_max_size', '50M');
        ini_set('upload_max_filesize', '50M');
        $resp = array();
        $post_max_size = ini_get('post_max_size');
        switch (substr($post_max_size, - 1)) {
            case 'G':
                $post_max_size = (int) $post_max_size * 1024 * 1024 * 1024;
                break;
            case 'M':
                $post_max_size = (int) $post_max_size * 1024 * 1024;
                break;
            case 'K':
                $post_max_size = (int) $post_max_size * 1024;
                break;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > $post_max_size) {
            $error = 'Posted data is too large. ' . $_SERVER['CONTENT_LENGTH'] . ' bytes exceeds the maximum size of ' . $post_max_size . ' bytes.';
            $this->log("The \$_SERVER['CONTENT_LENGTH'] exceeds the post_max_size directive in php.ini.");
            $this->set('error', $error);
        } else {
            if (isset($_FILES['image'])) {
                $Image = new ImageComponent();
                if ($Image->getErrorCode() !== 200) {
                    $Image->setAllowedTypes(array(
                        'image/png',
                        'image/gif',
                        'image/jpg',
                        'image/jpeg',
                        'image/pjpeg'
                    ));
                    if ($Image->load($_FILES['image'])) {
                        $resp = $Image->isConvertPossible();
                        if ($resp['status'] === true) {
                            $hash = md5(uniqid(rand(), true));
                            $source_path = PRODUCT_UPLOAD_PATH . 'source/' . @$_GET['foreign_id'] . '_' . $hash . '.' . $Image->getExtension();
                            if ($Image->save($source_path)) {
                                $GalleryModel = GalleryModel::factory();
                                $data = array();
                                if (isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) {
                                    $GalleryModel->where('t1.foreign_id', $_GET['foreign_id']);
                                    $data['foreign_id'] = $_GET['foreign_id'];
                                } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                                    $GalleryModel->where('t1.hash', $_GET['hash']);
                                    $data['hash'] = $_GET['hash'];
                                }
                                $arr = $GalleryModel->orderBy('t1.sort DESC')
                                ->limit(1)
                                ->findAll()
                                ->getData();
                                $sort = 1;
                                if (count($arr) === 1) {
                                    $sort = (int) $arr[0]['sort'] + 1;
                                }
                                $data['mime_type'] = $_FILES['image']['type'];
                                $data['source_path'] = $source_path;
                                $data['source_size'] = $_FILES['image']['size'];
                                $data['name'] = $_FILES['image']['name'];
                                $data['sort'] = $sort;
                                $data = array_merge($data, $this->BuildFromSource($Image, $data, NULL, NULL, PRODUCT_UPLOAD_PATH));
                                $size = $Image->getImageSize();
                                $data['source_width'] = $size[0];
                                $data['source_height'] = $size[1];
                                $data['source_type'] = GalleryModel::SOURCE_TYPE_PRODUCT_IMAGE;
                                $GalleryModel->reset()
                                ->setAttributes($data)
                                ->insert();
                            } else {
                                $this->log('Image has not been saved');
                            }
                        } else {
                            $this->set('error', sprintf('Allowed memory size of %u bytes exhausted (tried to allocate %u bytes)', $resp['memory_limit'], $resp['memory_needed']));
                            $this->log($this->get('error'));
                        }
                    } else {
                        $this->set('error', $Image->getError());
                        $this->log($this->get('error'));
                    }
                } else {
                    $this->log('GD extension is not loaded');
                }
            } else {
                $this->log("\$_FILES['image'] is not set");
                $this->set('error', 'Image is not set');
            }
        }
        if ($this->get('error') !== FALSE) {
            $resp['error'] = $this->get('error');
        }
        header("Content-Type: text/html; charset=utf-8");
        echo AppController::jsonEncode($resp);
        exit();
    }
    
    public function UploadWidgetImage()
    {
        $this->checkLogin();
        $this->setAjax(true);
        $resp = array();
        $post_max_size = ini_get('post_max_size');
        switch (substr($post_max_size, - 1)) {
            case 'G':
                $post_max_size = (int) $post_max_size * 1024 * 1024 * 1024;
                break;
            case 'M':
                $post_max_size = (int) $post_max_size * 1024 * 1024;
                break;
            case 'K':
                $post_max_size = (int) $post_max_size * 1024;
                break;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > $post_max_size) {
            $error = 'Posted data is too large. ' . $_SERVER['CONTENT_LENGTH'] . ' bytes exceeds the maximum size of ' . $post_max_size . ' bytes.';
            $this->log("The \$_SERVER['CONTENT_LENGTH'] exceeds the post_max_size directive in php.ini.");
            $this->set('error', $error);
        } else {
            if (isset($_FILES['image'])) {
                $Image = new ImageComponent();
                if ($Image->getErrorCode() !== 200) {
                    $Image->setAllowedTypes(array(
                        'image/png',
                        'image/gif',
                        'image/jpg',
                        'image/jpeg',
                        'image/peg'
                    ));
                    if ($Image->load($_FILES['image'])) {
                        $resp = $Image->isConvertPossible();
                        if ($resp['status'] === true) {
                            $hash = md5(uniqid(rand(), true));
                            
                            $date = date('Y/m/d') . '/';
                            $source_path = WIDGET_UPLOAD_PATH .$date. $hash . '.' . $Image->getExtension();
                            if (!is_dir(WIDGET_UPLOAD_PATH . $date)) {
                                mkdir(WIDGET_UPLOAD_PATH . $date, 0777, true);
                            }
                            if ($Image->save($source_path)) {
                                //Resize image
                                
                                if (isset($this->option_arr['o_widget_img_size'])) {
                                    $Image->loadImage($source_path);
                                    $d = explode('x', $this->option_arr['o_widget_img_size']);
                                    if ($d[0] < $size[0]) {
                                        if (isset($d[1]) && is_numeric($d[1])) {
                                            $Image->resizeSmart ( $d [0], $d [1] );
                                        } else {
                                            $Image->resizeToWidth ( $d [0] );
                                        }
                                        $Image->saveImage($source_path);
                                    }
                                }
                                $resp['url'] = $source_path;
                                
                            } else {
                                $this->log('Image has not been saved');
                            }
                        } else {
                            $this->set('error', sprintf('Allowed memory size of %u bytes exhausted (tried to allocate %u bytes)', $resp['memory_limit'], $resp['memory_needed']));
                            $this->log($this->get('error'));
                        }
                    } else {
                        $this->set('error', $Image->getError());
                        $this->log($this->get('error'));
                    }
                } else {
                    $this->log('GD extension is not loaded');
                }
            } else {
                $this->log("\$_FILES['image'] is not set");
                $this->set('error', 'Image is not set');
            }
        }
        if ($this->get('error') !== FALSE) {
            $resp['error'] = $this->get('error');
        }
        header("Content-Type: text/html; charset=utf-8");
        echo AppController::jsonEncode($resp);
        exit();
    }
    public function UploadPageImage()
    {
        $this->checkLogin();
        $this->setAjax(true);
        $resp = array();
        $post_max_size = ini_get('post_max_size');
        switch (substr($post_max_size, - 1)) {
            case 'G':
                $post_max_size = (int) $post_max_size * 1024 * 1024 * 1024;
                break;
            case 'M':
                $post_max_size = (int) $post_max_size * 1024 * 1024;
                break;
            case 'K':
                $post_max_size = (int) $post_max_size * 1024;
                break;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > $post_max_size) {
            $error = 'Posted data is too large. ' . $_SERVER['CONTENT_LENGTH'] . ' bytes exceeds the maximum size of ' . $post_max_size . ' bytes.';
            $this->log("The \$_SERVER['CONTENT_LENGTH'] exceeds the post_max_size directive in php.ini.");
            $this->set('error', $error);
        } else {
            if (isset($_FILES['image'])) {
                $Image = new ImageComponent();
                if ($Image->getErrorCode() !== 200) {
                    $Image->setAllowedTypes(array(
                        'image/png',
                        'image/gif',
                        'image/jpg',
                        'image/jpeg',
                        'image/peg'
                    ));
                    if ($Image->load($_FILES['image'])) {
                        $resp = $Image->isConvertPossible();
                        if ($resp['status'] === true) {
                            $hash = md5(uniqid(rand(), true));
                            
                            $date = date('Y/m/d') . '/';
                            $source_path = PAGE_CONTENT_UPLOAD_PATH .$date. $hash . '.' . $Image->getExtension();
                            if (!is_dir(PAGE_CONTENT_UPLOAD_PATH . $date)) {
                                mkdir(PAGE_CONTENT_UPLOAD_PATH . $date, 0777, true);
                            }
                            if ($Image->save($source_path)) {
                                //Resize image
                                
                                
                                if (isset($this->option_arr['o_page_img_size'])) {
                                    $Image->loadImage($source_path);
                                    $d = explode('x', $this->option_arr['o_page_img_size']);
                                    if ($d[0] < $size[0]) {
                                        if (isset($d[1]) && is_numeric($d[1])) {
                                            $Image->resizeSmart ( $d [0], $d [1] );
                                        } else {
                                            $Image->resizeToWidth ( $d [0] );
                                        }
                                        $Image->saveImage($source_path);
                                    }
                                }
                                
                                $resp['url'] = $source_path;
                                
                            } else {
                                $this->log('Image has not been saved');
                            }
                        } else {
                            $this->set('error', sprintf('Allowed memory size of %u bytes exhausted (tried to allocate %u bytes)', $resp['memory_limit'], $resp['memory_needed']));
                            $this->log($this->get('error'));
                        }
                    } else {
                        $this->set('error', $Image->getError());
                        $this->log($this->get('error'));
                    }
                } else {
                    $this->log('GD extension is not loaded');
                }
            } else {
                $this->log("\$_FILES['image'] is not set");
                $this->set('error', 'Image is not set');
            }
        }
        if ($this->get('error') !== FALSE) {
            $resp['error'] = $this->get('error');
        }
        header("Content-Type: text/html; charset=utf-8");
        echo AppController::jsonEncode($resp);
        exit();
    }
    
    public function UploadArticleImage()
    {
        $this->checkLogin();
        $this->setAjax(true);
        ini_set('post_max_size', '50M');
        ini_set('upload_max_filesize', '50M');
        $resp = array();
        $post_max_size = ini_get('post_max_size');
        switch (substr($post_max_size, - 1)) {
            case 'G':
                $post_max_size = (int) $post_max_size * 1024 * 1024 * 1024;
                break;
            case 'M':
                $post_max_size = (int) $post_max_size * 1024 * 1024;
                break;
            case 'K':
                $post_max_size = (int) $post_max_size * 1024;
                break;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > $post_max_size) {
            $error = 'Posted data is too large. ' . $_SERVER['CONTENT_LENGTH'] . ' bytes exceeds the maximum size of ' . $post_max_size . ' bytes.';
            $this->log("The \$_SERVER['CONTENT_LENGTH'] exceeds the post_max_size directive in php.ini.");
            $this->set('error', $error);
        } else {
            if (isset($_FILES['image'])) {
                $Image = new ImageComponent();
                if ($Image->getErrorCode() !== 200) {
                    $Image->setAllowedTypes(array(
                        'image/png',
                        'image/gif',
                        'image/jpg',
                        'image/jpeg',
                        'image/peg'
                    ));
                    if ($Image->load($_FILES['image'])) {
                        $resp = $Image->isConvertPossible();
                        if ($resp['status'] === true) {
                            $hash = md5(uniqid(rand(), true));
                            $date = date('Y/m/d') . '/';
                            $source_path = ARTICLE_CONTENT_UPLOAD_PATH .$date. $hash . '.' . $Image->getExtension();
                            if (!is_dir(ARTICLE_CONTENT_UPLOAD_PATH . $date)) {
                                mkdir(ARTICLE_CONTENT_UPLOAD_PATH . $date, 0777, true);
                            }
                            if ($Image->save($source_path)) {
                                //Resize image
                                $size = getimagesize($source_path);
                                if (isset($this->option_arr['o_artile_img_size'])) {
                                    $Image->loadImage($source_path);
                                    $d = explode('x', $this->option_arr['o_artile_img_size']);
                                    if ($d[0] < $size[0]) {
                                        if (isset($d[1]) && is_numeric($d[1])) {
                                            $Image->resizeSmart ( $d [0], $d [1] );
                                        } else {
                                            $Image->resizeToWidth ( $d [0] );
                                        }
                                        $Image->saveImage($source_path);
                                    }
                                }
                                $resp['url'] = $source_path;
                                
                            } else {
                                $this->log('Image has not been saved');
                            }
                        } else {
                            $this->set('error', sprintf('Allowed memory size of %u bytes exhausted (tried to allocate %u bytes)', $resp['memory_limit'], $resp['memory_needed']));
                            $this->log($this->get('error'));
                        }
                    } else {
                        $this->set('error', $Image->getError());
                        $this->log($this->get('error'));
                    }
                } else {
                    $this->log('GD extension is not loaded');
                }
            } else {
                $this->log("\$_FILES['image'] is not set");
                $this->set('error', 'Image is not set');
            }
        }
        if ($this->get('error') !== FALSE) {
            $resp['error'] = $this->get('error');
        }
        header("Content-Type: text/html; charset=utf-8");
        echo AppController::jsonEncode($resp);
        exit();
    }
    
    public function UploadProductCategoryImage()
    {
        $this->checkLogin();
        $this->setAjax(true);
        ini_set('post_max_size', '50M');
        ini_set('upload_max_filesize', '50M');
        $resp = array();
        $post_max_size = ini_get('post_max_size');
        switch (substr($post_max_size, - 1)) {
            case 'G':
                $post_max_size = (int) $post_max_size * 1024 * 1024 * 1024;
                break;
            case 'M':
                $post_max_size = (int) $post_max_size * 1024 * 1024;
                break;
            case 'K':
                $post_max_size = (int) $post_max_size * 1024;
                break;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > $post_max_size) {
            $error = 'Posted data is too large. ' . $_SERVER['CONTENT_LENGTH'] . ' bytes exceeds the maximum size of ' . $post_max_size . ' bytes.';
            $this->log("The \$_SERVER['CONTENT_LENGTH'] exceeds the post_max_size directive in php.ini.");
            $this->set('error', $error);
        } else {
            if (isset($_FILES['image'])) {
                $Image = new ImageComponent();
                if ($Image->getErrorCode() !== 200) {
                    $Image->setAllowedTypes(array(
                        'image/png',
                        'image/gif',
                        'image/jpg',
                        'image/jpeg',
                        'image/peg'
                    ));
                    if ($Image->load($_FILES['image'])) {
                        $resp = $Image->isConvertPossible();
                        if ($resp['status'] === true) {
                            $hash = md5(uniqid(rand(), true));
                            $date = date('Y/m/d') . '/';
                            $source_path = PRODUCT_CATEGORY_UPLOAD_PATH .$date. $hash . '.' . $Image->getExtension();
                            if (!is_dir(PRODUCT_CATEGORY_UPLOAD_PATH . $date)) {
                                mkdir(PRODUCT_CATEGORY_UPLOAD_PATH . $date, 0777, true);
                            }
                            if ($Image->save($source_path)) {
                                //Resize image
                                $size = getimagesize($source_path);
                                if (isset($this->option_arr['o_product_category_img_size'])) {
                                    $Image->loadImage($source_path);
                                    $d = explode('x', $this->option_arr['o_artile_img_size']);
                                    if ($d[0] < $size[0]) {
                                        if (isset($d[1]) && is_numeric($d[1])) {
                                            $Image->resizeSmart ( $d [0], $d [1] );
                                        } else {
                                            $Image->resizeToWidth ( $d [0] );
                                        }
                                        $Image->saveImage($source_path);
                                    }
                                }
                                $resp['url'] = $source_path;
                                
                            } else {
                                $this->log('Image has not been saved');
                            }
                        } else {
                            $this->set('error', sprintf('Allowed memory size of %u bytes exhausted (tried to allocate %u bytes)', $resp['memory_limit'], $resp['memory_needed']));
                            $this->log($this->get('error'));
                        }
                    } else {
                        $this->set('error', $Image->getError());
                        $this->log($this->get('error'));
                    }
                } else {
                    $this->log('GD extension is not loaded');
                }
            } else {
                $this->log("\$_FILES['image'] is not set");
                $this->set('error', 'Image is not set');
            }
        }
        if ($this->get('error') !== FALSE) {
            $resp['error'] = $this->get('error');
        }
        header("Content-Type: text/html; charset=utf-8");
        echo AppController::jsonEncode($resp);
        exit();
    }
    
    public function WatermarkGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if ((isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) ^ (isset($_GET['hash']) && ! empty($_GET['hash']))) {
                $GalleryModel = GalleryModel::factory();
                if (isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) {
                    $GalleryModel->where('foreign_id', $_GET['foreign_id']);
                } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                    $GalleryModel->where('hash', $_GET['hash']);
                }
                $arr = $GalleryModel->findAll()->getData();
                if (count($arr) > 0) {
                    $Image = new ImageComponent();
                    if ($Image->getErrorCode() !== 200) {
                        $Image->setFontSize(18)->setFont(WEB_PATH . 'obj/arialbd.ttf');
                        foreach ($arr as $item) {
                            if (isset($_POST['watermark'])) {
                                $this->BuildFromSource($Image, $item, $_POST['watermark'], $_POST['position']);
                            } else {
                                $this->BuildFromSource($Image, $item);
                            }
                        }
                    } else {
                        $this->log('GD extension is not loaded');
                    }
                    if (isset($_POST['watermark'])) {
                        $data = array(
                            'watermark' => $_POST['watermark']
                        );
                    } else {
                        $data = array(
                            'watermark' => array(
                                'NULL'
                            )
                        );
                    }
                    $GalleryModel->modifyAll($data);
                } else {
                    $this->log('No image records found in DB');
                }
            } else {
                $this->log("\$_GET['foreign_id'] is not set or has incorrect value");
            }
        }
        exit();
    }
    
    public function WatermarkProductGallery()
    {
        $this->setAjax(true);
        if ($this->isXHR() && $this->isLoged()) {
            if ((isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) ^ (isset($_GET['hash']) && ! empty($_GET['hash']))) {
                $GalleryModel = GalleryModel::factory();
                if (isset($_GET['foreign_id']) && (int) $_GET['foreign_id'] > 0) {
                    $GalleryModel->where('foreign_id', $_GET['foreign_id']);
                } elseif (isset($_GET['hash']) && ! empty($_GET['hash'])) {
                    $GalleryModel->where('hash', $_GET['hash']);
                }
                $arr = $GalleryModel->findAll()->getData();
                if (count($arr) > 0) {
                    $Image = new ImageComponent();
                    if ($Image->getErrorCode() !== 200) {
                        $Image->setFontSize(18)->setFont(WEB_PATH . 'obj/arialbd.ttf');
                        foreach ($arr as $item) {
                            if (isset($_POST['watermark'])) {
                                $this->BuildFromSource($Image, $item, $_POST['watermark'], $_POST['position']);
                            } else {
                                $this->BuildFromSource($Image, $item);
                            }
                        }
                    } else {
                        $this->log('GD extension is not loaded');
                    }
                    if (isset($_POST['watermark'])) {
                        $data = array(
                            'watermark' => $_POST['watermark']
                        );
                    } else {
                        $data = array(
                            'watermark' => array(
                                'NULL'
                            )
                        );
                    }
                    $GalleryModel->modifyAll($data);
                } else {
                    $this->log('No image records found in DB');
                }
            } else {
                $this->log("\$_GET['foreign_id'] is not set or has incorrect value");
            }
        }
        exit();
    }
}
?>