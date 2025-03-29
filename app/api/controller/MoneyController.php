<?php

namespace app\api\controller;

use app\admin\model\GoodsOrders;
use app\admin\model\UsersMoneyLog;
use app\admin\model\UsersRecharge;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use support\Log;
use support\Request;

class MoneyController extends Base
{
    #充值
    function recharge(Request $request)
    {
        $amount = $request->post('amount');
        $pay_type = $request->post('pay_type');# 1微信
        if ($pay_type == 1) {
            try {
                $ordersn = Pay::generateOrderSn();
                UsersRecharge::create([
                    'user_id' => $request->user_id,
                    'amount' => $amount,
                    'ordersn' => $ordersn,
                    'pay_type' => $pay_type,
                ]);
                $result = Pay::pay($pay_type, $amount, $ordersn, '余额充值', 'recharge');
            }catch (\Throwable $e){
                Log::error('支付失败');
                Log::error($e->getMessage());
                return $this->fail('支付失败');
            }
        } else {
            return $this->fail('支付类型错误');
        }
        return $this->success('成功',$result);
    }

    #获取账变记录
    function select(Request $request)
    {
        $datetime = $request->post('datetime');
        $status = $request->post('status'); #0=全部 1=支出，2=收入
        $datetime = Carbon::parse($datetime);
        // 提取年份和月份
        $year = $datetime->year;
        $month = $datetime->month;
        $rows = UsersMoneyLog::
            where('user_id', $request->user_id)
            ->when(!empty($status), function ($query) use ($status) {
                if ($status == 1) {
                    $query->where('money', '<', 0);
                } else {
                    $query->where('money', '>', 0);
                }
            })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderByDesc('id')
            ->paginate()
            ->getCollection()
            ->each(function ($item) {
                if ($item->money > 0) {
                    $item->money = '+' . $item->money;
                }
            });
        return $this->success('获取成功', $rows);
    }

}
