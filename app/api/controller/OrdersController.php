<?php

namespace app\api\controller;

use app\admin\model\GoodsOrders;
use app\admin\model\User;
use app\api\basic\Base;
use app\api\service\Pay;
use support\Db;
use support\Log;
use support\Request;


class OrdersController extends Base
{
    function pay(Request $request)
    {
        $ordersn = $request->post('ordersn');
        $pay_type = $request->post('pay_type');#支付类型:1=微信,2=余额
        $order = GoodsOrders::where('ordersn', $ordersn)->first();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 0) {
            return $this->fail('订单状态错误');
        }
        if (!in_array($pay_type, [1, 2])) {
            return $this->fail('支付类型错误');
        }
        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            if ($pay_type == 1) {
                $result = Pay::pay($pay_type, $order->pay_amount, $order->ordersn, '购买商品', 'goods');
            } else {
                $user = User::find($order->user_id);
                if ($user->money < $order->pay_amount) {
                    return $this->fail('余额不足');
                }
                User::money(-$order->pay_amount, $order->user_id, '购买商品');
                // 创建一个新的请求对象 直接调用支付
                $notify = new NotifyController();
                $request->setParams('get', ['paytype' => 'balance', 'out_trade_no' => $ordersn, 'attach' => 'goods']);
                $res = $notify->balance($request);
                $res = json_decode($res->rawBody());
                if ($res->code == 1) {
                    throw new \Exception($res->msg);
                }
                $result = null;
            }
            Db::connection('plugin.admin.mysql')->commit();
        } catch (\Throwable $e) {
            Db::connection('plugin.admin.mysql')->rollBack();
            Log::error('订单支付失败');
            Log::error($e->getMessage());
            return $this->fail('订单支付失败');
        }
        return $this->success('成功', $result);
    }


}
