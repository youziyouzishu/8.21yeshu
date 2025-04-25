<?php

namespace app\api\controller;

use app\admin\model\Activity;
use app\api\basic\Base;
use support\Request;
use Workerman\Protocols\Websocket;

class ActivityController extends Base
{
    protected array $noNeedLogin = ['select'];
    function select(Request $request)
    {
        $rows = Activity::latest()->get();
        return $this->success('成功',$rows);
    }

    function detail(Request $request)
    {
        $id = $request->post('id');
        $row = Activity::with('coupon')->find($id);
        return $this->success('成功',$row);
    }

}
