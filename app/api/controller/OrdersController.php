<?php

namespace app\api\controller;

use app\admin\model\GoodsOrders;
use app\admin\model\User;
use app\api\basic\Base;
use app\api\service\Pay;
use support\Request;


class OrdersController extends Base
{
    function pay(Request $request)
    {
        $ordersn = $request->post('ordersn');
        $pay_type = $request->post('pay_type');#支付类型:1=微信,2=余额
        $order = GoodsOrders::where('ordersn', $ordersn)->first();
        if ($order->status != 0) {
            return $this->fail('订单状态错误');
        }
        if(!in_array($pay_type,[1,2])){
            return $this->fail('支付类型错误');
        }
        if ($pay_type == 1){
            try {
                $result = Pay::pay($pay_type, $order->pay_amount, $order->ordersn, '购买商品', 'goods');
            }catch (\Throwable $e){
                return $this->fail($e->getMessage());
            }
        }else{
            $user = User::find($order->user_id);
            if ($user->money < $order->pay_amount){
                return $this->fail('余额不足');
            }
            User::money(-$order->pay_amount, $order->user_id, '购买商品');
            $result = null;
        }
        return  $this->success('成功',$result);
    }



}
