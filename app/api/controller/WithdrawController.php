<?php

namespace app\api\controller;

use app\admin\model\User;
use app\admin\model\UsersWithdraw;
use app\api\basic\Base;
use support\Log;
use support\Request;
use support\Response;

class WithdrawController extends Base
{
    /**
     * 提现
     * @param Request $request
     * @return Response
     */
    function insert(Request $request): Response
    {
        $amount = $request->post('amount');
        $type = $request->post('type');
        $bankcard_id = $request->post('bankcard_id');
        if (!$amount) {
            return $this->fail('请输入提现金额');
        }
        if ($amount < 100) {
            return $this->fail('提现金额不能小于100');
        }
        $user = User::find($request->user_id);
        if ($user->money < $amount) {
            return $this->fail('余额不足');
        }
        try {
            User::money(-$amount, $request->user_id, '提现');
            UsersWithdraw::create([
                'user_id' => $request->user_id,
                'withdraw_amount' => $amount,
                'into_amount' => $amount,
                'type' => $type,
                'bankcard_id' => $bankcard_id ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('提现失败');
            Log::error($e->getMessage());
            return $this->fail('提现失败');
        }
        return $this->success('提现成功');
    }

    function select(Request $request)
    {
        $rows = UsersWithdraw::where('user_id', $request->user_id)->latest()->paginate()->items();
        return $this->success('成功',$rows);
    }
}
