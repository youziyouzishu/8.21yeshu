<?php

namespace app\api\controller;

use app\admin\model\Area;
use app\admin\model\DeliveryConfig;
use app\admin\model\Goods;
use app\admin\model\Shopcar;
use app\admin\model\Warehouse;
use app\api\basic\Base;
use support\Request;

class ShopcarController extends Base
{

    function index(Request $request)
    {
        $shopcars = Shopcar::where(['user_id' => $request->user_id])->with(['goods'])->get();
        $goods = [];
        foreach ($shopcars as $shopcar) {
            $goodsType = $shopcar->goods->type_text;
            if (!isset($goods[$goodsType])) {
                $goods[$goodsType] = ['name' => $goodsType, 'list' => []];
            }
            $goods[$goodsType]['list'][] = $shopcar;
        }
        return $this->success('成功', $goods);
    }
    
    /**
     * 增加
     * @param Request $request
     * @return \support\Response
     */
    function insert(Request $request)
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
    function select(Request $request)
    {
        $shopcars = Shopcar::where(['user_id' => $request->user_id])->with(['goods'])->get();
        $total_amount = 0;
        foreach ($shopcars as $shopcar) {
            $total_amount += $shopcar->goods->price * $shopcar->num;
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

        return $this->success('成功', ['count'=>$shopcars->count(),'total_amount'=>$total_amount,'list'=>$shopcars,'freight'=>$freight]);
    }

    #编辑购物车
    function update(Request $request)
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


    function delete(Request $request)
    {
        $ids = $request->post('ids');
        Shopcar::destroy($ids);
        return $this->success('成功');
    }
}
