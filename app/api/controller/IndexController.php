<?php

namespace app\api\controller;

use app\api\basic\Base;
use Carbon\Carbon;
use support\Request;
use Workerman\Worker;


class IndexController extends Base
{

    protected array $noNeedLogin = ['index','test'];

    function index(Request $request)
    {
        $data = "\x01\x02\x03\x04\x05\x06\x07\x08";
        $result = unpack('C2chars/Sint/Nlong', $data);
        dump($result);
    }

    function stringToBinary($string) {
        $binary = '';
        $unpacked = unpack('C*', $string); // 将字符串解包为字节数组
        dump($unpacked);
        foreach ($unpacked as $byte) {
            $binary .= sprintf('%08b', $byte); // 将每个字节转为 8 位二进制
        }
        return $binary;
    }



}
