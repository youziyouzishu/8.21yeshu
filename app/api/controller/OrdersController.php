<?php

namespace app\api\controller;

use app\admin\model\GoodsOrders;
use app\admin\model\GoodsOrdersSubs;
use app\admin\model\User;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use support\Db;
use support\Log;
use support\Request;


class OrdersController extends Base
{
    protected array $noNeedLogin = [];
    /**
     * 支付
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
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
                    throw new \Exception('余额不足');
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


    /**
     * 列表
     * @param Request $request
     * @return \support\Response
     */
    function select(Request $request)
    {
        $status = $request->post('status');#状态:0=全部,1=进行中,2=已完成,3=售后
        $rows = GoodsOrders::where(['user_id' => $request->user_id])
            ->when(!empty($status), function ($query) use ($status) {
                if ($status == 1) {
                    $query->whereIn('status', [1, 3, 4, 5, 6]);
                }
                if ($status == 2) {
                    $query->where('status', 7);
                }
                if ($status == 3) {
                    $query->where('status', 8);
                }
            })
            ->with(['subs' => function ($query) {
                $query->with(['goods']);
            }])
            ->orderByDesc('id')
            ->paginate()
            ->getCollection()
            ->each(function (GoodsOrders $item) {
                if ($item->status == 0) {
                    $item->setAttribute('expire_time', Carbon::now()->diffInSeconds($item->created_at->addMinutes(15)));
                }
            });
        return $this->success('成功', $rows);
    }

    /**
     * 详情
     * @param Request $request
     * @return \support\Response
     */
    function detail(Request $request)
    {
        $id = $request->post('id');
        $order = GoodsOrders::where(['user_id' => $request->user_id, 'id' => $id])
            ->with(['subs' => function ($query) {
                $query->with(['goods']);
            },'address','warehouse','transport'])
            ->first();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        return $this->success('成功', $order);
    }

    /**
     * 取消订单
     * @param Request $request
     * @return \support\Response
     */
    function cancel(Request $request)
    {
        $id = $request->post('id');
        $order = GoodsOrders::where(['user_id' => $request->user_id, 'id' => $id])->first();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 0) {
            return $this->fail('订单状态错误');
        }
        $order->status = 2;
        $order->cancel_time = Carbon::now();
        $order->save();
        return  $this->success('成功');
    }

    /**
     * 确认收货
     * @param Request $request
     * @return \support\Response
     */
    function confirm(Request $request)
    {
        $id = $request->post('id');
        $order = GoodsOrders::find($id);
        if (!$order){
            return $this->fail('订单不存在');
        }
        if ($order->status != 6){
            return $this->fail('订单状态错误');
        }
        $arrival_time = Carbon::now();
        if ($arrival_time->gt($order->timeout_time)){
            $order->timeout_status = 2;#超时
        }
        $diff = $arrival_time->diff($order->accept_time);
        $total_time = $diff->i + ($diff->h * 60) + ($diff->d * 24); // 总分钟数
        $order->status = 7;
        $order->settle_status = 2;#已结算
        $order->arrival_time = $arrival_time;
        $order->total_time = $total_time;
        $order->save();
        User::money($order->freight, $order->transport_id, '配送完成');
        return $this->success();
    }

    /**
     * 评价
     * @param Request $request
     * @return \support\Response
     */
    function assess(Request $request)
    {
        $id = $request->post('id');
        $goods_score = $request->post('goods_score');
        $goods_content = $request->post('goods_content');
        $image = $request->post('image');
        $transport_score = $request->post('transport_score');
        $transport_content = $request->post('transport_content');
        $satisfied = $request->post('satisfied');
        $order = GoodsOrders::find($id);
        if (!$order){
            return $this->fail('订单不存在');
        }
        if ($order->status != 7){
            return $this->fail('订单状态错误');
        }
        $order->subs->each(function (GoodsOrdersSubs $item) use ($goods_score, $goods_content, $image, $transport_score, $transport_content, $satisfied){
            $item->assess()->create([
                'order_id' => $item->order_id,
                'user_id' => $item->orders->user_id,
                'goods_id' => $item->goods_id,
                'goods_score' => $goods_score,
                'goods_content' => $goods_content,
                'image' => $image,
                'transport_id' => $item->orders->transport_id,
                'transport_score' => $transport_score,
                'transport_content' => $transport_content,
                'satisfied' => $satisfied,
            ]);
        });
        $order->status = 9;
        $order->save();
        return $this->success();
    }




}
