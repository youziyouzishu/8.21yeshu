<?php

namespace app\api\controller;

use app\admin\model\Area;
use app\admin\model\Station;
use app\admin\model\Warehouse;
use app\api\basic\Base;
use support\Request;

class WarehouseController extends Base
{
    /**
     * 获取所有仓库信息
     */
    function select(Request $request)
    {
        $lat = $request->lat;
        $lng = $request->lng;
        $stations = Warehouse::all();
        // 计算每个站点与给定经纬度的距离，并按距离排序
        $stations = $stations->each(function ($station) use ($lat, $lng) {
            $station->distance =  Area::getDistanceFromLngLat($lng, $lat, $station->lng, $station->lat);
        });
            // 按距离由近到远排序
        $stations = $stations->sortBy('distance')->values();
        return $this->success('成功', $stations);
    }

    function detail(Request $request)
    {
        $id = $request->post('id');
        $station = Warehouse::find($id);
        return $this->success('成功', $station);
    }


}
