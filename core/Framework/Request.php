<?php
namespace Core\Framework;

class Request
{
    public $controller = null;
    public $action = null;
    public $params = [];
    public $data = [];
    public $server = [];
    public $evn = [];
    public $query = [];
}