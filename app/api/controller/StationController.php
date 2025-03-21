<?php

namespace app\api\controller;

use app\admin\model\Area;
use app\admin\model\Station;
use app\api\basic\Base;
use support\Request;

class StationController extends Base
{
    function select(Request $request)
    {
        $lat = $request->lat;
        $lng = $request->lng;
        $stations = Station::all();
        // 计算每个站点与给定经纬度的距离，并按距离排序
        $stations = $stations->each(function ($station) use ($lat, $lng) {
            $station->distance =  Area::getDistanceFromLngLat($lng, $lat, $station->lng, $station->lat);
        })->sortBy('distance');
        return $this->success('成功', $stations);
    }

    function detail(Request $request)
    {
        $id = $request->post('id');
        $station = Station::find($id);
        return $this->success('成功', $station);
    }


}
