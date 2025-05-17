<?php

namespace app\api\controller;

use app\admin\model\Area;
use app\admin\model\DriverOrders;
use app\admin\model\WarehouseSku;
use app\admin\model\WarehouseSkuLog;
use app\api\basic\Base;
use Carbon\Carbon;
use plugin\admin\app\model\Option;
use support\Request;
use support\Response;

class DriverController extends Base
{

    function getWorkList(Request $request)
    {
        $status = $request->post('status');#1=待接单,2=待取货,3=配送中
        $lat = $request->lat;
        $lng = $request->lng;
        $order = $request->post('order', 'asc');

        $config = Option::where('name', 'admin_config')->value('value');
        $config = json_decode($config);

        $rows = DriverOrders::where(['user_id' => $request->user_id])
            ->when(!empty($status), function ($query) use ($status) {
                if ($status == 1) {
                    $query->where('status', 1);
                }
                if ($status == 2) {
                    $query->where('status', 2);
                }
                if ($status == 3) {
                    $query->where('status', 4);
                }
            })
            ->orderBy('freight', $order)
            ->get();

        foreach ($rows as $row) {
            $warehouse_distance = Area::getDistanceFromLngLat($lng, $lat, $row->fromWarehouse->lng, $row->fromWarehouse->lat);#司机离仓库距离
            $address_distance = Area::getDistanceFromLngLat($row->fromWarehouse->lng, $row->fromWarehouse->lat, $row->toWarehouse->lng, $row->toWarehouse->lat);#仓库离收获地址距离

            $row->setAttribute('warehouse_distance', $warehouse_distance);
            $row->setAttribute('address_distance', $address_distance);

            if ($row->status == 1) {
                $distance = $warehouse_distance;
            }
            if ($row->status == 2) {
                $distance = $warehouse_distance;
            }
            if ($row->status == 4) {
                $distance = $address_distance;
            }
            $mins = ceil($config->driver_speed * $distance);
            $row->setAttribute('mins', $mins);
        }
        return $this->success('成功', $rows);
    }


    function getWorkDetail(Request $request)
    {
        $lat = $request->lat;
        $lng = $request->lng;
        $config = Option::where('name', 'admin_config')->value('value');
        $config = json_decode($config);

        $id = $request->post('id');
        $row = DriverOrders::where(['user_id' => $request->user_id, 'id' => $id])
            ->with(['fromWarehouse', 'toWarehouse'])
            ->first();
        if (!$row) {
            return $this->fail('订单不存在');
        }

        $warehouse_distance = Area::getDistanceFromLngLat($lng, $lat, $row->fromWarehouse->lng, $row->fromWarehouse->lat);#司机离仓库距离
        $address_distance = Area::getDistanceFromLngLat($row->fromWarehouse->lng, $row->fromWarehouse->lat, $row->toWarehouse->lng, $row->toWarehouse->lat);#仓库离收获地址距离

        $row->setAttribute('warehouse_distance', $warehouse_distance);
        $row->setAttribute('address_distance', $address_distance);
        if ($row->status == 1) {
            $distance = $warehouse_distance;
        }
        if ($row->status == 2) {
            $distance = $warehouse_distance;
        }
        if ($row->status == 4) {
            $distance = $address_distance;
        }
        $mins = ceil($config->driver_speed * $distance);
        $row->setAttribute('mins', $mins);
        return $this->success('成功', $row);
    }


    /**
     * 接受订单
     * @param Request $request
     * @return Response
     */
    function accept(Request $request): Response
    {
        $id = $request->post('id');
        $order = DriverOrders::find($id);
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 0) {
            return $this->fail('订单状态错误');
        }

        $lat = $request->lat;
        $lng = $request->lng;

        $config = Option::where('name', 'admin_config')->value('value');
        $config = json_decode($config);

        $warehouse_distance = Area::getDistanceFromLngLat($lng, $lat, $order->fromWarehouse->lng, $order->fromWarehouse->lat);#司机离仓库距离
        $address_distance = Area::getDistanceFromLngLat($order->fromWarehouse->lng, $order->fromWarehouse->lat, $order->toWarehouse->lng, $order->toWarehouse->lat);#仓库离收获地址距离

        $total_distance = $warehouse_distance + $address_distance;

        $mins = ceil($config->driver_speed * $total_distance);

        $accept_time = Carbon::now();
        $order->status = 2;
        $order->accept_time = $accept_time;
        $order->accept_lat = $request->lat;
        $order->accept_lng = $request->lng;
        $order->settle_status = 1;
        $order->timeout_status = 1;
        $order->total_distance = $total_distance;
        $order->timeout_time = $accept_time->addMinutes($mins);
        $order->save();

