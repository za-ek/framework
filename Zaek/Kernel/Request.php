<?php
namespace Zaek\Kernel;

class Request
{
    public function cookie()
    {
        if(func_num_args() == 1) {

        } else if (func_get_args() == 2) {

        } else {
            return $_COOKIE;
        }
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

    public function server()
    {
        if(func_num_args() == 1) {
            return $_SERVER[func_get_arg(0)] ?? null;
        }

        return $_SERVER;
    }
}