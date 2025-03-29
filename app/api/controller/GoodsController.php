<?php

namespace app\api\controller;

use app\admin\model\Area;
use app\admin\model\Category;
use app\admin\model\DeliveryConfig;
use app\admin\model\Goods;
use app\admin\model\GoodsOrders;
use app\admin\model\Shopcar;
use app\admin\model\UsersAddress;
use app\admin\model\UsersCoupon;
use app\admin\model\Warehouse;
use app\api\basic\Base;
use app\api\service\Pay;
use support\Db;
use support\Log;
use support\Request;
use support\Response;

class GoodsController extends Base
{
    protected array $noNeedLogin = [];


    function getCategoryList(Request $request)
    {
        $rows = Category::all();
        return $this->success('成功', $rows);
    }

    #商品列表
    function select(Request $request)
    {
        $order = $request->post('order');#排序:1=综合,2=销量升序,3=销量降序,4=价格升序,5=价格降序,6=最新
        $type = $request->post('type');#类型:1=水机直购,2=水机租赁,3=桶装水
        $category_id = $request->post('category_id');
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
            $distance = Area::getDistanceFromLngLat($request->lng, $request->lat, $warehouse->lng, $warehouse->lat);
            if ($distance < $minDistance && $distance <= $maxDeliveryDistance) {
                $minDistance = $distance;
                $closestWarehouse = $warehouse;
            }
        }

        if ($closestWarehouse) {
            $distance = round($minDistance, 2);
            $freight = DeliveryConfig::where('start', '<=', $distance)->where('end', '>=', $distance)->first()->price;
        } else {
            $freight = null;
        }
        $rows = Goods::where(['type' => $type])
            ->when(!empty($category_id), function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            })
            ->when(!empty($order), function ($query) use ($order) {
                if ($order == 1) {
                    $query->latest('sales');
                } elseif ($order == 2) {
                    $query->oldest('sales');
                } elseif ($order == 3) {
                    $query->latest('sales');
                } elseif ($order == 4) {
                    $query->oldest('price');
                } elseif ($order == 5) {
                    $query->latest('price');
                } elseif ($order == 6) {
                    $query->latest('id');
                }
            })
            ->paginate()
            ->items();
        foreach ($rows as $row) {
            $row->setAttribute('freight', $freight);
        }
        return $this->success('成功', $rows);
    }

    #商品详情
    function detail(Request $request)
    {
        $id = $request->post('id');
        $row = Goods::find($id);
        $row->setAttribute('is_collected', $row->collect()->where('user_id', $request->user_id)->exists());
        return $this->success('成功', $row);
    }

    #收藏
    function collect(Request $request)
    {
        $id = $request->post('id');
        $row = Goods::find($id);
        $collect = $row->collect()->where(['user_id' => $request->user_id])->first();
        if ($collect) {
            $collect->delete();
            return $this->success('取消收藏', false);
        } else {
            $row->collect()->create(['user_id' => $request->user_id]);
            return $this->success('收藏成功', true);
        }
    }


    #计算价格
    function getPrice(Request $request)
    {
        $address_id = $request->post('address_id');
        $shopcar_ids = $request->post('shopcar_ids');
        $coupon_id = $request->post('coupon_id');
        if (empty($shopcar_ids)) {
            return $this->fail('请选择商品');
        }
        $address = null;
        if (!empty($address_id)) {
            $address = UsersAddress::find($address_id);
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
            $distance = Area::getDistanceFromLngLat($address?$address->lng:$request->lng, $address?$address->lat:$request->lat, $warehouse->lng, $warehouse->lat);
            if ($distance < $minDistance && $distance <= $maxDeliveryDistance) {
                $minDistance = $distance;
                $closestWarehouse = $warehouse;
            }
        }


        if ($closestWarehouse) {
            $distance = round($minDistance, 2);
            $freight = DeliveryConfig::where('start', '<=', $distance)->where('end', '>=', $distance)->first()->price;
            $shopcars = Shopcar::where(['user_id' => $request->user_id])->whereIn('id', $shopcar_ids)->get();
            $total_goods_amount = 0;
            $coupon_amount = 0;
            $goods = [];
            foreach ($shopcars as $shopcar) {
                $goods_amount = $shopcar->goods->price * $shopcar->num;
                $total_goods_amount += $goods_amount;
                $shopcar->setAttribute('goods_amount', $goods_amount);
                $goodsType = $shopcar->goods->type_text;
                if (!isset($goods[$goodsType])) {
                    $goods[$goodsType] = ['name' => $goodsType, 'list' => []];
                }
                $goods[$goodsType]['list'][] = $shopcar;
            }

            $goods = array_values($goods);
            $pay_amount = $total_goods_amount + $freight;
            $coupons = UsersCoupon::where(['user_id' => $request->user_id, 'status' => 1])
                ->where(function ($query) use ($pay_amount) {
                    $query->where('type', 1)->orWhere(function ($query) use ($pay_amount) {
                        $query->where('type', 2)->where('with_amount', '<=', $pay_amount);
                    });
                })
                ->get();

            if (!empty($coupon_id)) {
                $coupon = $coupons->firstWhere('id', 1);
                if (!$coupon) {
                    return $this->fail('无效优惠券');
                }
                $coupon_amount = $coupon->amount; // 获取优惠券额度
                $pay_amount = $pay_amount - $coupon_amount;
            }
            // 你可以在这里处理找到的最近仓库
            return $this->success('成功', [
                'warehouse' => $closestWarehouse,
                'distance' => $distance,
                'freight' => $freight,
                'goods' => $goods,
                'coupons' => $coupons,
                'coupon_amount' => $coupon_amount,
                'pay_amount' => $pay_amount,
            ]);
        } else {
            return $this->fail('超出最大配送范围');
        }
    }


    /**
     * 创建订单
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    function createOrder(Request $request)
    {
        $address_id = $request->post('address_id');
        $shopcar_ids = $request->post('shopcar_ids');
        $coupon_id = $request->post('coupon_id');
        $delivery_type = $request->post('delivery_type');#配送类型:1=立即配送,2=预约配送
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
                $coupons = UsersCoupon::where(['user_id' => $request->user_id, 'status' => 1])
                    ->where(function ($query) use ($pay_amount) {
                        $query->where('type', 1)->orWhere(function ($query) use ($pay_amount) {
                            $query->where('type', 2)->where('with_amount', '<=', $pay_amount);
                        });
                    })
                    ->get();
                if (!empty($coupon_id)) {
                    $coupon = $coupons->firstWhere('id', 1);
                    if (!$coupon) {
                        return $this->fail('无效优惠券');
                    }
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
                Db::connection('plugin.admin.mysql')->commit();
            } catch (\Throwable $e) {
                Db::connection('plugin.admin.mysql')->rollBack();
                Log::error('创建订单失败');
                Log::error($e->getMessage());
                return $this->fail('创建订单失败');
            }
            return $this->success('成功', $order);
        } else {
            return $this->fail('超出最大配送范围');
        }
    }


}
