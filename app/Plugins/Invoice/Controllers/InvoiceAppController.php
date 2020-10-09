<?php
namespace App\Plugins\Invoice\Controllers;

use Core\Framework\Plugin;
use Core\Framework\Registry;

class InvoiceAppController extends Plugin
{

    public function __construct()
    {
        $this->setLayout('Admin');
    }


    public static function getConst($const)
    {
        $registry = Registry::getInstance();
        $store = $registry->get('Invoice');
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
            'app/web/invoices'
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


    public function isInvoiceReady()
    {
        $reflector = new \ReflectionClass('Core\\Framework\\Plugin');
        try {
            $ReflectionMethod = $reflector->getMethod('isInvoiceReady');
            return $ReflectionMethod->invoke(new Plugin(), 'isInvoiceReady');
        } catch (\ReflectionException $e) {
            return false;
        }
    }
}
?>