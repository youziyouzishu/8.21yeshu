<?php

namespace app\api\controller;

use app\api\basic\Base;
use support\Redis;
use support\Request;
use Tinywan\Jwt\JwtToken;

class IndexController extends Base
{

    protected array $noNeedLogin = ['index','test'];

    function index(Request $request)
    {
        $a=Redis::set('aaa','1111');
        return $this->success('成功',$a);
    }

    function test()
    {

    }



}
