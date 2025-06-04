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
        $address_id = 4;
        $shopcar_ids = [22];
        $coupon_id = 7;
        $delivery_type = 2;#配送类型:1=立即配送,2=预约配送
        $delivery_time = $request->post('delivery_time', '');#配送时间
        $mark = $request->post('mark', '');
        $invoice_id = $request->post('invoice_id', 0);
        if (empty($shopcar_ids)) {
            return $this->fail('请选择商品');
        }
        if (empty($delivery_type)) {
            return $this->fail('请选择配送类型');
        }
        $address = UsersAddress::find($address_id);
        if (!$address) {
            return $this->fail('地址不存在');
        }
        $warehouses = Warehouse::all();
        if ($warehouses->isEmpty()) {
            return $this->fail('没有可用的仓库');
        }
        $closestWarehouse = null;
        $minDistance = PHP_FLOAT_MAX;
        $maxDeliveryDistance = DeliveryConfig::max('end'); // 最大配送距离
        if (!$maxDeliveryDistance) {
            return $this->fail('未配置最大距离');
        }
        foreach ($warehouses as $warehouse) {
            $distance = Area::getDistanceFromLngLat($address->lng, $address->lat, $warehouse->lng, $warehouse->lat);
            if ($distance < $minDistance && $distance <= $maxDeliveryDistance) {
                $minDistance = $distance;
                $closestWarehouse = $warehouse;
            }
        }
        if ($closestWarehouse) {
            Db::connection('plugin.admin.mysql')->beginTransaction();
            try {
                $distance = round($minDistance, 2);
                $freight = DeliveryConfig::where('start', '<=', $distance)->where('end', '>=', $distance)->first()->price;
                $shopcars = Shopcar::where(['user_id' => $request->user_id])->whereIn('id', $shopcar_ids)->get();
                $total_goods_amount = 0;
                $coupon_amount = 0;
                $sub_data = [];
                foreach ($shopcars as $shopcar) {
                    $goods_amount = $shopcar->goods->price * $shopcar->num;
                    $total_goods_amount += $goods_amount;
                    $sub_data[] = [
                        'goods_id' => $shopcar->goods_id,
                        'num' => $shopcar->num,
                        'amount' => $shopcar->goods->price,
                        'total_amount' => $goods_amount,
                    ];
                    $shopcar->delete();
                }
                $pay_amount = $total_goods_amount + $freight;
                $coupon = UsersCoupon::where(['user_id' => $request->user_id, 'status' => 1])
                    ->where(function ($query) use ($pay_amount) {
                        $query->where('type', 1)->orWhere(function ($query) use ($pay_amount) {
                            $query->where('type', 2)->where('with_amount', '<=', $pay_amount);
                        });
                    })
                    ->where('id',$coupon_id)
                    ->first();
                if (!empty($coupon)) {
                    $coupon_amount = $coupon->amount; // 获取优惠券额度
                    $pay_amount = $pay_amount - $coupon_amount;
                    $coupon->delete();
                }
                $order = GoodsOrders::create([
                    'user_id' => $request->user_id,
                    'address_id' => $address->id,
                    'warehouse_id' => $closestWarehouse->id,
                    'delivery_type' => $delivery_type,
                    'delivery_time' => $delivery_time,
                    'ordersn' => Pay::generateOrderSn(),
                    'pay_amount' => $pay_amount,
                    'coupon_amount' => $coupon_amount,
                    'goods_amount' => $total_goods_amount,
                    'freight' => $freight,
                    'distance' => $distance,
                    'mark' => $mark,
                    'invoice_id' => $invoice_id
                ]);
                $order->subs()->createMany($sub_data);
                Client::send('job', ['order_id' => $order->id, 'event' => 'order_expire'], 60 * 15);
                Db::connection('plugin.admin.mysql')->commit();
            } catch (\Throwable $e) {
                Db::connection('plugin.admin.mysql')->rollBack();
                Log::error('创建订单失败');
                Log::error($e->getMessage());
                dump($e->getMessage());

                return $this->fail('创建订单失败');
            }
            return $this->success('成功', $order);
        } else {
            return $this->fail('超出最大配送范围');
        }
    }

    function stringToBinary($string) {
        $binary = '';
        $unpacked = unpack('C*', $string); // 将字符串解包为字节数组
        dump($unpacked);
        foreach ($unpacked as $byte) {
            $binary .= sprintf('%08b', $byte); // 将每个字节转为 8 位二进制
        }
        return $binary;
    }



}
