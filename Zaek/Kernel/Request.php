<?php
namespace Zaek\Kernel;

use Zaek\Engine\Main;

class Request
{
    protected $_app;

    public function __construct(Main $app)
    {
        $this->_app = $app;
    }

    public function cookie()
    {
        if(func_num_args() == 1) {
            return $_COOKIE[func_get_arg(0)] ?? null;
        }

        return $_COOKIE;
    }

    public function post()
    {
        if(func_num_args() == 1) {
            return $_POST[func_get_arg(0)] ?? null;
        }

        return $_POST;
    }

    public function get()
    {
        if(func_num_args() == 1) {
            return $_GET[func_get_arg(0)] ?? null;
        }

        return $_GET;
    }

    public function bin()
    {
        return $_FILES;
    }

    public function server()
    {
        if(func_num_args() == 1) {
            return $_SERVER[func_get_arg(0)] ?? null;
        }

        return $_SERVER;
    }
}