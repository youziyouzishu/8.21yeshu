<?php

namespace app\api\controller;

use app\admin\model\Area;
use app\admin\model\DeliveryConfig;
use app\admin\model\Goods;
use app\admin\model\PrivacyService;
use app\admin\model\Shopcar;
use app\admin\model\UsersAddress;
use app\admin\model\UsersCollect;
use app\admin\model\Warehouse;
use app\api\basic\Base;
use support\Request;

class GoodsController extends Base
{
    protected array $noNeedLogin = [];

    #提交服务
    function addService(Request $request)
    {
        $type = $request->post('type');#类型:1=水机维修,2=水机清洗
        $user_address_id = $request->post('user_address_id');
        $visit_time = $request->post('visit_time');
        $image = $request->post('image');
        $mark = $request->post('mark');
        PrivacyService::create([
            'user_id' => $request->user_id,
            'user_address_id' => $user_address_id,
            'type' => $type,
            'visit_time' => $visit_time,
            'image' => $image,
            'mark' => $mark,
        ]);
        return $this->success();
    }

    #获取服务列表
    function getServiceList(Request $request)
    {
        $type = $request->post('type');#类型:1=水机维修,2=水机清洗
        $rows = PrivacyService::where(['user_id' => $request->user_id, 'type' => $type])->orderByDesc('id')->paginate()->items();
        return $this->success('成功', $rows);
    }

    #商品列表
    function getGoodsList(Request $request)
    {
        $order = $request->post('order');#排序:1=综合,2=销量升序,3=销量降序,4=价格升序,5=价格降序,6=最新
        $type = $request->post('type');#类型:1=水机直购,2=水机租赁,3=桶装水
        $rows = Goods::where(['type' => $type])->when(!empty($order), function ($query) use ($order, $type) {
            if ($order == 1) {
                $query->orderByDesc('sale_count');
            } elseif ($order == 2) {
                $query->orderBy('sales', 'asc');
            } elseif ($order == 3) {
                $query->orderBy('sales', 'desc');
            } elseif ($order == 4) {
                if ($type == 1) {
                    $query->orderBy('price', 'asc');
                } else {
                    $query->orderBy('rent', 'asc');
                }
            } elseif ($order == 5) {
                if ($type == 1) {
                    $query->orderBy('price', 'desc');
                } else {
                    $query->orderBy('rent', 'desc');
                }
            } elseif ($order == 6) {
                $query->orderByDesc('id');
            }
        })->paginate()->items();
        return $this->success('成功', $rows);
    }

    #商品详情
    function getGoodsDetail(Request $request)
    {
        $goods_id = $request->post('goods_id');
        // 查询Privacy
        $row = Goods::find($goods_id);
        // 检查当前用户是否收藏了该Privacy
        $row->setAttribute('is_collected', $row->collect()->where('user_id', $request->user_id)->exists());
        return $this->success('成功', $row);
    }

    #收藏
    function collect(Request $request)
    {
        $privacy_id = $request->post('privacy_id');
        $row = Goods::find($privacy_id);
        $collect = $row->collect()->where(['user_id' => $request->user_id])->first();
        if ($collect) {
            $collect->delete();
            return $this->success('取消收藏', false);
        } else {
            $row->collect()->create(['user_id' => $request->user_id]);
            return $this->success('收藏成功', true);
        }
    }

    #添加购物车
    function shopcarAdd(Request $request)
    {
        $goods_id = $request->post('goods_id');
        $num = $request->post('num');
        $row = Goods::find($goods_id);
        $shopcar = $row->shopcar()->where(['user_id' => $request->user_id])->first();
        if ($shopcar) {
            $shopcar->num += $num;
            $shopcar->save();
        } else {
            $row->shopcar()->create(['user_id' => $request->user_id, 'num' => $num]);
        }
        return $this->success('成功');
    }

    #获取购物车列表
    function getShopcarList(Request $request)
    {
        $rows = Shopcar::with(['goods'])->where(['user_id' => $request->user_id])->paginate()->items();
        return $this->success('成功', $rows);
    }

    #编辑购物车
    function shopcarEdit(Request $request)
    {
        $id = $request->post('id');
        $num = $request->post('num');
        $row = Shopcar::find($id);
        $row->num = $num;
        $row->save();
        if ($num == 0) {
            $row->delete();
        }
        return $this->success('成功');
    }

    #计算价格
    function getPrice(Request $request)
    {
        $address_id = $request->post('address_id');
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
            return $this->fail('没有可用的配送距离');
        }
        foreach ($warehouses as $warehouse) {
            $distance = Area::getDistanceFromLngLat($address->lng, $address->lat, $warehouse->lng, $warehouse->lat);
            if ($distance < $minDistance && $distance <= $maxDeliveryDistance) {
                $minDistance = $distance;
                $closestWarehouse = $warehouse;
            }
        }
        if ($closestWarehouse) {
            $distance = round($minDistance, 2);
            $freight = DeliveryConfig::where('start', '<=', $distance)->where('end', '>=', $distance)->first()->price;
            $shopcars = Shopcar::where(['user_id' => $request->user_id])->get();

            $groupedGoods = [];
            foreach ($shopcars as $shopcar) {
                $goodsType = $shopcar->goods->type;
                if (!isset($groupedGoods[$goodsType])) {
                    $groupedGoods[$goodsType] = [];
                }
                $groupedGoods[$goodsType][] = $shopcar->goods;
            }
            // 你可以在这里处理找到的最近仓库
            return $this->success('成功', [
                'warehouse' => $closestWarehouse,
                'distance' => $distance,
                'freight' => $freight,
            ]);
        }else{
            return $this->fail('没有找到在配送范围内的仓库');
        }

    }

}
