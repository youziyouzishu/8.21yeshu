<?php

namespace app\api\controller;

use app\admin\model\Shopcar;
use app\api\basic\Base;
use support\Request;
use Webman\Exception\BusinessException;

class IndexController extends Base
{

    protected array $noNeedLogin = ['*'];

    function index(Request $request)
    {
        $coupons = Shopcar::all();
        $coupon = $coupons->firstWhere('id', 2);
        if (!$coupon){
            return $this->fail('无效优惠券');
        }


        return $this->success('ok');
    }



}
