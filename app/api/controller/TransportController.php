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
            $address_distance = Area::getDistanceFromLngLat($row->warehouse->lng, $row->warehouse->lat, $row->address->lng, $row->address->lat);#仓库离收获地址距离

            $row->setAttribute('warehouse_distance',$warehouse_distance);
            $row->setAttribute('address_distance',$address_distance);

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
        $config = Option::where('name', 'admin_config')->value('value');
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

        $config = Option::where('name', 'admin_config')->value('value');
        $config = json_decode($config);

        $warehouse_distance = Area::getDistanceFromLngLat($request->lng, $request->lat, $order->warehouse->lng, $order->warehouse->lat);#骑手离仓库距离

        $address_distance = Area::getDistanceFromLngLat($order->warehouse->lng, $order->warehouse->lat,$order->address->lng, $order->address->lat);#仓库离收获地址距离
        $total_distance = $warehouse_distance + $address_distance;

        $mins = ceil($config->delivery_speed * $total_distance);

        $accept_time = Carbon::now();
        $order->status = 4;
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
        $arrival_time = Carbon::now();
        if ($arrival_time->gt($order->timeout_time)){
            $order->timeout_status = 2;#超时
        }

        $diff = $arrival_time->diff($order->accept_time);
        $total_time = $diff->i + ($diff->h * 60) + ($diff->d * 24); // 总分钟数
        $order->status = 7;
        $order->settle_status = 2;#待结算
        $order->arrival_time = $arrival_time;
        $order->total_time = $total_time;
        $order->save();
        return $this->success();
    }


    function getWeekCount(Request $request)
    {
        $transport_id = $request->user_id;
        $week = GoodsOrders::where(['transport_id' => $transport_id])
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        return $this->success('成功', ['money'=>$week->sum('freight'),'count'=>$week->count()]);
    }

    function getMoneyList(Request $request)
    {
        $date = $request->post('date', Carbon::now());
        if (!$date){
            return $this->fail('请选择月份');
        }
        $date = Carbon::parse($date);
        // 提取年份和月份
        $year = $date->year;
        $month = $date->month;
        $transport_id = $request->user_id;
        $orders = GoodsOrders::where(['transport_id' => $transport_id])
            ->select(['freight','accept_time','ordersn'])
            ->whereYear('accept_time', $year)
            ->whereMonth('accept_time', $month)
            ->latest()
            ->get()
            ->groupBy(function ($order) {
                return $order->accept_time->toDateString();
            });
        $result = [];
        foreach ($orders as $date => $orderList) {
            $result[] = [
                'date' => $date,
                'list' => $orderList->toArray()
            ];
        }

        return $this->success('成功', $result);
    }


    function getOrdersStatistic(Request $request)
    {
        $today = Carbon::today();
        $transport_id = $request->user_id;
        $orders = GoodsOrders::where(['transport_id' => $transport_id])
            ->whereDate('accept_time', $today);
        return $this->success('成功', [
            'arrival_count' => $orders->where('status', 7)->count(),
            'cancel_count' => $orders->where('status', 2)->count(),
            'ontime_rate' => $orders->where('status', 7)->count() / $orders->count() * 100 . '%',
            'avg_time' => $orders->where('status', 7)->avg('total_time') . '分钟'
        ]);
    }

    function getOrdersList(Request $request)
    {
        $status = $request->post('status');#订单状态:0 全部 1 已完成 2 已取消
        $keyword = $request->post('keyword');
        $date = $request->post('date');
        $transport_id = $request->user_id;
        $orders = GoodsOrders::where(['transport_id' => $transport_id])
            ->when(!empty($status) || $status == 0, function ($query) use ($status) {
                if ($status == 0) {
                    $query->whereIn('status', [2,7]);
                }
                if ($status == 1) {
                    $query->where('status', 7);
                }
                if ($status == 2) {
                    $query->where('status', 2);
                }
            })
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('ordersn',  $keyword );
            })
            ->when($date, function ($query) use ($date) {
                $query->whereBetween('accept_time', $date);
            })
            ->get();
        foreach ($orders as $order) {
            if ($order->timeout_status == 1){
                //未超时
                $end_time = Carbon::now();
            }else{
                $end_time = $order->arrival_time;
            }
            $diff = $end_time->diff($order->accept_time);
            $hours = $diff->h + ($diff->d * 24); // 总小时数
            $minutes = $diff->i; // 总分钟数
            $seconds = $diff->s; // 总秒数
            $timeDifference = "{$hours}小时{$minutes}分{$seconds}秒";
            $order->setAttribute('time_difference', $timeDifference);
        }
        return $this->success('成功', ['list'=>$orders]);
    }
    













}
