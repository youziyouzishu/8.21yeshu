<?php

use support\Container;

/**
 * Here is your custom functions.
 */


function get_rand_string($length = 32): string
{
    $str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $key = "";
    for ($i = 0; $i < $length; $i++) {
        $key .= $str[mt_rand(0, 61)];    //生成php随机数
    }
    return $key;
}

function hook($hookname, $data)
{
    $path = str_replace('/', '\\', $hookname);
    list($controller, $action) = explode('@', $path);
    $controller = Container::get($controller);
    if (method_exists($controller, $action)) {
        try {
            $response = call_user_func([$controller, $action], $data);
        } catch (\Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        return $response;
    }
}