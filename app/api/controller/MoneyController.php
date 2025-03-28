<?php

namespace app\api\controller;

use app\api\basic\Base;
use support\Request;

class MoneyController extends Base
{
    function recharge(Request $request)
    {
        $amount = $request->post('amount');
        $pay_type = $request->post('pay_type');
    }

}
