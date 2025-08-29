<?php

namespace app\api\controller;

use app\admin\model\Area;
use app\admin\model\DeliveryConfig;
use app\admin\model\GoodsOrders;
use app\admin\model\GoodsOrdersAssess;
use app\admin\model\Shopcar;
use app\admin\model\UsersAddress;
use app\admin\model\UsersCoupon;
use app\admin\model\Warehouse;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use support\Db;
use support\Log;
use support\Request;
use Webman\RedisQueue\Client;
use Workerman\Worker;


class IndexController extends Base
{

    protected array $noNeedLogin = ['index','test'];

    function index(Request $request)
    {

        $id = 38;
        $order = GoodsOrders::where(['id' => $id])->first();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if (!in_array($order->status, [0, 1, 3])) {
            return $this->fail('订单状态错误');
        }
        if ($order->status == 1 || $order->status == 3) {
            //退款
            $refund_order_no = Pay::generateOrderSn();
            $res = Pay::refund(1, $order->pay_amount, $order->ordersn, $refund_order_no, '取消订单退款');
            dump($res);
        }
        $order->status = 2;
        $order->cancel_time = Carbon::now();
        $order->save();
        return  $this->success('成功');

    }





}
