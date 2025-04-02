<?php

namespace app\api\controller;

use app\admin\model\User;
use app\api\basic\Base;
use support\Request;

class TransportController extends Base
{
    /**
     * 更改工作状态
     * @param Request $request
     * @return \support\Response
     */
    function changWorkStatus(Request $request)
    {
        $status = $request->post('status'); #工作状态:0=否,1=是
        $user = User::find($request->user_id);
        $user->work_status = $status;
        $user->save();
        return $this->success();
    }



}
