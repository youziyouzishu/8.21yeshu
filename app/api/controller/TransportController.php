<?php

namespace app\api\controller;

use app\admin\model\Area;
use app\admin\model\GoodsOrders;
use app\admin\model\User;
use app\api\basic\Base;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use plugin\admin\app\model\Option;
use support\Request;
use support\Response;

/**
 * 配送端
 */
class TransportController extends Base
{

    /**
     * 派单列表
     * @param Request $request
     * @return \support\Response
     */
    function getWorkList(Request $request)
    {
        $lat = $request->lat;
        $lng = $request->lng;

        $name = 'admin_config';
        $config = Option::where('name', $name)->value('value');
        $config = json_decode($config);

        $status = $request->post('status');#1=待接单,2=待取货,3=配送中
        $rows = GoodsOrders::where(['transport_id' => $request->user_id])
            ->with(['address', 'warehouse'])
            ->when(!empty($status), function (Builder $query) use ($status) {
                if ($status == 1) {
                    $query->whereIn('status', [3, 4]);
                }
                if ($status == 2) {
                    $query->where('status', 5);
                }
                if ($status == 3) {
                    $query->where('status', 6);
                }
            })
            ->latest()
            ->get();
        foreach ($rows as $row){
            $warehouse_distance = Area::getDistanceFromLngLat($lng, $lat, $row->warehouse->lng, $row->warehouse->lat);#骑手离仓库距离
            $address_distance = Area::getDistanceFromLngLat($lng, $lat, $row->address->lng, $row->address->lat);#骑手离收获地址距离

            $row->setAttribute('warehouse_distance',$warehouse_distance);
            if ($row->status == 3){
                $distance = $warehouse_distance;
            }
            if ($row->status == 4){
                $distance = $warehouse_distance;
            }
            if ($row->status == 5){
                $distance = $address_distance;
            }
            if ($row->status == 6){
                $distance = $address_distance;
            }
            $mins = ceil($config->delivery_speed * $distance);
            $row->setAttribute('mins',$mins);
        }
        return $this->success('成功', $rows);
    }


    /**
     * 订单详情
     * @param Request $request
     * @return Response
     */
    function getWorkDetail(Request $request)
    {
        $lat = $request->lat;
        $lng = $request->lng;
        $name = 'admin_config';
        $config = Option::where('name', $name)->value('value');
        $config = json_decode($config);

        $id = $request->post('id');
        $row = GoodsOrders::where(['transport_id' => $request->user_id, 'id' => $id])
            ->with(['address', 'warehouse'])
            ->first();
        if (!$row) {
            return $this->fail('订单不存在');
        }

        $warehouse_distance = Area::getDistanceFromLngLat($lng, $lat, $row->warehouse->lng, $row->warehouse->lat);#骑手离仓库距离
        $address_distance = Area::getDistanceFromLngLat($lng, $lat, $row->address->lng, $row->address->lat);#骑手离收获地址距离

        $row->setAttribute('warehouse_distance',$warehouse_distance);
        if ($row->status == 3){
            $distance = $warehouse_distance;
        }
        if ($row->status == 4){
            $distance = $warehouse_distance;
        }
        if ($row->status == 5){
            $distance = $address_distance;
        }
        if ($row->status == 6){
            $distance = $address_distance;
        }
        $mins = ceil($config->delivery_speed * $distance);
        $row->setAttribute('mins',$mins);
        return $this->success('成功', $row);
    }


    /**
     * 接受订单
     * @param Request $request
     * @return Response
     */
    function accept(Request $request):Response
    {
        $id = $request->post('id');
        $order = GoodsOrders::find($id);
        if (!$order){
            return $this->fail('订单不存在');
        }
        if ($order->status != 3){
            return $this->fail('订单状态错误');
        }
        $order->status = 4;
        $order->accept_time = Carbon::now();
        $order->save();
        return $this->success();
    }


    /**
     * 取消订单
     * @param Request $request
     * @return Response
     */
    function cancel(Request $request):Response
    {
        $id = $request->post('id');
        $cancel_reason = $request->post('cancel_reason');
        $cancel_explain = $request->post('cancel_explain');
        $cancel_images = $request->post('cancel_images');
        if (!$cancel_reason){
            return $this->fail('请填写取消原因');
        }
        $order = GoodsOrders::find($id);
        if (!$order){
            return $this->fail('订单不存在');
        }
        if (!in_array($order->status,[3,4,5,6])){
            return $this->fail('订单状态错误');
        }
        $order->status = 1;#更改订单状态
        $order->transport_id = null;#重置骑手id
        $order->cancel_reason = $cancel_reason;
        $order->cancel_explain = $cancel_explain;
        $order->cancel_images = $cancel_images;
        $order->save();
        return $this->success();
    }


    /**
     * 到达仓库
     * @param Request $request
     * @return Response
     */
    function reach(Request $request):Response
    {
        $id = $request->post('id');
        $order = GoodsOrders::find($id);
        if (!$order){
            return $this->fail('订单不存在');
        }
        if ($order->status != 4){
            return $this->fail('订单状态错误');
        }
        $lat = $request->lat;
        $lng = $request->lng;
        $warehouse_distance = Area::getDistanceFromLngLat($lng, $lat, $order->warehouse->lng, $order->warehouse->lat);#骑手离仓库距离
        if ($warehouse_distance > 1){
            return $this->fail('骑手离仓库距离太远');
        }

        $order->status = 5;
        $order->reach_time = Carbon::now();
        $order->save();
        return $this->success();
    }

    /**
     * 确认取货
     * @param Request $request
     * @return Response
     */
    function take(Request $request):Response
    {
        $id = $request->post('id');
        $order = GoodsOrders::find($id);
        if (!$order){
            return $this->fail('订单不存在');
        }
        if ($order->status != 5){
            return $this->fail('订单状态错误');
        }
        $order->status = 6;
        $order->take_time = Carbon::now();
        $order->save();
        return $this->success();
    }


    /**
     * 送达
     * @param Request $request
     * @return Response
     */
    function arrival(Request $request):Response
    {
        $id = $request->post('id');
        $order = GoodsOrders::find($id);
        if (!$order){
            return $this->fail('订单不存在');
        }
        if ($order->status != 6){
            return $this->fail('订单状态错误');
        }
        $order->status = 7;
        $order->settle_status = 1;#待结算
        $order->arrival_time = Carbon::now();
        $order->save();
        return $this->success();
    }









}
