<?php

namespace app\api\controller;

use app\api\basic\Base;
use Carbon\Carbon;
use support\Request;

use support\Response;
use Webman\Openai\Chat;
use Webman\Openai\Image;
use Workerman\Protocols\Http\Chunk;
use Workerman\Protocols\Http\ServerSentEvents;
use Workerman\Timer;

class IndexController extends Base
{

    protected array $noNeedLogin = ['index','test'];

    function index(Request $request)
    {

        // 获取当前时间
        $startTime = Carbon::now();

        // 增加一天
        $endTime = $startTime->copy()->addMinutes(30);

        $diff = $endTime->diffForHumans($startTime);
        dump($diff);
    }

    function stringToBinary($string) {
        $binary = '';
        $unpacked = unpack('C*', $string); // 将字符串解包为字节数组
        foreach ($unpacked as $byte) {
            $binary .= sprintf('%08b', $byte); // 将每个字节转为 8 位二进制
        }
        return $binary;
    }



}
