<?php

namespace app\api\controller;

use app\admin\model\UsersBankcard;
use app\api\basic\Base;
use support\Request;
use support\Response;

class BankCardController extends Base
{
    /**
     * 添加银行卡
     * @param Request $request
     * @return Response
     */
    function insert(Request $request): Response
    {
        $truename = $request->post('truename');
        $cardnum = $request->post('cardnum');
        $bank = $request->post('bank');
        $open_bank = $request->post('open_bank');
        $mobile = $request->post('mobile');
        if (empty($truename)){
            return $this->fail('真实姓名不能为空');
        }
        if (empty($cardnum)){
            return $this->fail('银行卡号不能为空');
        }
        if (empty($bank)){
            return $this->fail('银行不能为空');
        }
        if (empty($open_bank)){
            return $this->fail('开户行不能为空');
        }
        if (empty($mobile)){
            return $this->fail('手机号不能为空');
        }
        UsersBankcard::create([
            'user_id' => $request->user_id,
            'truename' => $truename,
            'cardnum' => $cardnum,
            'bank' => $bank,
            'open_bank' => $open_bank,
            'mobile' => $mobile,
        ]);
        return $this->success('成功');
    }

    /**
     * 删除银行卡
     * @param Request $request
     * @return Response
     */
    function delete(Request $request): Response
    {
        $ids = $request->post('ids');
        if (empty($ids)) {
            return $this->fail('请选择要删除的银行卡');
        }
        UsersBankcard::where(['user_id' => $request->user_id])->whereIn('id', $ids)->delete();
        return $this->success('成功');
    }

    /**
     * 查询银行卡
     * @param Request $request
     * @return Response
     */
    function select(Request $request): Response
    {
        $rows = UsersBankcard::where(['user_id' => $request->user_id])->latest()->get();
        return $this->success('成功', $rows);
    }

}
