<?php

namespace app\queue\redis;

use app\admin\model\Area;
use app\admin\model\GoodsOrders;
use app\admin\model\GoodsOrdersAssess;
use app\admin\model\GoodsOrdersSubs;
use app\admin\model\UsersCoupon;
use Webman\RedisQueue\Client;
use Webman\RedisQueue\Consumer;

class Job implements Consumer
{
    // 要消费的队列名
    public $queue = 'job';

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public $connection = 'default';

    // 消费
    public function consume($data)
    {
        #优惠券过期
        $event = $data['event'];
        if ($event == 'user_coupon_expire') {
            $id = $data['id'];
            $user_coupon = UsersCoupon::find($id);
            if ($user_coupon->status == 1) {
                $user_coupon->status = 3;
                $user_coupon->save();
            }
        }

        if ($event == 'order_assess') {
            $id = $data['id'];
            $order = GoodsOrders::find($id);
            if ($order->status == 7) {
                $order->status = 9;
                $order->save();
                $goods_score = 5.0;
                $goods_content = '默认好评!';
                $image = null;
                $transport_score = 5.0;
                $transport_content  = '默认好评!';
                $satisfied = 1;
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
                    $assess_rate = GoodsOrdersAssess::where('goods_id',$item->goods_id)->avg('goods_score');
                    $item->goods()->update([
                        'assess_rate' => $assess_rate,
                    ]);
                });
            }
        }

        if ($event == 'order_expire') {
            $id = $data['id'];
            $order = GoodsOrders::find($id);
            if ($order->status == 0) {
                //如果还没支付 取消订单
                $order->status = 2;
                $order->save();
            }
        }

        if ($event == 'distribute_order') {
            $id = $data['id'];
            $order = GoodsOrders::find($id);
            if ($order->status == 3 && $order->distribute_type == 2) {
                //进行匹配
                $users = $order->warehouse->user()->where(['work_status' => 1])->get();
                // 计算用户距离并找到最近的用户
                $minDistance = null;
                $userIdWithMinDistance = null;
                foreach ($users as $user){
                    if (!isset($user->lng, $user->lat)) {
                        continue; // 跳过无效坐标
                    }
                    $distance = Area::getDistanceFromLngLat($user->lng, $user->lat, $order->warehouse->lng, $order->warehouse->lat);
                    if ($distance !== null && ($minDistance === null || $distance < $minDistance)) {
                        $minDistance = $distance;
                        $userIdWithMinDistance = $user->id;
                    }
                }
                if ($userIdWithMinDistance) {
                    // 更新订单的配送人员ID
                    $order->transport_id = $userIdWithMinDistance;
                    $order->save();
                } else {
                    // 没有找到合适的用户，重新进入队列
                    Client::send('job', ['event' => 'distribute_order', 'id' => $order->id], 30);
                }
            }
        }


    }

}
