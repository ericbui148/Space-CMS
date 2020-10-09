<?php
namespace App\Controllers;
use App\Controllers\Components\UtilComponent;

class ProductFavsController extends BaseShopCartController
{


    public function Add()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_COOKIE[$this->defaultCookie])) {
                $data = unserialize(stripslashes($_COOKIE[$this->defaultCookie]));
            }
            if (! isset($data) || $data === FALSE) {
                $data = array();
            }
            $arr = UtilComponent::stripFav($_POST);
            if (! empty($arr)) {
                $data[serialize($arr)] = 1;
            }
            setcookie($this->defaultCookie, serialize($data), time() + 60 * 60 * 24 * 30);
            AppController::jsonResponse(array(
                'status' => 'OK',
                'code' => 202,
                'text' => __('system_202', true)
            ));
        }
        exit();
    }

    public function Check()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (! isset($_COOKIE[$this->defaultCookie]) || empty($_COOKIE[$this->defaultCookie])) {
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 100,
                    'text' => 'Fav list not set or empty.'
                ));
            }
            $data = unserialize(stripslashes($_COOKIE[$this->defaultCookie]));
            if (! isset($data) || $data === FALSE) {
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 100,
                    'text' => 'Fav list is empty.'
                ));
            }
            $key = serialize($_POST);
            if (! array_key_exists($key, $data)) {
                AppController::jsonResponse(array(
                    'status' => 'ERR',
                    'code' => 100,
                    'text' => 'Stock was not found in the favs list.'
                ));
            }
            AppController::jsonResponse(array(
                'status' => 'OK',
                'code' => 200,
                'text' => 'Stock found in the favs list.'
            ));
        }
        exit();
    }


    public function Remove()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_POST['hash']) && ! empty($_POST['hash']) && isset($_COOKIE[$this->defaultCookie]) && ! empty($_COOKIE[$this->defaultCookie])) {
                $favs = unserialize(stripslashes($_COOKIE[$this->defaultCookie]));
                foreach ($favs as $key => $whatever) {
                    if ($_POST['hash'] == md5($key)) {
                        $favs[$key] = NULL;
                        unset($favs[$key]);
                        if (empty($favs)) {
                            $favs = "";
                            $time = time() - 3600;
                        } else {
                            $favs = serialize($favs);
                            $time = time() + 60 * 60 * 24 * 30;
                        }
                        setcookie($this->defaultCookie, $favs, $time);
                        $response = array(
                            'status' => 'OK',
                            'code' => 203,
                            'text' => __('system_203', true)
                        );
                        break;
                    }
                }
            }
            if (! isset($response)) {
                $response = array(
                    'status' => 'ERR',
                    'code' => 102,
                    'text' => __('system_102', true)
                );
            }
            AppController::jsonResponse($response);
        }
        exit();
    }


    public function Empty()
    {
        $this->setAjax(true);
        if ($this->isXHR()) {
            if (isset($_COOKIE[$this->defaultCookie]) && ! empty($_COOKIE[$this->defaultCookie])) {
                setcookie($this->defaultCookie, "", time() - 3600);
                $response = array(
                    'status' => 'OK',
                    'code' => 204,
                    'text' => __('system_204', true)
                );
            } else {
                $response = array(
                    'status' => 'ERR',
                    'code' => 103,
                    'text' => __('system_103', true)
                );
            }
            AppController::jsonResponse($response);
        }
        exit();
    }
}
?>