        return $this->success();
    }


    /**
     * 取消订单
     * @param Request $request
     * @return Response
     */
    function cancel(Request $request): Response
    {
        $id = $request->post('id');
        $cancel_reason = $request->post('cancel_reason');
        $cancel_explain = $request->post('cancel_explain');
        $cancel_images = $request->post('cancel_images');
        if (!$cancel_reason) {
            return $this->fail('请填写取消原因');
        }
        $order = DriverOrders::find($id);
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if (!in_array($order->status, [3, 4, 5, 6])) {
            return $this->fail('订单状态错误');
        }
        $order->status = 0;#更改订单状态
        $order->user_id = null;#重置司机id
        $order->cancel_reason = $cancel_reason;
        $order->cancel_explain = $cancel_explain;
        $order->cancel_images = $cancel_images;
        $order->save();
        return $this->success();
    }


    /**
     * 确认取货
     * @param Request $request
     * @return Response
     */
    function take(Request $request): Response
    {
        $id = $request->post('id');
        $order = DriverOrders::find($id);
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 2) {
            return $this->fail('订单状态错误');
        }

        $lat = $request->lat;
        $lng = $request->lng;
        $warehouse_distance = Area::getDistanceFromLngLat($lng, $lat, $order->fromWarehouse->lng, $order->fromWarehouse->lat);#骑手离仓库距离
        if ($warehouse_distance > 1) {
            return $this->fail('距离仓库太远');
        }

        $order->status = 4;
        $order->take_time = Carbon::now();
        $order->save();
        return $this->success();
    }


    /**
     * 送达
     * @param Request $request
     * @return Response
     */
    function arrival(Request $request): Response
    {
        $id = $request->post('id');
        $order = DriverOrders::find($id);
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 4) {
            return $this->fail('订单状态错误');
        }
        $arrival_time = Carbon::now();
        if ($arrival_time->gt($order->timeout_time)) {
            $order->timeout_status = 2;#超时
        }

        $diff = $arrival_time->diff($order->accept_time);
        $total_time = $diff->i + ($diff->h * 60) + ($diff->d * 24); // 总分钟数
        $order->status = 5;
        $order->settle_status = 2;#待结算
        $order->arrival_time = $arrival_time;
        $order->total_time = $total_time;
        $order->save();


        $skus = $order->sku()->get();
        $skus->each(function ($item) use($order) {
            #增加仓库库存
            $sku = WarehouseSku::where(['warehouse_id' => $order->to_warehouse_id, 'goods_id' => $item->goods_id])->first();
            if ($sku) {
                $sku->num += $item->num;
                $sku->save();
            }else{
                WarehouseSku::create([
                    'warehouse_id' => $order->to_warehouse_id,
                    'goods_id' => $item->goods_id,
                    'num' => $item->num,
                ]);
            }
            #仓库库存变动记录
            WarehouseSkuLog::create([
                'warehouse_id' => $order->to_warehouse_id,
                'goods_id' => $item->goods_id,
                'num' => $item->num,
                'type' => 2,
                'remark' => '司机订单' . $order->ordersn . '入库',
            ]);
        });


        return $this->success();
    }


    function getWeekStatistic(Request $request)
    {
        $week = DriverOrders::where(['user_id' => $request->user_id])
            ->where('status',5)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        return $this->success('成功', ['money' => $week->sum('freight'), 'count' => $week->count()]);
    }


    function getOrdersList(Request $request)
    {
        $status = $request->post('status');#订单状态:0 全部 1 已完成 2 已取消
        $keyword = $request->post('keyword');
        $date = $request->post('date');

        $orders = DriverOrders::where(['user_id' => $request->user_id])
            ->when(!empty($status) || $status == 0, function ($query) use ($status) {
                if ($status == 0) {
                    $query->whereIn('status', [3, 5]);
                }
                if ($status == 1) {
                    $query->where('status', 5);
                }
                if ($status == 2) {
                    $query->where('status', 3);
                }
            })
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('ordersn', $keyword);
            })
            ->when($date, function ($query) use ($date) {
                $query->whereBetween('accept_time', $date);
            })
            ->get();
        foreach ($orders as $order) {
            if ($order->timeout_status == 1) {
                //未超时
                $end_time = Carbon::now();
            } else {
                $end_time = $order->arrival_time;
            }
            $diff = $end_time->diff($order->accept_time);
            $hours = $diff->h + ($diff->d * 24); // 总小时数
            $minutes = $diff->i; // 总分钟数
            $seconds = $diff->s; // 总秒数
            $timeDifference = "{$hours}小时{$minutes}分{$seconds}秒";
            $order->setAttribute('time_difference', $timeDifference);

            $warehouse_distance = Area::getDistanceFromLngLat($order->accept_lng, $order->accept_lat, $order->fromWarehouse->lng, $order->fromWarehouse->lat);#骑手离仓库距离
            $address_distance = Area::getDistanceFromLngLat($order->fromWarehouse->lng, $order->fromWarehouse->lat, $order->toWarehouse->lng, $order->toWarehouse->lat);#仓库离收获地址距离

            $order->setAttribute('warehouse_distance',$warehouse_distance);
            $order->setAttribute('address_distance',$address_distance);
        }
        return $this->success('成功', $orders);
    }

}
