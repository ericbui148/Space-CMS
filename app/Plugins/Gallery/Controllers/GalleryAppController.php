<?php
namespace App\Plugins\Gallery\Controllers;

use Core\Framework\Registry;
use Core\Framework\Plugin;

class GalleryAppController extends Plugin
{

    public function __construct()
    {
        $this->setLayout('Admin');
    }

    public static function getConst($const)
    {
        $registry = Registry::getInstance();
        $store = $registry->get('Gallery');
        return isset($store[$const]) ? $store[$const] : NULL;
    }

    public function CheckInstall()
    {
        $this->setLayout('Empty');
        $result = array(
            'status' => 'OK',
            'code' => 200,
            'text' => 'Operation succeeded',
            'info' => array()
        );
        $folders = array(
            'app/web/upload',
            'app/web/upload/large',
            'app/web/upload/medium',
            'app/web/upload/small',
            'app/web/upload/source'
        );
        foreach ($folders as $dir) {
            if (! is_writable($dir)) {
                $result['status'] = 'ERR';
                $result['code'] = 101;
                $result['text'] = 'Permission requirement';
                $result['info'][] = sprintf('Folder \'<span class="bold">%1$s</span>\' is not writable. You need to set write permissions (chmod 777) to directory located at \'<span class="bold">%1$s</span>\'', $dir);
            }
        }
        return $result;
    }
}
?>